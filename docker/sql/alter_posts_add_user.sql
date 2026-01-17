-- filepath: docker/sql/alter_posts_add_user.sql
-- ALTER migration for existing DB: add user_id column and FK to posts

-- 1) Add column (if not exists)
ALTER TABLE `posts`
  ADD COLUMN IF NOT EXISTS `user_id` INT UNSIGNED NULL AFTER `id`;

-- Note: MySQL < 8.0 doesn't support IF NOT EXISTS for ADD COLUMN. If your server is older, run a safe check:
-- ALTER TABLE `posts` ADD COLUMN `user_id` INT UNSIGNED NULL;

-- 2) Create index
CREATE INDEX `idx_posts_user_id` ON `posts` (`user_id`);

-- 3) Add foreign key constraint
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- OPTIONAL: set owner for existing posts (replace 1 with the id of an existing user)
-- UPDATE `posts` SET `user_id` = 1 WHERE `user_id` IS NULL;

