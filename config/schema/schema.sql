DROP TABLE IF EXISTS `referee_logs`;

CREATE TABLE `referee_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(32) NOT NULL DEFAULT '',
  `file` varchar(255) NOT NULL DEFAULT '',
  `line` int(4) unsigned NOT NULL DEFAULT '0',
  `class` varchar(32) DEFAULT NULL,
  `function` varchar(32) DEFAULT NULL,
  `args` text,
  `type` varchar(8) DEFAULT NULL COMMENT 'method, static, function',
  `message` text NOT NULL,
  `trace` text,
  `request_method` varchar(6) DEFAULT '',
  `request_plugin` varchar(32) DEFAULT NULL,
  `request_controller` varchar(32) DEFAULT '',
  `request_action` varchar(32) DEFAULT '',
  `request_ext` varchar(8) DEFAULT NULL,
  `request_parameters` text,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
