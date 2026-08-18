-- OTA agent release tables (safe to re-run)

SET @db := DATABASE();

SET @sql := (SELECT IF(COUNT(*)=0,
  'CREATE TABLE `fx_agent_release_packages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `version` VARCHAR(64) NOT NULL,
    `file_name` VARCHAR(255) NULL,
    `path` VARCHAR(512) NOT NULL,
    `sha256` VARCHAR(64) NULL,
    `size_bytes` BIGINT NULL,
    `status` CHAR(1) NOT NULL DEFAULT ''Y'',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `agent_release_packages_version_uq` (`version`),
    KEY `agent_release_packages_status` (`status`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
  'SELECT 1') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_agent_release_packages');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'CREATE TABLE `fx_agent_release_targets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` INT UNSIGNED NULL,
    `package_id` BIGINT UNSIGNED NOT NULL,
    `target_version` VARCHAR(64) NOT NULL,
    `status` CHAR(1) NOT NULL DEFAULT ''Y'',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `agent_release_targets_site` (`site_id`),
    KEY `agent_release_targets_pkg` (`package_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
  'SELECT 1') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_agent_release_targets');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'CREATE TABLE `fx_agent_release_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` INT UNSIGNED NULL,
    `agent_id` BIGINT UNSIGNED NULL,
    `ip_private` VARCHAR(64) NULL,
    `current_version` VARCHAR(64) NULL,
    `target_version` VARCHAR(64) NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT ''checking'',
    `message` TEXT NULL,
    `started_at` DATETIME NULL,
    `finished_at` DATETIME NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `agent_release_events_site` (`site_id`),
    KEY `agent_release_events_agent` (`agent_id`),
    KEY `agent_release_events_status` (`status`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
  'SELECT 1') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_agent_release_events');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_version_current` VARCHAR(64) NULL',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_version_current');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_version_target` VARCHAR(64) NULL',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_version_target');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_update_status` VARCHAR(32) NULL',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_update_status');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_update_checked_at` DATETIME NULL',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_update_checked_at');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_update_applied_at` DATETIME NULL',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_update_applied_at');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_update_schedule` VARCHAR(5) NOT NULL DEFAULT ''04:00''',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_update_schedule');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
