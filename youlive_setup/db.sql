CREATE TABLE `errorlog` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `type` varchar(255) NOT NULL,
                            `error` text,
                            `occured_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            `status` varchar(16) NOT NULL DEFAULT 'pending',
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8;

CREATE TABLE `channels` (
                            `id` varchar(16) NOT NULL,
                            `name` varchar(255) NOT NULL,
                            `picture` varchar(255) NOT NULL,
                            `access_token` varchar(255) DEFAULT NULL,
                            `refresh_token` varchar(255) DEFAULT NULL,
                            `max_users` int(11) DEFAULT '-1',
                            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
                         `channel_id` varchar(16) DEFAULT NULL,
                         `id` varchar(16) NOT NULL,
                         `type` varchar(16) NOT NULL,
                         `email` varchar(255) NOT NULL,
                         `name` varchar(255) NOT NULL,
                         `password` varchar(64) NOT NULL,
                         `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `last_login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `email` (`email`),
                         KEY `channel_user_fk` (`channel_id`),
                         CONSTRAINT `channel_user_fk` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `streams` (
                           `id` varchar(48) NOT NULL,
                           `user_id` varchar(16) NOT NULL,
                           `title` varchar(255) NOT NULL,
                           `ingestionType` varchar(16) NOT NULL,
                           `frameRate` varchar(16) NOT NULL,
                           `resolution` varchar(16) NOT NULL,
                           `streamKey` varchar(36) NOT NULL,
                           `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           PRIMARY KEY (`id`),
                           UNIQUE KEY `streamKey` (`streamKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `finance` (
                           `channel_id` varchar(16) NOT NULL,
                           `id` varchar(16) NOT NULL,
                           `description` varchar(255) NOT NULL,
                           `amount` float NOT NULL,
                           `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           `created_by` varchar(16) NOT NULL,
                           `method` varchar(16) NOT NULL,
                           `counterparty` varchar(255) NOT NULL,
                           `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           PRIMARY KEY (`id`),
                           KEY `channel_finance_fk` (`channel_id`),
                           CONSTRAINT `channel_finance_fk` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
                          `id` varchar(64) NOT NULL,
                          `stream` varchar(48) DEFAULT NULL,
                          `created_by` varchar(16) NOT NULL,
                          `title` varchar(100) NOT NULL,
                          `description` text NOT NULL,
                          `scheduled_start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `privacy_status` varchar(64) NOT NULL,
                          `thumbnail` varchar(255) NOT NULL,
                          `etag` varchar(64) NOT NULL,
                          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `last_updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          `charge` float DEFAULT NULL,
                          `contribution` float DEFAULT NULL,
                          `payment_status` varchar(32) NOT NULL DEFAULT 'pending',
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `etag` (`etag`),
                          KEY `created_by_user_fk` (`created_by`),
                          KEY `stream_event_fk` (`stream`),
                          CONSTRAINT `created_by_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                          CONSTRAINT `stream_event_fk` FOREIGN KEY (`stream`) REFERENCES `streams` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;