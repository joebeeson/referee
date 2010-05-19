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
	 * Tacks into PHP's error handling to provide an easy way to attach custom
	 * listeners for errors that occur during execution.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class WhistleComponent extends Object {
		
		/**
		 * Holds our listeners that are attached to our current execution and 
		 * their respective configuration for each.
		 * @var array
		 * @access protected
		 */
		protected $listeners = array();
		
		/**
		 * Holds the actual objects that represent our $listeners -- this way we
		 * reuse the objects instead of instantiating new ones for everything.
		 * @var array
		 * @access protected
		 */
		protected $objects = array();
		
		/**
		 * Holds the paths we should search for listeners in
		 * @var array
		 * @access protected
		 */
		protected $paths = array();
		
		/**
		 * Initialization method executed prior to the controller's beforeFilter
		 * method but after the model instantiation.
		 * @param Controller $controller
		 * @param array $listeners
		 */
		public function initialize($controller, $configuration = array()) {
			
			// Add our listeners directory to the paths
			$this->addListenerPath(
				App::pluginPath('referee') . 'libs' . DS . 'listeners'
			);
			
			// Setup any paths that we were given
			if (isset($configuration['paths'])) {
				$this->addListenerPath($configuration['paths']);
			}
			
			// Attach any passed listeners...
			if (isset($configuration['listeners'])) {
				$this->attachListeners($configuration['listeners']);
			}
			
			// Attach our error handler for all errors save for E_STRICT
			set_error_handler(array($this, '__error'), E_ALL ^ E_STRICT);
			
			// Register a shutdown function to catch fatal errors
			register_shutdown_function(array($this, '__shutdown'));
			
		}
		
		/**
		 * Triggered when an error occurs during execution. We handle the process
		 * of looping through our listener configurations and seeing if there is
		 * anyone that matches our current error level and if so we will trigger
		 * the listener's error method and pass along our parameters.
		 * @param integer $level
		 * @param string $string
		 * @param string $file
		 * @param integer $line
		 * @return boolean
		 * @access public
		 */
		public function __error($level, $string, $file, $line) {
			foreach ($this->listeners as $listener=>$configurations) {
				foreach ($configurations as $configuration) {
					if ($configuration['levels'] & $level) {
						$this->objects[$listener]->error(
							$level,
							$string,
							$file,
							$line
						);
					}
				}
			}
		}
		
		/**
		 * Executed via register_shutdown_function() in an attempt to catch any
		 * fatal errors before we stop execution. If we find one we kick it back
		 * out to our __error method to handle accordingly.
		 * @return null
		 * @access public
		 */
		public function __shutdown() {
			extract(error_get_last());
			if ($this->_isFatal($type)) {
				$this->__error($type, $message, $file, $line);
			}
		}
		
		/**
		 * Adds the given $paths to our paths member variable after we confirm
		 * that it is valid and doesn't already exist.
		 * @param mixed $paths
		 * @return null
		 * @access public
		 */
		public function addListenerPath($paths = '') {
			$paths = (!is_array($paths) ? array($paths) : $paths);
			foreach ($paths as $path) {
				if (file_exists($path) and !in_array($path, $this->paths)) {
					if (substr($path, -1) != DIRECTORY_SEPARATOR) {
						$path .= DIRECTORY_SEPARATOR;
					}
					$this->paths[] = $path;
				}
			}
		}
		
		/**
		 * Convenience method for attaching the passed $listeners to our current
		 * execution. If you need to know if the listener was properly attached
		 * you should use the attachListener method since it returns its success
		 * @param array $listeners
		 * @return null
		 * @access public
		 */
		public function attachListeners($listeners = array()) {
			foreach ($listeners as $listener=>$configuration) {
				// Just in case they pass us a listener with no configuration
				if (is_numeric($listener)) {
					$listener = $configuration;
					$configuration = array();
				}
				$this->attachListener($listener, $configuration);
			}
		}
		
		/**
		 * Attaches the passed $listener with the optional $configuration for it.
		 * We return boolean to indicate success or failure.
		 * @param string $listener
		 * @param array $configuration
		 * @return boolean
		 * @access public
		 */
		public function attachListener($listener, $configuration = array()) {
			if ($this->_loadListener($listener, $configuration)) {
				if ($this->_instantiateListener($listener)) {
					$this->_attachConfiguration($listener, $configuration);
					return true;
				}
			}
			return false;
		}
		
		/**
		 * Convenience method for attaching the supplied configuration to the
		 * given listener. We take into account the possibility of multiple 
		 * configurations for a listener.
		 * @param string $listener
		 * @param array $configuration
		 * @return null
		 * @access protected
		 */
		protected function _attachConfiguration($listener, $configuration = array()) {
			if ($this->_hasManyConfigurations($configuration)) {
				foreach ($configuration as $config) {
					$this->_attachConfiguration($listener, $config);
				}
			} else {
				$this->listeners[$listener][] = am(
					array(
						'levels' => E_ALL
					),
					$configuration
				);
			}
		}
		
		/**
		 * Convenience method for determining if the passed $configuration has
		 * more than one configuration in it, which signals that the listener in
		 * question wishes to have more than one instance.
		 * @param array $configuration
		 * @return boolean
		 * @access protected
		 */
		protected function _hasManyConfigurations($configuration = array()) {
				return (count(
					array_filter(
						array_map(
							'is_numeric', 
							array_keys($configuration)
						)
					)
				) > 0);
		}
		
		/**
		 * Creates the requested $listener object and attaches it to our objects
		 * member variable if we don't already have it available. Returns boolean
		 * to indicate success of our actions.
		 * @param string $listener
		 * @return boolean
		 * @access protected
		 */
		protected function _instantiateListener($listener = '') {
			$class = $this->_listenerClassname($listener);
			if (class_exists($class)) {
				if (!isset($this->objects[$listener])) {
					$this->objects[$listener] = new $class;
				}
				return true;
			}
			return false;
		}
		
		/**
		 * Attempts to load the provided $listener object. Returns boolean to
		 * indicate if we were successful or not.
		 * @param string $listener
		 * @param array $configuration
		 * @return boolean
		 * @access protected
		 */
		protected function _loadListener($listener = '', $configuration = array()) {
			if (!class_exists($this->_listenerClassname($listener))) {
				if (isset($configuration['file'])) {
					// The $configuration told us where to load the file...
					require($configuration['file']);
				} else {
					// We must search through our $paths for the file...
					foreach ($this->paths as $path) {
						$filePath = $path . $this->_listenerFilename($listener);
						if (file_exists($filePath)) {
							require($filePath);
						}
					}
				}
			}
			
			// If we managed to find it, this should return true...
			return class_exists($this->_listenerClassname($listener));
		}
		
		/**
		 * Convenience method for determining the expected class name for the
		 * given $listener
		 * @param string $listener
		 * @return string
		 * @access protected
		 */
		protected function _listenerClassname($listener = '') {
			return $listener . 'Listener';
		}
		
		/**
		 * Convenience method for determining the expected file name for the
		 * given $listener
		 * @param string $listener
		 * @return string
		 * @access protected
		 */
		protected function _listenerFilename($listener = '') {
			return Inflector::underscore($listener) . '.php';
		}
		
		/**
		 * Convenience method for determining if the passed level is fatal
		 * @param integer $level
		 * @return boolean
		 * @access protected
		 */
		protected function _isFatal($level = '') {
			return in_array(
				$level, 
				array(
					E_ERROR,
					E_USER_ERROR,
					E_PARSE
				)
			);
		}
		
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
	class WhistlerComponent extends Observable {
		
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
