<?php

namespace App\Services;

use MongoDB\BSON\UTCDateTime;

/**
 * Normalizes honeypot timestamps from the legacy Python agent.
 *
 * The agent treats naive apache log times as UTC, then emits ISO strings with +07:00,
 * which shifts displayed Thailand time forward by 7 hours. We correct on ingest and
 * when reading older MongoDB documents that were stored before the ingest fix.
 */
class HoneypotTimestamp
{
    public static function legacyCorrectionEnabled(): bool
    {
        return (bool) config('honeypot.ingest.legacy_timestamp_correction', true);
    }

    public static function legacyCorrectionHours(): int
    {
        return (int) config('honeypot.ingest.legacy_timestamp_offset_hours', -7);
    }

    public static function isLegacyAgentOffset(string $raw): bool
    {
        $raw = trim($raw);

        return $raw !== ''
            && (preg_match('/\+07:00$/', $raw) === 1 || preg_match('/\+0700$/', $raw) === 1);
    }

    public static function parseToUtcImmutable($value): \DateTimeImmutable
    {
        if ($value instanceof UTCDateTime) {
            return \DateTimeImmutable::createFromMutable(
                $value->toDateTime()->setTimezone(new \DateTimeZone('UTC'))
            );
        }

        if (is_numeric($value)) {
            $seconds = (int) $value;

            if ($seconds > 9999999999) {
                $seconds = (int) floor($seconds / 1000);
            }

            return (new \DateTimeImmutable('@' . $seconds))->setTimezone(new \DateTimeZone('UTC'));
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        $bangkok = new \DateTimeZone('Asia/Bangkok');
        $utc = new \DateTimeZone('UTC');

        if (preg_match('/(Z|[+-]\d{2}:?\d{2})$/i', $raw)) {
            $parsed = (new \DateTimeImmutable($raw))->setTimezone($utc);
        } else {
            $parsed = (new \DateTimeImmutable($raw, $bangkok))->setTimezone($utc);
        }

        if (self::legacyCorrectionEnabled() && self::isLegacyAgentOffset($raw)) {
            $parsed = $parsed->modify(self::legacyCorrectionHours() . ' hours');
        }

        return $parsed;
    }

    public static function toUtcDateTime($value, bool &$legacyCorrected = false): UTCDateTime
    {
        $raw = is_string($value) ? trim($value) : '';
        $legacyCorrected = self::legacyCorrectionEnabled() && $raw !== '' && self::isLegacyAgentOffset($raw);
        $parsed = self::parseToUtcImmutable($value);

        return new UTCDateTime($parsed->getTimestamp() * 1000);
    }

    public static function normalizeStoredUtc(UTCDateTime $value, bool $legacyCorrected = false): UTCDateTime
    {
        if ($legacyCorrected || !self::legacyCorrectionEnabled()) {
            return $value;
        }

        $parsed = \DateTimeImmutable::createFromMutable(
            $value->toDateTime()->setTimezone(new \DateTimeZone('UTC'))
        )->modify(self::legacyCorrectionHours() . ' hours');

        return new UTCDateTime($parsed->getTimestamp() * 1000);
    }

    public static function formatBangkok($value, string $format = 'Y-m-d H:i:s', bool $legacyCorrected = false): string
    {
        if (!$value instanceof UTCDateTime) {
            return '';
        }

        $normalized = self::normalizeStoredUtc($value, $legacyCorrected);

        return $normalized->toDateTime()
            ->setTimezone(new \DateTimeZone('Asia/Bangkok'))
            ->format($format);
    }

    /**
     * Legacy agent rows are stored ~7 hours ahead. Shift query bounds on raw Mongo timestamps.
     *
     * @return array{0:\DateTimeImmutable,1:\DateTimeImmutable}
     */
    public static function queryBoundsForLegacyStorage(
        \DateTimeImmutable $from,
        \DateTimeImmutable $to
    ): array {
        if (!self::legacyCorrectionEnabled()) {
            return [$from, $to];
        }

        $offsetHours = abs(self::legacyCorrectionHours());

        return [
            $from->modify('+' . $offsetHours . ' hours'),
            $to->modify('+' . $offsetHours . ' hours'),
        ];
    }
}
