<?php

error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);

// Ensure we have our RefereeWhistle available
App::import('Lib', 'Referee.RefereeListener');
App::import('Lib', 'Referee.RefereeWhistle');

/**
 * @package     referee
 * @subpackage  referee.tests.components
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @uses        RefereeWhistle
 */
class RefereeWhistleProxy extends RefereeWhistle {

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
 * RefereeWhistleTest
 *
 * Tests the Whistle component for the Referee plugin.
 *
 * @package     referee
 * @subpackage  referee.tests.components
 * @author      Joe Beeson <jbeeson@gmail.com>
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class RefereeWhistleTest extends CakeTestCase {

	/**
	 * @return void
	 */
	public function startTest($method) {

		Configure::write('Referee', $method != 'testDisabled');

		$path = App::pluginPath('Referee') . 'tests' . DS . 'libs' . DS;

		$this->RefereeWhistleProxy = new RefereeWhistleProxy(array(
			'listeners' => array(
				'Syslog',
				'TestRefereeListener' => array(
					array(
						'class' => 'TestRefereeListener',
						'file' => $path . 'test_referee_listener.php',
						'ident' => 'Standard 1'
					),
					array(
						'class' => 'TestRefereeListener',
						'file' => $path . 'test_referee_listener.php',
						'ident' => 'Standard 2'
					)
				),
				'CustomTestListener' => array(
					'class' => 'CustomTestListener',
					'file' => $path . 'custom_test_listener.php'
				)
			)
		));
	}

	/**
	 * @return void
	 */
	public function endTest() {
		unset($this->RefereeWhistleProxy);
		ClassRegistry::flush();
	}

	/**
	 * @return void
	 */
	public function testListenersLoaded() {
		$this->assertTrue($this->RefereeWhistleProxy->getListener('Standard 1'));
		$this->assertTrue($this->RefereeWhistleProxy->getListener('Standard 2'));
		$this->assertTrue($this->RefereeWhistleProxy->getListener('Custom'));
	}

	/**
	 * @return void
	 */
	public function testDisabled() {

		$this->assertFalse($this->RefereeWhistleProxy->getListeners());

	}

	/**
	 * @return void
	 */
	public function testHandlers() {

		$this->RefereeWhistleProxy->registerHandlers();

		trigger_error('testing notice', E_USER_NOTICE);

		$listener = $this->RefereeWhistleProxy->getListener('Standard 1');
		$this->assertTrue($listener->error['message'] == 'testing notice');
		$this->assertTrue($listener->error['level'] == E_USER_NOTICE);

		$listener = $this->RefereeWhistleProxy->getListener('Standard 2');
		$this->assertTrue($listener->error['message'] == 'testing notice');
		$this->assertTrue($listener->error['level'] == E_USER_NOTICE);

		$listener = $this->RefereeWhistleProxy->getListener('Custom');
		$this->assertTrue($listener->error['message'] == 'testing notice');
		$this->assertTrue($listener->error['level'] == E_USER_NOTICE);

		restore_error_handler();

	}

}

?>