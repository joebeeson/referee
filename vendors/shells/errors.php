<?php

	/**
	 * Provides administrator tools to inspect the errors logged by the
	 * Referee plugin through the command line.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class ErrorsShell extends Shell {
		
		/**
		 * Models.
		 * @var array
		 * @access public
		 */
		public $uses = array(
			'Referee.Error'
		);
		
		/**
		 * Initialization method. We have to jump through some hoops to
		 * allow our shell to work correctly from a plugin path.
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
		 * Monitors the Error model for new records in realtime. Useful
		 * for production sites that don't display errors.
		 * @return null
		 * @access public
		 */
		public function monitor() {
			set_time_limit(0);
			$olderThan = date('Y-m-d H:i:s');
			while (true) {
				// Search for any errors we haven't yet seen.
				$errors = $this->Error->find('all', array(
					'conditions' => array('created >' => $olderThan)
				));
				
				// Found some. Display them and update our $olderThan
				if (!empty($errors)) {
					$this->displayError($errors);
					$this->out('');
					$olderThan = date('Y-m-d H:i:s');
				}
				
				// We don't want to destroy the database, wait a second
				sleep(1);
			}
		}
		
		/**
		 * Purges all records in the Errors model.
		 * @return null
		 * @access public
		 */
		public function purge() {
			$choice = $this->in('Are you sure?', array('y', 'n'));
			if ($choice == 'y') {
				$this->out('Deleting all Error records...');
				$this->Error->deleteAll();
			} else {
				$this->out('Operation aborted');
			}
		}
		
		/**
		 * Convenience method for displaying one or many errors. Accepts
		 * an array directly from the model. If passed more than one it
		 * will loop over it and display each.
		 * @param array $errors
		 * @return null
		 * @access private
		 */
		private function displayError($errors = array()) {
			if (!isset($errors['Error'])) {
				foreach ($errors as $error) {
					$this->displayError($error);
				}
			} else {
				extract($errors['Error']);
				$this->out("Severity: $level");
				$this->out("Location: $file:$line");
				$this->out($message);
			}
		}
		
	}
