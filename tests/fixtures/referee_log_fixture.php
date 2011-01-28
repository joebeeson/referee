<?php

/**
 * RefereeLog Fixture
 *
 * @package     referee
 * @subpackage  referee.tests.fixtures
 * @see         RefereeLog
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class RefereeLogFixture extends CakeTestFixture {

	/**
     * @var string
     */
	public $name = 'RefereeLog';

	/**
     * @var array
     */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'level' => array('type' => 'string', 'null' => false, 'length' => 32),
		'file' => array('type' => 'string', 'null' => false),
		'line' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4),
		'class' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'function' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'args' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'type' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 8),
		'message' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'trace' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'request_method' => array('type' => 'string', 'null' => true, 'length' => 6),
		'request_plugin' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'request_controller' => array('type' => 'string', 'null' => true, 'length' => 32),
		'request_action' => array('type' => 'string', 'null' => true, 'length' => 32),
		'request_ext' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 8),
		'request_parameters' => array('type' => 'text', 'null' => true, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);

}

?>