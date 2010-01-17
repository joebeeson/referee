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
		 * Main (only) action
		 * @return null
		 * @acccess public
		 */
		public function index() {
			$errors = $this->paginate();
			$this->set(compact('errors'));
		}
		
	}
