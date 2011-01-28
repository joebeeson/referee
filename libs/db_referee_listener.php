<?php

App::import('Lib', 'Referee.RefereeListener');

/**
 * DbRefereeListener
 *
 * Provides functionality for logging errors to the database.
 *
 * @author  Joe Beeson <jbeeson@gmail.com>
 * @author  Joshua McNeese <jmcneese@gmail.com>
 * @see     http://blog.joebeeson.com/monitoring-your-applications-health/
 * @uses    RefereeListener
 */
class DbRefereeListener extends RefereeListener {

	/**
	 * Holds our model instance
	 *
	 * @var Model
	 */
	private $_model;

	/**
	 * This is the model we will attempt to use when saving the error
	 * record to the database.
	 *
	 * @var string
	 */
	protected $model = 'Referee.RefereeLog';

	/**
	 * The key represents the key value we get from the error and the
	 * value represents the columns we will attempt to look for when
	 * saving the error to the database.
	 *
	 * @var array
	 */
	protected $mapping = array(
		'level' => array(
			'level',
			'severity',
			'type'
		),
		'file' => array(
			'file',
			'location',
		),
		'message' => array(
			'message',
			'error',
			'string'
		),
		'line' => array(
			'line',
		),
		'url' => array(
			'url',
			'address',
			'location'
		)
	);

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->_model = ClassRegistry::init($this->model);
	}

	/**
	 * Triggered when we're passed an error from the `WhistleComponent`
	 *
	 * @param   array $error
	 * @return  void
	 */
	public function error($error) {
		$error['level'] = $this->_translateError($error['level']);
		$data = array();
		if ($this->_model->name == 'RefereeLog') {
			if(!empty($error['args'])) {
				$error['args'] = serialize($error['args']);
			}
			if(!empty($error['trace'])) {
				$error['trace'] = serialize($error['trace']);
			}
			if(!empty($error['request_parameters'])) {
				$error['request_parameters'] = serialize($error['request_parameters']);
			}
			$data = $error;
		} else {
			$schema  = array_keys($this->_model->schema());
			$mapping = $this->_config['mapping'];
			foreach ($error as $key=>$value) {
				if (!empty($mapping[$key])) {
					if (is_array($mapping[$key])) {
						$column = array_pop(
							array_intersect(
								$mapping[$key],
								$schema
							)
						);
					} else {
						$column = (in_array($mapping[$key], $schema)
							? $mapping[$key]
							: null
						);
					}
					if (!empty($column)) {
						$data[$column] = $value;
					}
				}
			}
		}
		$this->_model->save($data);
	}

}

?>