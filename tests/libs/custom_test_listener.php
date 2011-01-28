<?php

/**
 * CustomTestListener
 *
 * Small listener to attach to the RefereeWhistle and confirm that
 * it's operating correctly.
 *
 * @package     referee
 * @subpackage  referee.tests.libs
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class CustomTestListener {

	/**
	 * @var array
	 */
	public $error;

	/**
	 * @var string
	 */
	public $ident = 'Custom';

	/**
	 * @var integer
	 */
	public $errorLevels = E_ALL;

	/**
	 * @var string
	 */
	public $method = 'customError';

	/**
	 * A custom method for handling errors
	 *
	 * @param   array $error
	 * @return  void
	 */
	public function customError($error) {
		$this->error = $error;
	}

}

?>