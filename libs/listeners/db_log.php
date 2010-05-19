<?php

	/**
	 * DbLogListener
	 * Provides functionality for logging errors to the database. 
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class DbLogListener {
		
		/**
		 * Holds our current configuration
		 * @var array
		 * @access protected
		 */
		protected $configuration;
		
		/**
		 * Holds our default configuration options
		 * @var array
		 * @access protected
		 */
		protected $defaults = array(
			
			/**
			 * This is the model we will attempt to use. We will check if it
			 * exists prior to saving a new record.
			 */
			'model' => 'error',
			
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
				'string' => array(
					'message',
					'error',
					'string'
				),
				'line' => array(
					'line',
				)
			)
		);
		
		public function error($error, $configuration = array()) {
			extract($this->_setConfiguration($configuration));
			if (in_array($model, Configure::listObjects('model'))) {
				
			}
		}
		
		/**
		 * Convenience method for setting our configuration array. 
		 * @param array $configuration
		 * @return array
		 * @access protected
		 */
		protected function _setConfiguration($configuration) {
			$this->configuration = am(
				$this->defaults,
				$configuration
			);
			return $this->configuration;
		}
		
	}