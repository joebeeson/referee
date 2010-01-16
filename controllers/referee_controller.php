<?php

	class RefereeController extends RefereeAppController {

		/**
		 * Models
		 * @var array
		 * @access public
		 */
		public $uses = array(
			'Referee.Error'
		);
		
		/**
		 * Pagination defaults
		 * @var array
		 * @access public
		 */
		public $paginate = array(
			'limit' => 25,
			'order' => 'Error.created DESC'
		);
		
		/**
		 * Helpers used
		 * @var array
		 * @access public
		 */
		public $helpers = array(
			'Paginator',
			'Time',
			'Html'
		);

		/**
		 * Check if AuthComponent is active and allow access if it is,
		 * also stop caching-- just in case.
		 * @return null
		 * @access public
		 */
		public function beforeFilter() {
			if (Configure::read()) {
				if (isset($this->Auth)) {
					$this->Auth->allow(array('index'));
				}
				Configure::write('Cache.disable', true);
				parent::beforeFilter();
			} else {
				// Referee plugin? What referee plugin?
				$this->cakeError('error404'); 
			}
		}

		/**
		 * Main (only) action
		 * @return null
		 * @acccess public
		 */
		public function index() {
			$errors = $this->paginate();
			$this->set(compact('errors'));
		}
		
	}
