<?php

	/**
	 * DbLogListener
	 *
	 * Provides functionality for logging errors to the database.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 * @see http://blog.joebeeson.com/monitoring-your-applications-health/
	 */
	class DbLogListener {

		/**
		 * Holds our current configuration.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_configuration = array();

		/**
		 * Holds our model instance
		 *
		 * @var Model
		 * @access protected
		 */
		protected $_model;

		/**
		 * Holds our default configuration options.
		 *
		 * @var array
		 * @access protected
		 */
		protected $_defaults = array(

			/**
			 * This is the model we will attempt to use when saving the error
			 * record to the database. At the very least the table should exist
			 */
			'model' => 'Error',

			/**
			 * The key represents the key value we get from the error and the
			 * value represents the columns we will attempt to look for when
			 * saving the error to the database.
			 */
			'mapping' => array(
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
			)
		);

		/**
		 * Triggered when we're passed an error from the `WhistleComponent`
		 *
		 * @param array $error
		 * @apram array $configuration
		 * @return null
		 * @access public
		 */
		public function error($error, $configuration = array()) {
			extract($this->_setConfiguration($configuration));
			$this->_getModel()->save($this->_getSaveArray($error));
		}

		/**
		 * Maps our $error onto the correct columns, at least the ones that we
		 * can determine from the model schema.
		 *
		 * @param array $error
		 * @return array
		 * @access protected
		 */
		protected function _getSaveArray($error) {
			$schema  = array_keys($this->_getModel()->schema());
			$mapping = $this->_configuration['mapping'];
			$return  = array();
			foreach ($error as $key=>$value) {
				if (isset($mapping[$key])) {
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
						$return[$column] = $value;
					}
				}
			}
			return $return;
		}

		/**
		 * Convenience method for returning the model we should be using. We are
		 * reusing the same model so we minimize ClassRegistry::init() calls.
		 *
		 * @return Model
		 * @access protected
		 */
		protected function _getModel() {
			if (!isset($this->_model)) {

				// Initialize the original model
				$this->_model = ClassRegistry::init(
					$this->_configuration['model']
				);
			} else {

				// They're asking for a different model
				if ($this->_model->name != $this->_configuration['model']) {
					$this->_model = ClassRegistry::init(
						$this->_configuration['model']
					);
				}
			}

			// Return the setup model
			return $this->_model;
		}

		/**
		 * Convenience method for setting our configuration array.
		 *
		 * @param array $configuration
		 * @return array
		 * @access protected
		 */
		protected function _setConfiguration($configuration) {
			$this->_configuration = am(
				$this->_defaults,
				$configuration
			);
			return $this->_configuration;
		}

	}
