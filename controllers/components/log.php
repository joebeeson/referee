<?php

	/**
	 * Tacks into PHP's error handling stack and adds functionality to 
	 * store and act upon certain errors while suppressing them from the
	 * view if debug is turned off.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class LogComponent extends Object {
		
		/**
		 * Helps us translate error integers back into their respective
		 * human readable levels.
		 * @var array
		 * @static
		 */
		static $levels = array(
			1     => 'E_ERROR',
			2     => 'E_WARNING',
			4     => 'E_PARSE',
			8     => 'E_NOTICE',
			16    => 'E_CORE_ERROR',
			32    => 'E_CORE_WARNING',
			64    => 'E_COMPILE_ERROR',
			128   => 'E_COMPILE_WARNING',
			256   => 'E_USER_ERROR',
			512   => 'E_USER_WARNING',
			1024  => 'E_USER_NOTICE',
			2048  => 'E_STRICT',
			4096  => 'E_RECOVERABLE_ERROR',
			8192  => 'E_DEPRECATED',
			16384 => 'E_USER_DEPRECATED',
			30719 => 'E_ALL'
		);
		
		/**
		 * Our Error model for saving data.
		 * @var Model
		 * @access protected
		 */
		protected $Error;
		
		/**
		 * Register ourselves as the error handler.
		 * @return null
		 * @access public
		 */
		public function initialize() {
			set_error_handler(array($this, '__error'));
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
			// Determine the method to call by the error thrown
			$method = strtolower(str_replace('E_', '', LogComponent::$levels[$level]));
			/**
			 * Returning false will cause PHP's internal error handler
			 * to execute normally. By default we tell it not to but if
			 * the method we call says otherwise, we do that.
			 */
			return (
				$this->$method($string, $file, $line, $context) 
				or 
				!Configure::read()
			);
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
				$this->Error = ClassRegistry::init('Journal.Error');
			}
			// Reset our Error model before we write to it.
			$this->Error->create();
			// Cover our bases and set the "created" column manually
			$created = date('Y-m-d H:i:s');
			$this->Error->save(compact('level', 'message', 'file', 'line', 'created'));
		}
		
		/**
		 * Handles E_STRICT error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function strict($message, $file, $line, $context) {
			// You DO NOT want to log strict errors. There are a lot.
		}
		
		/**
		 * Handles E_NOTICE error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function notice($message, $file, $line, $context) {
			$this->logError('notice', $message, $file, $line);
		}
		
		/**
		 * Handles E_WARNING error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function warning($message, $file, $line, $context) {
			$this->logError('warning', $message, $file, $line);
		}
		
		/**
		 * Handles E_ERROR error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function error($message, $file, $line, $context) {
			$this->logError('error', $message, $file, $line);
			// Stop execution
			return false;
		}
		
		/**
		 * Handles E_PARSE error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function parse($message, $file, $line, $context) {
			$this->logError('parse', $message, $file, $line);
		}
		
		/**
		 * Handles E_USER_WARNING error messages
		 * @param string $message
		 * @param string $file
		 * @param integer $line
		 * @param array $context
		 * @return null
		 * @access private
		 */
		private function user_warning($message, $file, $line, $context) {
			$this->logError('user_warning', $message, $file, $line);
		}
		
	}
