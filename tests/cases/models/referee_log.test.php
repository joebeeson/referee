<?php

error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);

/**
 * RefereeLog Test
 *
 * @package     referee
 * @subpackage  referee.tests.cases.model
 * @see         RefereeLog
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class RefereeLogTestCase extends CakeTestCase {

	/**
	 * @var array
	 */
	public $fixtures = array(
		'plugin.referee.referee_log'
	);

	/**
	 * @return void
	 */
	public function startTest() {
		$this->RefereeLog = ClassRegistry::init('Referee.RefereeLog');
	}

	/**
	 * @return void
	 */
	public function testInstantiation() {
		$this->assertTrue(is_a($this->RefereeLog, 'Model'));
	}

	/**
	 * @return void
	 */
	public function testValidation() {
		$data = array(
			'level' => null,
			'file' => null,
			'line' => null
		);
		$this->RefereeLog->create();
		$result = $this->RefereeLog->save($data);
		$this->assertFalse($result, 'Should not be able to pass validation with errors.');
		$this->assertEqual(count($data), count($this->RefereeLog->invalidFields()));
	}

}

?>