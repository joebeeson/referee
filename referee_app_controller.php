<?php

	/**
	 * RefereeAppController
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class RefereeAppController extends AppController {

		/**
		 * Hides the plugin behind a 404 if we're not in debug mode. If
		 * AuthComponent is in use we tell it to allow our index action.
		 * @return null
		 * @access public
		 */
		public function beforeFilter() {
			if (Configure::read()) {
				if (isset($this->Auth)) {
					$this->Auth->allow(array('index'));
				}
				parent::beforeFilter();
			} else {
				// Referee plugin? What referee plugin?
				$this->cakeError('error404'); 
			}
		}
		
	}
