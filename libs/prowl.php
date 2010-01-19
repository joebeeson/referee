<?php

	// Utilize Cake's HttpSocket for the API usage
	App::import('Core', 'HttpSocket');

	/**
	 * A class to facilitate the usage of Prowl, the iPhone notification
	 * service. Expects to be passed your API key on construction.
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	class Prowl {
		
		/**
		 * Our API key
		 * @var string
		 * @access protected
		 */
		protected $api;
		
		/**
		 * Our HttpSocket instance
		 * @var HttpSocket
		 * @access protected
		 */
		protected $socket;

		/**
		 * Construction method, expects to be passed our API key
		 * @param string $api
		 * @return null
		 * @access public
		 */
		public function __construct($api = '') {
			if (empty($api)) {
				throw new BadMethodCallException('Prowl::__construct() expects to be passed an API key');
			} else {
				$this->api = $api;
				$this->socket = new HttpSocket();
			}
		}
		
		/**
		 * Fires a notification off to the service. Returns true for a
		 * successful notification being sent.
		 * @param string $application
		 * @param string $event
		 * @param string $description
		 * @param integer $priority
		 * @return boolean
		 * @access public
		 */
		public function notify($application, $event, $description, $priority = 0) {
			if (!in_array($priority, range(-2, 2))) {
				throw new OutOfRangeException('Prowl::notify() was passed in incorrect $priority, '.$priority);
			} else {
				// Fire off the request 
				$result = $this->socket->post('https://prowl.weks.net/publicapi/add', array(
					'apikey'	  => $this->api,
					'application' => $application,
					'event'		  => $event,
					'description' => $description,
					'priority'	  => $priority
				));
				return (stristr($result, 'success') !== false);
			}
		}
		
	}
