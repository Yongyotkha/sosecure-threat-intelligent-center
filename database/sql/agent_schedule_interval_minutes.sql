-- Convert agent schedule columns from HH:mm (daily) to interval minutes.
-- Defaults: batch=1440, ti_sync=60, agent_update=360
-- Presets: 1,5,10,15,20,25,30,45,60,120,180,360,720,1440

SET @db := DATABASE();

-- Widen columns if needed (ignore errors if already wide / missing).
SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='batchjob_everydate'),
    'ALTER TABLE `fx_site_agents` MODIFY COLUMN `batchjob_everydate` VARCHAR(16) NOT NULL DEFAULT ''1440''',
    'SELECT 1'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='ti_sync_everydate'),
    'ALTER TABLE `fx_site_agents` MODIFY COLUMN `ti_sync_everydate` VARCHAR(16) NOT NULL DEFAULT ''60''',
    'SELECT 1'
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @sql := (
  SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='fx_site_agents' AND COLUMN_NAME='agent_update_schedule'),
    'ALTER TABLE `fx_site_agents` MODIFY COLUMN `agent_update_schedule` VARCHAR(16) NOT NULL DEFAULT ''360''',
    'ALTER TABLE `fx_site_agents` ADD COLUMN `agent_update_schedule` VARCHAR(16) NOT NULL DEFAULT ''360'''
  )
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

UPDATE `fx_site_agents` SET `batchjob_everydate` = '1440'
WHERE `batchjob_everydate` LIKE '%:%' OR `batchjob_everydate` IS NULL OR `batchjob_everydate` = '';

UPDATE `fx_site_agents` SET `ti_sync_everydate` = '60'
WHERE `ti_sync_everydate` LIKE '%:%' OR `ti_sync_everydate` IS NULL OR `ti_sync_everydate` = '';

UPDATE `fx_site_agents` SET `agent_update_schedule` = '360'
WHERE `agent_update_schedule` LIKE '%:%' OR `agent_update_schedule` IS NULL OR `agent_update_schedule` = '';
