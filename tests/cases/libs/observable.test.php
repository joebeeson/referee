<?php

	// Load our required libraries
	App::import('Lib', 'Referee.Observable');
	
	/**
	 * ObservableLibTest
	 * Tests the Observable library for the Referee plugin.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	final class ObservableLibTest extends CakeTestCase {
		
		/**
		 * Performed piror to each test method is executed. Sets up our test
		 * environment for the following test.
		 * @return null
		 * @access public
		 */
		public function startTest() {
			$this->Observable = new TestObservable();
			$this->Observer   = new TestObserver();
		}
		
		/**
		 * Performed after each test method is executed. Resets our environment
		 * for the next test.
		 * @return null
		 * @access public
		 */
		public function endTest() {
			unset(
				$this->Observable,
				$this->Observer
			);
		}
		
		/**
		 * Tests the "universal notification" of the Observable::notify method
		 * @return null
		 * @access public
		 */
		public function testNotifyUniversal() {
			// Attach our Observer's "catcher" method...
			$this->Observable->attach('*', array($this->Observer, 'catcher'));
			
			// Fire an event with a silly event name
			$this->Observable->notify('this should trigger the universal notification', 'universal');
			$this->assertIdentical(
				$this->Observer->events,
				array('universal')
			);
		}
		
		/**
		 * Tests the Observable::notify method
		 * @return null
		 * @access public
		 */
		public function testNotify() {
			// Attach our Observer's "catcher" method...
			$this->Observable->attach('testAttach', array($this->Observer, 'catcher'));
			
			// Fire an event that noone is listening for
			$this->Observable->notify('fakeEvent!');
			$this->assertIdentical(
				$this->Observer->events,
				null
			);
			
			// Now fire an event we are listening for and confirm the argument
			$this->Observable->notify('testAttach', 'Argument');
			$this->assertIdentical(
				$this->Observer->events,
				array('Argument')
			);
		}
		
		/**
		 * Tests the Observable::attach() method
		 * @return null
		 * @access public
		 */
		public function testAttach() {
			// Our events array should be empty...
			$this->assertIdentical(
				$this->Observable->getEvents(),
				array()
			);
			
			// Attach our Observer's "catcher" method...
			$this->Observable->attach('testAttach', array($this->Observer, 'catcher'));
			
			// Make sure that it's our class and method that is attached
			$this->assertIdentical(
				$this->Observable->getListeners('testAttach'),
				array(array($this->Observer, 'catcher'))
			);
			
			// Lets cause an exception
			$this->expectException('InvalidArgumentException');
			$this->Observable->attach('testException', 'this isnt a callback!');
		}
		
		/**
		 * Tests the Observable::detach() method
		 * @return null
		 * @access public
		 */
		public function testDetach() {
			// Our events array should be empty...
			$this->assertIdentical(
				$this->Observable->getEvents(),
				array()
			);
			
			// Attach our Observer's "catcher" method...
			$this->Observable->attach('testDetach', array($this->Observer, 'catcher'));
			
			// Make sure that it's our class and method that is attached
			$this->assertIdentical(
				$this->Observable->getListeners('testDetach'),
				array(array($this->Observer, 'catcher'))
			);
			
			// Now lets detach our listener...
			$this->Observable->detach('testDetach', array($this->Observer, 'catcher'));
			
			// Our events array should be empty...
			$this->assertIdentical(
				$this->Observable->getEvents(),
				array()
			);
		}
		
		/**
		 * Tests the Observable::getListeners() method
		 * @return null
		 * @access public
		 */
		public function testListeners() {
			$this->assertIdentical(
				$this->Observable->getListeners('fake event!'),
				array()
			);
			
			// Attach our Observer's "catcher" method...
			$this->Observable->attach('testDetach', array($this->Observer, 'catcher'));
			
			// Make sure that it's our class and method that is attached
			$this->assertIdentical(
				$this->Observable->getListeners('testDetach'),
				array(array($this->Observer, 'catcher'))
			);
		}
		
	}
	
	/**
	 * TestObservable
	 * Test class for firing events from.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class TestObservable extends Observable {
		
		/**
		 * "Pitches" events through observer
		 * @param string $event
		 * @param array $arguments
		 * @return null
		 * @access public
		 */
		public function pitcher($event, $arguments = array()) {
			$this->notify($event, $arguments);
		}
		
	}
	
	/**
	 * TestObserver
	 * Test class for receiving events.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class TestObserver {
		
		/**
		 * Events we've caught
		 * @var array
		 * @access public
		 */
		public $events;
		
		/**
		 * Catches thrown events
		 * @return null
		 * @access public
		 */
		public function catcher() {
			$this->events = func_get_args();
		}
		
	}