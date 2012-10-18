<?php

error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);

Configure::write('Referee', true);

// Ensure we have our WhistleComponent available
App::import('Lib', 'Referee.RefereeListener');
App::import('Component', 'Referee.Whistle');

/**
 * @package     referee
 * @subpackage  referee.tests.components
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @uses        WhistleComponent
 */
class WhistleProxyComponent extends WhistleComponent {

	/**
	 * @return array
	 */
	public function getListeners() {
		return $this->_listeners;
	}

	/**
	 * @param   string $ident
	 * @return  mixed
	 */
	public function getListener($ident = null) {
		foreach($this->_listeners as $listener) {
			if($listener->ident == $ident) {
				return $listener;
			}
		}
		return false;
	}
}

/**
 * @package     referee
 * @subpackage  referee.tests.components
 * @uses        Controller
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class RefereeTestController extends Controller {

	/**
	 * @var boolean
	 */
	public $autoRender = false;

	/**
	 * @var array
	 */
	public $uses = array();

	/**
	 * @var array
	 */
	public $data = array(
		'_Token' => true
	);

	/**
	 * @return void
	 */
	public function __construct() {

		$this->components = array(
			'WhistleProxy' => array(
				'listeners' => array(
					'CustomTestListener' => array(
						'class' => 'CustomTestListener',
						'file' => App::pluginPath('Referee') . 'tests' . DS . 'libs' . DS . 'custom_test_listener.php'
					)
				)
			)
		);

		parent::__construct();

	}

}

/**
 * WhistleComponentTest
 *
 * Tests the Whistle component for the Referee plugin.
 *
 * @package     referee
 * @subpackage  referee.tests.components
 * @author      Joe Beeson <jbeeson@gmail.com>
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class WhistleComponentTest extends CakeTestCase {

	/**
	 * @return void
	 */
	public function startTest() {
		$this->WhistleTest = new RefereeTestController();
		$this->WhistleTest->constructClasses();
		$this->WhistleTest->startupProcess();
	}

	/**
	 * @return void
	 */
	public function endTest() {
		unset($this->WhistleTest);
		ClassRegistry::flush();
	}

	/**
	 * @return void
	 */
	public function testListenersLoaded() {
		$this->assertTrue($this->WhistleTest->WhistleProxy);
		$this->assertTrue($this->WhistleTest->WhistleProxy->getListener('Custom'));
	}

	/**
	 * @return void
	 */
	public function testHandlers() {
		
		// re-register because simpletest sets a new error handler on each invocation
		$this->WhistleTest->WhistleProxy->registerHandlers();
		
		trigger_error('testing notice', E_USER_NOTICE);

		$listener = $this->WhistleTest->WhistleProxy->getListener('Custom');
		$this->assertTrue($listener->error['message'] == 'testing notice');
		$this->assertTrue($listener->error['level'] == E_USER_NOTICE);
		$this->assertTrue($listener->error['request_controller']);

		restore_error_handler();

	}

}

?>