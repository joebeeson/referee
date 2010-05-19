<?php

	/**
	 * SysLogListener
	 * Provides functionality for logging errors to the system logger
	 * @author Joe Beeson <jbeeson@gmail.com>
	 * @package RefereePlugin
	 */
	class SysLogListener {
		
		/**
		 * Holds our current configuration
		 * @var array
		 * @access protected
		 */
		protected $configuration;
		
		/**
		 * Holds our default configuration options
		 * @var array
		 * @access protected
		 */
		protected $defaults = array(
			'ident'  => 'CakePHP Application',
			'format' => 'Caught an %s error, "%s" in %s at line %s'
		);
		
		/**
		 * Mapping of our error levels to their names. It's a lot more user 
		 * friendly to say "We caught an E_ERROR" instead of "We caught 1"
		 * @var array
		 * @access protected
		 */
		protected $levels = array(
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
		 * Triggered when we're passed an error from the WhistleComponent
		 * @param array $error
		 * @apram array $configuration
		 * @return null
		 * @access public
		 */
		public function error($error, $configuration = array()) {
			extract($this->_setConfiguration($configuration));
			extract($error);
			$level = $this->_translateError($level);
			syslog(LOG_INFO, $ident . ': ' .sprintf($format, $level, $message, $file, $line));
		}
		
		/**
		 * Translates the $level integer into its human readable form.
		 * @param integer $level
		 * @return string
		 * @access protected
		 */
		protected function _translateError($level) {
			if (isset($this->levels[$level])) {
				return $this->levels[$level];
			}
			return 'E_UNKNOWN';
		}
		
		/**
		 * Convenience method for setting our configuration array. 
		 * @param array $configuration
		 * @return array
		 * @access protected
		 */
		protected function _setConfiguration($configuration) {
			$this->configuration = am(
				$this->defaults,
				$configuration
			);
			return $this->configuration;
		}
		
	}