<?php

App::import('Lib', 'Referee.RefereeWhistle');

/**
 * WhistleComponent
 *
 * Tacks into PHP's error handling to provide an easy way to attach custom
 * listeners for errors that occur during execution.
 *
 * @author  Joe Beeson <jbeeson@gmail.com>
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @see     http://blog.joebeeson.com/monitoring-your-applications-health/
 * @uses    RefereeWhistle
 * @todo    Add method to manually log errors (->error())
 */
class WhistleComponent extends RefereeWhistle {

	/**
	 * @var Controller
	 */
	protected $_controller;

	/**
	 * Include HTTP request information, if present
	 *
	 * @var boolean
	 */
	public $includeRequest = true;

	/**
	 * Get relevant controller parameters
	 *
	 * @return array
	 */
	protected function _getControllerParams() {
		$params = array(
			'request_method' => env('REQUEST_METHOD'),
			'request_plugin' => !empty($this->_controller->params['plugin']) ? $this->_controller->params['plugin'] : null,
			'request_controller' => !empty($this->_controller->params['controller']) ? $this->_controller->params['controller'] : Inflector::underscore($this->_controller->name),
			'request_action' => !empty($this->_controller->params['action']) ? $this->_controller->params['action'] : Inflector::underscore($this->_controller->action),
			'request_ext' => !empty($this->_controller->params['url']['ext']) ? $this->_controller->params['url']['ext'] : null
		);
		$data = !empty($this->_controller->data) ? $this->_controller->data : array();
		if (!empty($data['_Token'])) {
			unset($data['_Token']);
		}
		$params['request_parameters'] = array_filter(array(
			'url' => $this->_controller->here,
			'data' => $data,
			'pass' => !empty($this->_controller->params['pass']) ? $this->_controller->params['pass'] : array(),
			'named' => !empty($this->_controller->params['named']) ? $this->_controller->params['named'] : array(),
			'form' => !empty($this->_controller->params['form']) ? $this->_controller->params['form'] : array()
		));
		return array_filter($params);
	}

	/**
	 * Get/parse error data
	 *
	 * @param   array $error
	 * @return  void
	 */
	protected function _getErrorData($error = array()) {
		$error = parent::_getErrorData($error);
		if($this->includeRequest) {
			$error += $this->_getControllerParams();
		}
		return $error;
	}

	/**
	 * Initialization method executed prior to the controller's beforeFilter
	 * method but after the model instantiation.
	 *
	 * @param   Controller  $controller
	 * @param   array       $config
	 * @return  void
	 */
	public function initialize(&$controller, $config = array()) {
		$this->_controller = $controller;
		$this->_initialize($config);
	}

}

?>