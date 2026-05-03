-- Migration: Add normalized status enum values to request tables and migrate existing data

-- 1) Add new column with temporary name to preserve existing data
ALTER TABLE `request` ADD COLUMN `status_new` ENUM('pending','approved','declined','processing','completed') DEFAULT 'pending';
ALTER TABLE `requests` ADD COLUMN `status_new` ENUM('pending','approved','declined','processing','completed') DEFAULT 'pending';

-- 2) Migrate existing values (case-insensitive mapping)
UPDATE `request` SET status_new = CASE LOWER(COALESCE(status,'pending'))
    WHEN 'pending' THEN 'pending'
    WHEN 'ready to pick up' THEN 'approved'
    WHEN 'approved' THEN 'approved'
    WHEN 'rejected' THEN 'declined'
    ELSE 'pending' END;

UPDATE `requests` SET status_new = CASE LOWER(COALESCE(status,'pending'))
    WHEN 'pending' THEN 'pending'
    WHEN 'approved' THEN 'approved'
    WHEN 'rejected' THEN 'declined'
    ELSE 'pending' END;

-- 3) Drop old columns and rename new ones
ALTER TABLE `request` DROP COLUMN `status`;
ALTER TABLE `request` CHANGE COLUMN `status_new` `status` ENUM('pending','approved','declined','processing','completed') NOT NULL DEFAULT 'pending';

ALTER TABLE `requests` DROP COLUMN `status`;
ALTER TABLE `requests` CHANGE COLUMN `status_new` `status` ENUM('pending','approved','declined','processing','completed') NOT NULL DEFAULT 'pending';

-- 4) Optional: verify
SELECT request_id, status FROM request LIMIT 20;
SELECT id, status FROM requests LIMIT 20;
