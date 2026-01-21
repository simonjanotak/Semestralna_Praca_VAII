CREATE TABLE `users` (
                         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                         `username` varchar(100) NOT NULL,
                         `email` varchar(255) NOT NULL,
                         `password_hash` varchar(255) NOT NULL,
                         `role` enum('user','moderator','admin') NOT NULL DEFAULT 'user',
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `ux_users_username` (`username`),
                         UNIQUE KEY `ux_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

