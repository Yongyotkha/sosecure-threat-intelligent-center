-- Soft-delete-friendly unique IP per site (raw SQL; physical table name).
-- With Laravel prefix fx_, physical table is fx_site_agents.
-- Active rows: ip_unique_key = ip_private
-- Soft-deleted: ip_unique_key = NULL

SET @db := DATABASE();
SET @tbl := IF(
  EXISTS(SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND TABLE_TYPE='BASE TABLE'),
  'fx_site_agents',
  IF(
    EXISTS(SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA=@db AND TABLE_NAME='site_agents' AND TABLE_TYPE='BASE TABLE'),
    'site_agents',
    NULL
  )
);

SET @skip := IF(@tbl IS NULL, 1, 0);

SET @sql := IF(
  @skip=1 OR EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND COLUMN_NAME='ip_unique_key'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `ip_unique_key` VARCHAR(64) NULL AFTER `ip_private`')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@skip=1, 'SELECT 1', CONCAT(
  'UPDATE `', @tbl, '` SET `ip_unique_key` = `ip_private` ',
  'WHERE `deleted_at` IS NULL AND `ip_private` IS NOT NULL AND `ip_private` <> '''''
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@skip=1, 'SELECT 1', CONCAT(
  'UPDATE `', @tbl, '` SET `ip_unique_key` = NULL WHERE `deleted_at` IS NOT NULL'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@skip=1, 'SELECT ''no agents table'' AS msg', CONCAT(
  'SELECT site_id, ip_private, COUNT(*) AS cnt, GROUP_CONCAT(id ORDER BY id) AS agent_ids ',
  'FROM `', @tbl, '` ',
  'WHERE deleted_at IS NULL AND ip_private IS NOT NULL AND ip_private <> '''' ',
  'GROUP BY site_id, ip_private HAVING COUNT(*) > 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(@skip=1, 'SELECT 1', CONCAT(
  'UPDATE `', @tbl, '` a ',
  'JOIN (',
  '  SELECT site_id, ip_private, MIN(id) AS keep_id FROM `', @tbl, '` ',
  '  WHERE deleted_at IS NULL AND ip_private IS NOT NULL AND ip_private <> '''' ',
  '  GROUP BY site_id, ip_private HAVING COUNT(*) > 1',
  ') d ON a.site_id = d.site_id AND a.ip_private = d.ip_private ',
  '  AND a.deleted_at IS NULL AND a.id <> d.keep_id ',
  'SET a.deleted_at = COALESCE(a.deleted_at, NOW()), a.ip_unique_key = NULL'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  @skip=1 OR EXISTS(SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@tbl AND INDEX_NAME='site_agents_site_ip_unique'),
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tbl, '` ADD UNIQUE INDEX `site_agents_site_ip_unique` (`site_id`, `ip_unique_key`)')
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
