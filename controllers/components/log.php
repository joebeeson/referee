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
		 * Our controller instance
		 * @var Controller
		 * @access protected
		 */
		protected $controller;
		
		/**
		 * Our collection of errors to be logged.
		 * @var array
		 * @access protected
		 */
		protected $errors;
		
		/**
		 * Path to our listeners directory
		 * @var string
		 * @access protected
		 */
		protected $directory;
		
		/**
		 * Initialization actions
		 * @return null
		 * @access public
		 */
		public function initialize($controller, $directory = '') {
			$this->controller = $controller;
			
			// Set our directory, tell ClassRegistry about ourself.
			ClassRegistry::addObject('Referee.Log', $this);
			$this->setDirectory($directory);
			
			// Get our grubby paws on the errors and load our listeners
			$this->attachHandlers();
			$this->loadListeners();
		}
		
		/**
		 * Validates and sets our listeners directory. If passed an empty
		 * string we will set our default directory.
		 * @param string $directory
		 * @return null
		 * @access private
		 */
		private function setDirectory($directory = '') {
			if (!empty($directory)) {
				if (!is_dir($directory)) {
					throw new RuntimeException('Referee::setDirectory() expects a directory');
				} else {
					$this->directory = $directory;
				}
			} else {
				$this->directory = dirname(__FILE__).'/../../vendors/listeners/';
			}
		}
		
		/**
		 * Attach our methods to the various handlers.
		 * @return null
		 * @access private
		 */
		private function attachHandlers() {
			// Set us as the error handler and grab Cake's error handler
			$errorHandler = set_error_handler(array($this, '__error'));
			
			// If we're in debug we want to attach Cake's handler
			if ($errorHandler !== null) {
				$this->attach('*', $errorHandler);
			}
			
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
			$directory = new Folder($this->directory);
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
			$error = self::translateError($level);
			
			// We don't act upon E_STRICT since there are a ton of them.
			if ($error != 'strict') {
				$this->logError($error, $message, $file, $line);
				$this->notify($level, $level, $message, $file, $line, $context);
			}
			// Allow PHP's error handler to take over if we're in debug
			return Configure::read();
		}
		
		/**
		 * Appends the error to our $errors variable for use later. If 
		 * the passed error is fatal we start the writeOutErrors now.
		 * @param string $level
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @return null
		 * @access private
		 */
		private function logError($level, $message, $file, $line) {
			$url = $this->controller->here;
			$this->errors[] = compact('level', 'message', 'file', 'line', 'url');
			if (self::isFatal($level)) {
				$this->writeOutErrors();
			}
		}
		
		/**
		 * Handles writing out our errors to the database. Clears the
		 * $errors variable back to an empty array.
		 * @return null
		 * @access private
		 */
		private function writeOutErrors() {
			if (!empty($this->errors)) {
				ClassRegistry::init('Referee.Error')->saveAll($this->errors);
				$this->errors = array();
			}
		}
		
		/**
		 * Convenience method for determining if the passed level is a
		 * fatal error level. Accepts integers or strings.
		 * @param mixed $level
		 * @return boolean
		 * @access public
		 * @static
		 */
		public static function isFatal($level = '') {
			$level = (is_int($level) ? self::translateError($level) : $level);
			return in_array($level, array('error', 'user_error', 'parse'));
		}
		
		/**
		 * Convenience method for translating an error integer into its
		 * corresponding, human readable, error text.
		 * @param integer $level
		 * @return string
		 * @access public
		 * @static
		 */
		public static function translateError($level = 0) {
			return strtolower(str_replace('E_', '', self::$levels[$level]));
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
			if (self::isFatal($error['type'])) {
				extract($error);
				$this->__error($type, $message, $file, $line, array());
			}
			// Execution is ending, write out our errors
			$this->writeOutErrors();
		}
		
	}
