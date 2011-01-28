<?php

App::import('Lib', 'Referee.RefereeListener');

/**
 * TestRefereeListener
 *
 * @package     referee
 * @subpackage  referee.tests.libs
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @uses        RefereeListener
 */
class TestRefereeListener extends RefereeListener {

	/**
	 * @var string
	 */
	public $ident;

	/**
	 * @var array
	 */
	public $error;

	/**
	 * @param   array $error
	 * @return  void
	 */
	public function error($error) {
		$this->error = $error;
	}

}

?>