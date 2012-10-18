<?php

if(!defined('E_FATAL')) {
	define('E_FATAL', E_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_CORE_ERROR | E_PARSE);
}

if(!defined('E_DEFAULT')) {
	define('E_DEFAULT', E_ALL & ~E_STRICT & ~E_DEPRECATED);
}

/**
 * RefereeWhistle
 *
 * Tacks into PHP's error handling to provide an easy way to attach custom
 * listeners for errors that occur during execution.
 *
 * @author  Joe Beeson <jbeeson@gmail.com>
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @see     http://blog.joebeeson.com/monitoring-your-applications-health/
 * @uses    Object
 * @todo    Add method to manually log errors (->error())
 */
class RefereeWhistle extends Object {

	/**
	 * Holds the actual objects that represent our $listeners -- this way we
	 * reuse the objects instead of instantiating new ones for everything.
	 *
	 * @var array
	 */
	protected $_listeners = array();

	/**
	 * Disables the reporting of errors.
	 *
	 * @var boolean
	 */
	protected $_enabled = true;

	/**
	 * Include stacktrace, if possible
	 *
	 * @var boolean
	 */
	public $includeTrace = true;

	/**
	 * How many levels to return from backtrace
	 *
	 * @var integer
	 */
	public $traceDepth = 3;

	/**
	 * Error level to trigger our custom handler
	 *
	 * @var integer
	 */
	public $errorLevels = E_DEFAULT;

	/**
	 * What error levels we consider fatal (this is bitwise added levels)
	 * E.g. E_ERROR | E_PARSE
	 *
	 * @var integer
	 */
	public $fatal = E_FATAL;

	/**
	 * The listeners to relay errors to
	 *
	 * @var array
	 */
	public $listeners = array(
		'Syslog'
	);

	/**
	 * Constructor
	 *
	 * @param   array $config
	 * @return  void
	 */
	public function __construct($config = array()) {
		// Register our handler functions
		$this->registerHandlers();
		if(!empty($config)) {
			$this->initialize(new Controller(), $config);
		}
	}

	/**
	 * Initialize
	 *
	 * @param   array $config
	 * @return  void
	 */
	public function initialize(&$controller = null, $config = array()) {
		$this->_controller = $controller;
		// Set config
		$this->_set($config);
		// We don't want to execute when testing
		if (
			$this->_enabled === false ||
			Configure::read('Referee') === false
		) {
			$this->enable(false);
		} else {
			// Attach any passed listeners...
			$this->_loadListeners();
		}
	}

	/**
	 * Triggered when an error occurs during execution. We handle the process
	 * of looping through our listener configurations and seeing if there is
	 * anyone that matches our current error level and if so we will trigger
	 * the listener's error method.
	 *
	 * @param   integer $level
	 * @param   string  $string
	 * @param   string  $file
	 * @param   integer $line
	 * @return  void
	 */
	public function __error($level, $message, $file, $line) {
		$data = $this->_getErrorData(compact('level', 'message', 'file', 'line'));
		foreach ($this->_listeners as $listener) {
			if ($level & $listener->errorLevels) {
				$listener->{$listener->method}($data);
			}
		}
	}

	/**
	 * Executed via register_shutdown_function() in an attempt to catch any
	 * fatal errors before we stop execution. If we find one we kick it back
	 * out to our __error method to handle accordingly.
	 *
	 * @return void
	 */
	public function __shutdown() {
		$error = error_get_last();
		if (!empty($error['type']) && ((int)$error['type'] & $this->fatal)) {
			$this->__error($error['type'], $error['message'], $error['file'], $error['line']);
		}
	}

	/**
	 * Get/parse error data
	 *
	 * @param   array $error
	 * @return  void
	 * @todo    Improve stacktrace mechanism to not die on large traces
	 */
	protected function _getErrorData($error = array()) {
		$trace = debug_backtrace(false);
		if(!empty($trace[3])) {
			$first = $trace[3];
			if(empty($first['args'])) {
				unset($first['args']);
			}
			$error += $first;
			if($this->includeTrace) {
				$error['trace'] = array_slice($trace, 3, $this->traceDepth);
			}
		}
		return $error;
	}

	/**
	 * Load up any configured listeners
	 *
	 * @return void
	 */
	protected function _loadListeners() {
		if (!empty($this->listeners)) {
			foreach ($this->listeners as $listener=>$configs) {
				// Just in case they pass us a listener with no configuration
				if (is_numeric($listener)) {
					$listener = $configs;
					$configs = array();
				}
				if (!Set::numeric(array_keys($configs))) {
					$configs = array($configs);
				}
				$class = $listener;
				foreach ($configs as $config) {
					if (!class_exists($class)) {
						if (!empty($config['class']) && !empty($config['file'])) {
							require($config['file']);
							$class = $config['class'];
						} else {
							$class = $listener.'RefereeListener';
							App::import('Lib', 'Referee.'.$class);
						}
					}
				}
				if (class_exists($class)) {
					foreach ($configs as $config) {
						$this->_listeners[] = new $class($config);
					}
				}
			}
		}
	}

	/**
	 * Set object to be enabled or not
	 *
	 * @param   boolean $enabled
	 * @return  void
	 */
	public function enable($enabled = true) {
		$this->_enabled = $enabled;
	}

	/**
	 * Set our error handlers to be the default
	 *
	 * @return void
	 */
	public function registerHandlers() {
		// Attach our error handler for requested errors
		set_error_handler(array($this, '__error'), $this->errorLevels);
		// Register a shutdown function to catch fatal errors
		register_shutdown_function(array($this, '__shutdown'));
	}

}

?>