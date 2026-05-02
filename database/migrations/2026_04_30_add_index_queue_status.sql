-- Migration: Add missing queue status index for faster queue lookups
ALTER TABLE `queue`
ADD INDEX `idx_queue_status` (`status`);
