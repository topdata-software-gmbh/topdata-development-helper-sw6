-- THIS TABLE IS MISSING WITH AFTER INSTALLING USING THE SW6 INSTALLER, SO WE NEED TO MANUALLY ADD IT TO THE DATABASE

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `elasticsearch_index_task`;
CREATE TABLE `elasticsearch_index_task` (
                                            `id` binary(16) NOT NULL,
                                            `index` varchar(500) NOT NULL,
                                            `alias` varchar(500) NOT NULL,
                                            `entity` varchar(500) NOT NULL,
                                            `doc_count` int(11) NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2024-05-26 07:33:02