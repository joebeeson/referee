<?php

	// We need the Observable library for our listeners
	App::import('Lib', 'Referee.observable');
	
	// Make sure we have the ErrorHandler in place
	App::import('Core', 'Error');
	
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
		 * Our collection of errors to be logged.
		 * @var array
		 * @access protected
		 */
		protected $errors;
		
		/**
		 * Initialization actions
		 * @return null
		 * @access public
		 */
		public function initialize() {
			// Tell ClassRegistry about ourself.
			ClassRegistry::addObject('Referee.Log', $this);
			// Get our grubby paws on the errors and load our listeners
			$this->attachHandlers();
			$this->loadListeners();
		}
		
		/**
		 * Attach our methods to the various handlers.
		 * @return null
		 * @access private
		 */
		private function attachHandlers() {
			// Set us as the error handler and attach Cake's as a listener
			$this->attach('*', set_error_handler(array($this, '__error')));
			// Register a shutdown function to catch __fatal errors
			register_shutdown_function(array($this, '__shutdown'));
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
		public function __error($level, $message, $file, $line, $context) {
			// Translate to a human readable error level
			$error = strtolower(str_replace('E_', '', LogComponent::$levels[$level]));
			
			// We don't act upon E_STRICT since there are a ton of them.
			if ($error != 'strict') {
				// Log the event and notify any listeners
				$this->errors[] = array(
					'level' => $error,	'message' => $message,
					'file' => $file,	'line' => $line,
				);
				$this->notify($level, $level, $message, $file, $line, $context);
			}
			// Allow PHP's error handler to take over if we're in debug
			return Configure::read();
		}
		
		/**
		 * Handles writing out our errors to the database
		 * @return null
		 * @access private
		 */
		private function writeOutErrors() {
			if (!empty($this->errors)) {
				ClassRegistry::init('Referee.Error')->saveAll($this->errors);
			}
		}
		
		/**
		 * Registered as a shutdown function, checks if we stopped for a
		 * fatal error of sorts so that we can catch and log it. We use
		 * this time to write our errors to the database.
		 * @return null
		 * @access public
		 */
		public function __shutdown() {
			$error = error_get_last();
			if (in_array($error['type'], array(E_ERROR, E_USER_ERROR, E_PARSE))) {
				extract($error);
				
				/**
				 * We have to append the error here because once Cake is
				 * notified of the error it will stop all execution and
				 * wont give us a chance to log the error ourself.
				 */
				$this->errors[] = array(
					'level'   => strtolower(str_replace('E_', '', LogComponent::$levels[$type])),	
					'message' => $message,
					'file' 	  => $file,	
					'line' 	  => $line,
				);
				$this->writeOutErrors();
				$this->__error($type, $message, $file, $line, array());
			}
			$this->writeOutErrors();
		}
		
	}
