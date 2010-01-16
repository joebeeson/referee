<?php

	/**
	 * Error model.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class Error extends JournalAppModel {
		/**
		 * For you MySQL users, here is the CREATE TABLE query to run.
		 * 
		 * CREATE TABLE `errors` (
		 *   `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
		 *   `level` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `file` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `line` int(5) DEFAULT NULL,
		 *   `message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		 *   `created` timestamp DEFAULT NULL,
		 *   PRIMARY KEY (`id`)
		 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		 */
		
		/**
		 * Returns errors for the day matching $date, which is strtotime
		 * ready. Defaults to an "all" find but can also be used for count
		 * @param string $date
		 * @param string $method
		 * @return array
		 * @access public
		 */
		public function day($date = 'today', $method = 'all') {
			return $this->find($method, array(
				'conditions' => array(
					'`Error`.`created` >' => date('Y-m-d H:i:s', strtotime($date)),
					'`Error`.`created` <' => date('Y-m-d H:i:s', strtotime('+1 day', strtotime($date))),
				)
			));
		}
		
	}
