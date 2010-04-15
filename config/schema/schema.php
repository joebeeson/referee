<?php 

	class Schema extends CakeSchema {
		
		public $name = '';

		public function before($event = array()) {
			return true;
		}

		public function after($event = array()) {
		}

		public $errors = array(
			'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
			'level' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 15),
			'file' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 200),
			'line' => array('type' => 'integer', 'null' => true, 'default' => NULL, 'length' => 5),
			'message' => array('type' => 'string', 'null' => true, 'default' => NULL),
			'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
			'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
			'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM')
		);
		
	}
