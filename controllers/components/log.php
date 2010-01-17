<?php

	// We need the Observable library for our listeners
	App::import('Lib', 'Referee.observable');

	/**
	 * Tacks into PHP's error handling stack. Extends Observable for any
	 * class to tack into our error events. Any PHP file in found inside
	 * vendors/listeners/ will be loaded automatically.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class LogComponent extends Observable {
		
		/**
		 * Helps us translate error integers back into their respective
		 * human readable labels. We use the constant because their values
		 * can change between certain PHP versions.
		 * @var array
		 * @static
		 */
		public static $levels = array(
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
		 * Our Error model.
		 * @var Error
		 * @access protected
		 */
		protected $Error;
		
		/**
		 * Initialization actions
		 * @return null
		 * @access public
		 */
		public function initialize() {
			// Attach us as an event handler and shutdown function
			set_error_handler(array($this, '__error'));
			register_shutdown_function(array($this, '__shutdown'));
			// Let others find us by hooking into ClassRegistry
			ClassRegistry::addObject('Referee.Log', $this);
			// Load any listeners that may exist
			$this->loadListeners();
		}
		
		/**
		 * Brings any files in the listeners/ directory into execution
		 * @return null
		 * @access private
		 */
		private function loadListeners() {
			// TODO: Perhaps this should be configurable?
			$directory = realpath(dirname(__FILE__).'/../../vendors/listeners');
			$directory = new Folder($directory);
			foreach ($directory->find('.+\.php') as $listener) {
				require($directory->path.DS.$listener);
			}
		}
		
		/**
		 * Access point for errors to enter the class, dispatches the
		 * issue out to other methods
		 * @param integer $level
		 * @param string $string
		 * $param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access public
		 */
		public function __error($level, $string, $file, $line, $context) {
			// Translate to a human readable error level
			$error = strtolower(str_replace('E_', '', LogComponent::$levels[$level]));
			
			// We don't act upon E_STRICT since there are a ton of them.
			if ($error != 'strict') {
				// Log the event and notify any listeners
				$this->logError($error, $string, $file, $line);
				$this->notify($level, $string, $file, $line, $context);
			}
			
			// Returning false causes PHP's internal handler to fire.
			return !Configure::read();
		}

		/**
		 * Registered as a shutdown function, checks if we stopped for a
		 * fatal error of sorts so that we can catch and log it.
		 * @return null
		 * @access public
		 */
		public function __shutdown() {
			$error = error_get_last();
			if (in_array($error['type'], array(E_ERROR, E_USER_ERROR, E_PARSE))) {
				extract($error);
				$this->__error($type, $message, $file, $line, array());
			}
		}
		
		/**
		 * Stores the error in the database.
		 * @param string $level
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @return void
		 * @access private
		 */
		private function logError($level, $message, $file, $line) {
			// Initialize our Error model if we haven't yet
			if (empty($this->Error)) {
				$this->Error = ClassRegistry::init('Referee.Error');
			}
			// Reset our Error model before we write to it.
			$this->Error->create();
			$this->Error->save(compact('level', 'message', 'file', 'line'));
		}
		
	}
