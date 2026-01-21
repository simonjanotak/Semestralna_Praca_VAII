CREATE TABLE `posts` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                         `user_id` int(10) unsigned DEFAULT NULL,
                         `title` varchar(255) NOT NULL,
                         `content` text NOT NULL,
                         `picture` varchar(300) DEFAULT NULL,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         `category_id` int(11) NOT NULL,
                         PRIMARY KEY (`id`),
                         KEY `idx_posts_user_id` (`user_id`),
                         KEY `fk_posts_category` (`category_id`),
                         CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
                         CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci


