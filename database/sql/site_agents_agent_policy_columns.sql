-- Safe to re-run. App DB prefix is often fx_ → physical table fx_site_agents.
SET @db = DATABASE();
SET @tbl = IF(
  EXISTS(SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND TABLE_TYPE='BASE TABLE'),
  'fx_site_agents',
  'site_agents'
);

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='ssdeep_enabled'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `ssdeep_enabled` CHAR(1) NOT NULL DEFAULT ''Y''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='ssdeep_threshold'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `ssdeep_threshold` SMALLINT UNSIGNED NOT NULL DEFAULT 85')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='ssdeep_report_api'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `ssdeep_report_api` CHAR(1) NOT NULL DEFAULT ''Y''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='quarantine_on_detect'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `quarantine_on_detect` CHAR(1) NOT NULL DEFAULT ''Y''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='send_ssdeep_candidate'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `send_ssdeep_candidate` CHAR(1) NOT NULL DEFAULT ''N''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='auto_scan_on_login'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `auto_scan_on_login` CHAR(1) NOT NULL DEFAULT ''N''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='exclusion_paths'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `exclusion_paths` TEXT NULL')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='scan_extensions'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `scan_extensions` TEXT NULL')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='quick_scan_paths'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `quick_scan_paths` TEXT NULL')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='log_level'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `log_level` VARCHAR(16) NOT NULL DEFAULT ''info''')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='cache_expiry_hours'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `cache_expiry_hours` INT UNSIGNED NOT NULL DEFAULT 168')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql = IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='config_updated_at'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `config_updated_at` DATETIME NULL')
); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
