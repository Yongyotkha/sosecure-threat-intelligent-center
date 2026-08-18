<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SsdeepFile extends Model
{
    protected $table = 'ssdeep_file';

    protected $fillable = [
        'version',
        'path',
        'file_name',
        'title',
        'category',
        'description',
        'format',
        'sha256',
        'size_bytes',
        'signature_count',
        'status',
        'source',
    ];

    /**
     * Infer pack origin when DB column is missing or empty.
     */
    public function packSource()
    {
        if (!empty($this->source)) {
            return $this->source === 'auto' ? 'auto' : 'master';
        }
        $blob = strtolower(trim(
            (string) $this->file_name.' '.
            (string) $this->title.' '.
            (string) $this->description
        ));
        if (strpos($blob, '_auto') !== false
            || strpos($blob, '(auto)') !== false
            || strpos($blob, 'auto-promoted') !== false) {
            return 'auto';
        }
        return 'master';
    }

    /**
     * Human-readable label for UI lists.
     */
    public function displayLabel()
    {
        $parts = [];
        if (!empty($this->category)) {
            $parts[] = '['.$this->category.']';
        }
        if (!empty($this->title)) {
            $parts[] = $this->title;
        }
        if (empty($parts)) {
            $parts[] = $this->version ?: ($this->file_name ?: 'ssdeep-pack');
        } else {
            $parts[] = '—';
            $parts[] = $this->version ?: ($this->file_name ?: '');
        }
        return trim(preg_replace('/\s+/', ' ', implode(' ', $parts)));
    }
}
