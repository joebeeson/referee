<?php

	/**
	 * WhistleComponent
	 * Tacks into PHP's error handling to provide an easy way to attach custom
	 * listeners for errors that occur during execution.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class WhistleComponent {

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

			// Attach our error handler for all errors save for E_STRICT and E_DEPRECATED
			set_error_handler(array($this, '__error'), E_ALL & ~E_STRICT & ~E_DEPRECATED);

			// Register a shutdown function to catch fatal errors
			register_shutdown_function(array($this, '__shutdown'));

		}

		/**
		 * Triggered when an error occurs during execution. We handle the process
		 * of looping through our listener configurations and seeing if there is
		 * anyone that matches our current error level and if so we will trigger
		 * the listener's error method.
		 * @param integer $level
		 * @param string $string
		 * @param string $file
		 * @param integer $line
		 * @return null
		 * @access public
		 */
		public function __error($level, $message, $file, $line) {
			foreach ($this->listeners as $listener=>$configurations) {
				foreach ($configurations as $configuration) {
					if ($configuration['levels'] & $level) {
						$this->objects[$listener]->{$configuration['method']}(
							compact('level', 'message', 'file', 'line'),
							$configuration['parameters']
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
				if ($this->_instantiateListener($listener, $configuration)) {
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
				foreach ($configuration as $key=>$config) {
					if (is_numeric($key)) {
						$this->_attachConfiguration($listener, $config);
					}
				}
			} else {
				$this->listeners[$listener][] = array_merge(
					array(
						'levels' => E_ALL,
						'method' => 'error',
						'parameters' => array()
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
			if (is_array($configuration)) {
				unset($configuration['file'], $configuration['class']);
				return (count(
					array_filter(
						array_map(
							'is_numeric',
							array_keys($configuration)
						)
					)
				) > 0);
			}
			return false;
		}

		/**
		 * Creates the requested $listener object and attaches it to our objects
		 * member variable if we don't already have it available. Returns boolean
		 * to indicate success of our actions.
		 * @param string $listener
		 * @param array $configuration
		 * @return boolean
		 * @access protected
		 */
		protected function _instantiateListener($listener = '', $configuration = array()) {
			if (isset($configuration['class'])) {
				$class = $configuration['class'];
			} else {
				$class = $this->_listenerClassname($listener);
			}
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
			extract($configuration);
			$class = (isset($class) ? $class : $this->_listenerClassname($listener));
			if (!class_exists($class)) {
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
			return class_exists($class);
		}

		/**
		 * Convenience method for determining the expected class name for the
		 * given $listener
		 * @param string $listener
		 * @return string
		 * @access protected
		 */
		protected function _listenerClassname($listener = '') {
			return ucwords($listener) . 'Listener';
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
