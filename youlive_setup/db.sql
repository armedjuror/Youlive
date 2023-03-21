CREATE TABLE `errorlog` (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
                                       `type` varchar(255) NOT NULL,
                                       `error` text,
                                       `occured_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                       `status` varchar(16) NOT NULL DEFAULT 'pending',
                                       PRIMARY KEY (`id`)
           ) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `channels` (
                            `id` varchar(16) NOT NULL,
                            `name` varchar(255) NOT NULL,
                            `picture` varchar(255) NOT NULL,
                            `access_token` varchar(255) DEFAULT NULL,
                            `refresh_token` varchar(255) DEFAULT NULL,
                            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `users` (
                         `channel_id` varchar(16) NOT NULL,
                         `id` varchar(16) NOT NULL,
                         `type` varchar(16) NOT NULL,
                         `email` varchar(255) NOT NULL,
                         `name` varchar(255) NOT NULL,
                         `password` varchar(64) NOT NULL,
                         `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `last_login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `email` (`email`),
                         KEY `channel_user_fk` (`channel_id`),
                         CONSTRAINT `channel_user_fk` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8