<?php

	// We need the Observable library for our listeners
	App::import('Lib', 'Referee.observable');
	
	// When running via the TestShell we have to make sure we don't do this twice
	if (!class_exists('ErrorHandler')) {
		// Make sure we have the ErrorHandler in place
		App::import('Core', 'Error');
	}
	
	/**
	 * WhistleComponent
	 * 
	 * Tacks into PHP's error handling stack. Extends Observable for any class to 
	 * tack into our error events. Any PHP file in found inside vendors/listeners/ 
	 * will be loaded automatically.
	 * 
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class WhistleComponent extends Observable {
		
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
		protected $errors = array();
		
		/**
		 * Folder with listeners to load up
		 * @var string
		 * @access protected
		 */
		protected $listeners;
		
		/**
		 * Initialization method. Attaches our error handlers into the current
		 * execution and loads all of our error listeners.
		 * 
		 * @return null
		 * @access public
		 */
		public function initialize() {
			// Tell ClassRegistry about ourself
			ClassRegistry::addObject('Referee.Whistle', $this);
			
			// Get our grubby paws on the errors and load our listeners
			$this->attachHandlers();
			$this->loadListeners();
		}
		
		/**
		 * Performs the actual attaching of our error handlers which includes
		 * __error() and __shutdown() -- We require a shutdown handler to catch
		 * any fatal errors before PHP halts.
		 * 
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
		 * Loops through our listeners directory and loads any file that ends in
		 * ".php", it is assumed these are our error listeners. They can attach
		 * to our instance by ClassRegistry::getObject('Referee.Whistle')
		 * 
		 * @return null
		 * @access private
		 */
		private function loadListeners() {
			$directory = realpath(dirname(__FILE__).'/../../vendors/listeners');
			$directory = new Folder($directory);
			foreach ($directory->find('.+\.php') as $listener) {
				require($directory->path.DS.$listener);
			}
		}
		
		/**
		 * Handles any errors thrown in the current execution. Returns a boolean
		 * that matches our debug level to fire off PHP's normal error handler.
		 * 
		 * @param integer $level
		 * @param string $string
		 * $param string $file
		 * @param integer $line
		 * @param array $context
		 * @return boolean
		 * @access public
		 */
		public function __error($level, $message, $file, $line, $context) {
			// Translate to a human readable error level
			$error = self::translateError($level);
			
			// We don't act upon E_STRICT since there are a *ton* of them.
			if ($error != 'strict') {
				$this->logError($error, $message, $file, $line);
				$this->notify($level, $level, $message, $file, $line, $context);
			}
			
			// Allow PHP's error handler to take over if we're in debug
			return (boolean) Configure::read();
		}
		
		/**
		 * Appends the error to our $errors variable for use later. If 
		 * the passed error is fatal we start the writeOutErrors now.
		 * 
		 * @param string $level
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @return null
		 * @access private
		 */
		private function logError($level, $message, $file, $line) {
			$this->errors[] = compact('level', 'message', 'file', 'line');
			if (self::isFatal($level)) {
				
				// Execution will be ending soon, write out our errors now
				$this->writeOutErrors();
			}
		}
		
		/**
		 * Handles writing out our errors to the database. Clears the
		 * $errors variable back to an empty array.
		 * 
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
		 * 
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
		 * corresponding, human readable error level.
		 * 
		 * @param integer $level
		 * @return string
		 * @access public
		 * @static
		 */
		public static function translateError($level = 0) {
			if (isset(self::$levels[$level])) {
				return strtolower(str_replace('E_', '', self::$levels[$level]));
			} 
			return null;
		}
		
		/**
		 * Our normal error handler isn't capable of catching fatal errors, PHP
		 * likes to just close up shop and go home when one occurs during but
		 * this function *always* is fired right before our execution shutdown 
		 * which means we can check if the last error was fatal and log it. Yay.
		 * 
		 * @see http://php.net/set_error_handler
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
		
		/**
		 * Accessor method to our $errors member variable
		 * @return array
		 * @access public
		 */
		public function getErrors() {
			return $this->errors;
		}
		
	}
