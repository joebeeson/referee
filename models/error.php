<?php

	/**
	 * Error model.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class Error extends AppModel {
		/**
		 * For you MySQL users, here is the CREATE TABLE query to run.
		 * 
		 * CREATE TABLE `errors` (
		 *   `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
		 *   `level` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `file` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `line` int(5) DEFAULT NULL,
		 *   `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		 *   PRIMARY KEY (`id`)
		 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		 */
	}
