CREATE TABLE `errors` (
  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` int(5) DEFAULT NULL,
  `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8_unicode_ci,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
