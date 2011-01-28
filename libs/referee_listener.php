<?php

/**
 * RefereeListener
 *
 * Provides abstract base for listeners
 *
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @uses    Object
 */
abstract class RefereeListener extends Object {

	/**
	 * Mapping of our error levels to their names.
	 *
	 * @var array
	 */
	protected $_errorLevels = array(
		E_ERROR 			=> 'E_ERROR',
		E_WARNING			=> 'E_WARNING',
		E_PARSE				=> 'E_PARSE',
		E_NOTICE			=> 'E_NOTICE',
		E_CORE_ERROR		=> 'E_CORE_ERROR',
		E_CORE_WARNING    	=> 'E_CORE_WARNING',
		E_COMPILE_ERROR    	=> 'E_COMPILE_ERROR',
		E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
		E_USER_ERROR   		=> 'E_USER_ERROR',
		E_USER_WARNING   	=> 'E_USER_WARNING',
		E_USER_NOTICE  		=> 'E_USER_NOTICE',
		E_STRICT  			=> 'E_STRICT',
		E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
		E_DEPRECATED  		=> 'E_DEPRECATED',
	);

	/**
	 * Level to log errors at
	 *
	 * @var integer
	 */
	public $errorLevels = E_ALL;

	/**
	 * Method to call on error
	 *
	 * @var string
	 */
	public $method = 'error';

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		$this->_set($config);
	}

	/**
	 * Translates the `$level` integer into its human readable form.
	 *
	 * @param   integer $level
	 * @return  string
	 */
	protected function _translateError($level) {
		return !empty($this->_errorLevels[$level]) ? $this->_errorLevels[$level] : 'E_UNKNOWN';
	}

	/**
	 * Triggered when we're passed an error from the `WhistleComponent`
	 *
	 * @param   array $error
	 * @return  void
	 */
	abstract public function error($error);

}

?>