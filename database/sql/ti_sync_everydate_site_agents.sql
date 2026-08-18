-- Daily TI (rules + ssdeep) sync schedule on fx_site_agents (safe to re-run)

SET @db := DATABASE();

SET @sql := (SELECT IF(COUNT(*)=0,
  'ALTER TABLE `fx_site_agents` ADD COLUMN `ti_sync_everydate` VARCHAR(5) NOT NULL DEFAULT ''03:00''',
  'SELECT 1') FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='ti_sync_everydate');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
