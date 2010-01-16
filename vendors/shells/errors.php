<?php

	/**
	 * Provides administrator tools to inspect the errors logged by the
	 * Referee plugin through the command line.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class ErrorsShell extends Shell {
		
		/**
		 * Models
		 * @var array
		 * @access public
		 */
		public $uses = array(
			'Referee.Error'
		);
		
		/**
		 * Initialization method. We have to jump through some hoops to
		 * allow our shell to work correctly.
		 * @return null
		 * @access public
		 */
		public function initialize() {
			require(realpath(CAKE.'..'.DS).DS.'app'.DS.'config'.DS.'database.php');
			require(CAKE.'libs'.DS.'model'.DS.'model.php');
			require(CAKE.'libs'.DS.'model'.DS.'app_model.php');
			parent::initialize();
		}
		
		/**
		 * Monitors the Error model for new records in realtime
		 * @return null
		 * @access public
		 */
		public function monitor() {
			$this->out('');
			set_time_limit(0);
			$olderThan = date('Y-m-d H:i:s');
			while (true) {
				// Search for any errors we haven't yet seen
				$errors = $this->Error->find('all', array(
					'conditions' => array('created >' => $olderThan)
				));
				
				// We found some! Display them and update $olderThan
				if (!empty($errors)) {
					foreach ($errors as $error) {
						$this->out('Severity: '.$error['Error']['level']);
						$this->out('Location: '.$error['Error']['file'].':'.$error['Error']['line']);
						$this->out($error['Error']['message']);
						$this->hr(1);
					}
					$olderThan = date('Y-m-d H:i:s');
				}
				
				// We don't want to destroy the database, wait a second
				sleep(1);
			}
		}
		
	}
