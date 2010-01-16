<?php

	class JournalController extends JournalAppController {

		/**
		 * Models
		 * @var array
		 * @access public
		 */
		public $uses = array(
			'Journal.Error'
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
			if (isset($this->Auth)) {
				$this->Auth->allow(array('index', 'today'));
			}
			Configure::write('Cache.disable', true);
			parent::beforeFilter();
		}
	
		public function index() {
			pr($this->Error->day('today', 'count'));
			die;
			die;
		}
		
		public function today() {
			$count  = $this->Error->day('today', 'count');
			$this->paginate['conditions'] = array(
				'`Error`.`created` >' => date('Y-m-d H:i:s', strtotime('today'))
			);
			$errors = $this->paginate();
			$this->set(compact('count', 'errors'));
		}
		
	}
