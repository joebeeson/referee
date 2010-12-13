<?php

	/**
	 * SysLogListener
	 *
	 * Provides functionality for logging errors to the system logger
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 * @see http://blog.joebeeson.com/monitoring-your-applications-health/
	 */
	class SysLogListener {

		/**
		 * Holds our current configuration.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_configuration = array();

		/**
		 * Holds our default configuration options.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_defaults = array(
			'ident'  => 'CakePHP Application',
			'format' => 'Caught an %s error, "%s" in %s at line %s'
		);

		/**
		 * Mapping of our error levels to their names.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_levels = array(
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
		 * Triggered when we're passed an error from the `WhistleComponent`
		 *
		 * @param array $error
		 * @apram array $configuration
		 * @return null
		 * @access public
		 */
		public function error($error, $configuration = array()) {
			extract($this->_setConfiguration($configuration));
			extract($error);
			$level = $this->_translateError($level);
			$message = sprintf($format, $level, $message, $file, $line);
			syslog(LOG_INFO, $ident . ': ' . $message);
		}

		/**
		 * Translates the `$level` integer into its human readable form.
		 *
		 * @param integer $level
		 * @return string
		 * @access protected
		 */
		protected function _translateError($level) {
			if (isset($this->_levels[$level])) {
				return $this->_levels[$level];
			}
			return 'E_UNKNOWN';
		}

		/**
		 * Convenience method for setting our configuration array.
		 *
		 * @param array $configuration
		 * @return array
		 * @access protected
		 */
		protected function _setConfiguration($configuration) {
			$this->_configuration = am(
				$this->_defaults,
				$configuration
			);
			return $this->_configuration;
		}

	}
