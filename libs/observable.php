<?php

	/**
	 * Observable pattern. We don't make use of the SplObserver pattern
	 * because it doesn't allow for us to fire named events.
	 * @author Joe Beeson <joe@joebeeson.com>
	 * @abstract
	 */
	abstract class Observable {
		
		/**
		 * Our registered callbacks and events. 
		 * array('event' => array(callback, callback))
		 * @param array
		 * @access private
		 */
		private $events = array();
		
		/**
		 * Attaches a $callback to an $event
		 * @param string $event
		 * @param callback $callback
		 * @access public
		 * @final
		 */
		final public function attach($event, $callback) {
			if (is_callable($callback)) {
				$this->events[$event][] = $callback;
			} else {
				throw new InvalidArgumentException(get_class($this).'::attach() was passed a non-callable $callback');
			}
		}
		
		/**
		 * Removes a callback from the $event. Returns boolean.
		 * @param string $event
		 * @param callback $callback
		 * @return boolean
		 * @access public
		 * @final
		 */
		final public function detach($event, $callback) {
			if (isset($this->events[$event])) {
				foreach ($this->events[$event] as $key=>$listener) {
					if ($listener === $callback) {
						unset($this->events[$event][$key]);
						
						// If that was the last event, lets clean up
						if (empty($this->events[$event])) {
							unset($this->events[$event]);
						}
						return true;
					}
				}
			}
		}
		
		/**
		 * Fires an event off to its observers. Any passed arguments
		 * provided beyond $event is passed on to the observers. Returns
		 * all of the returned values from its observers
		 * @param string $event
		 * @return array
		 * @access public
		 * @final
		 */
		final public function notify($event) {
			$arguments = func_get_args();
			$event     = array_shift($arguments);
			$return    = array();
			foreach ($this->getListeners($event) as $callback) {
				$return[] = call_user_func_array($callback, $arguments);
			}
			// Support for universal notifications
			foreach ($this->getListeners('*') as $callback) {
				$return[] = call_user_func_array($callback, $arguments);
			}
			return $return;
		}
		
		/**
		 * Accessor method for our $events member variable.
		 * @return array
		 * @access public
		 * @final
		 */
		final public function getEvents() {
			return $this->events;
		}
		
		/**
		 * Returns all listeners for the requested $event
		 * @param string $event
		 * @return array
		 * @access public
		 * @final
		 */
		final public function getListeners($event) {
			if (isset($this->events[$event])) {
				return $this->events[$event];
			}
			return array();
		}
		
	}
