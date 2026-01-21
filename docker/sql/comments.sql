CREATE TABLE `comments` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `post_id` int(11) NOT NULL,
                            `user_id` int(10) unsigned NOT NULL,
                            `content` text NOT NULL,
                            `created_at` datetime DEFAULT current_timestamp(),
                            PRIMARY KEY (`id`),
                            KEY `idx_comments_post_id` (`post_id`),
                            KEY `idx_comments_user_id` (`user_id`),
                            CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                            CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci