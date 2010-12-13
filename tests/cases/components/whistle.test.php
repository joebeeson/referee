<?php

	/**
	 * WhistleComponentTest
	 *
	 * Tests the Whistle component for the Referee plugin.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class WhistleComponentTest extends CakeTestCase {

		/**
		 * Performed piror to each test method is executed.
		 *
		 * @return null
		 * @access public
		 */
		public function startTest() {
			$this->Whistle = new WhistleComponentProxy();
		}

		/**
		 * Performed after each test method is executed.
		 *
		 * @return null
		 * @access public
		 */
		public function endTest() {
			unset($this->Whistle);
			ClassRegistry::flush();
		}

		/**
		 * Tests the addListenerPath method of the component.
		 *
		 * @return null
		 * @access public
		 */
		public function testAddListenerPath() {

			// We shouldn't have any paths available
			$this->assertIdentical(
				$this->Whistle->_paths,
				array()
			);

			// Add a known existing path...
			$this->Whistle->addListenerPath(APP);

			// We should now have the path available
			$this->assertIdentical(
				$this->Whistle->_paths,
				array(
					APP
				)
			);

			// Make sure that adding a non-existant path fails
			$this->Whistle->addListenerPath(APP . uniqid());

			// We should still only have APP available
			$this->assertIdentical(
				$this->Whistle->_paths,
				array(
					APP
				)
			);

		}

		/**
		 * Tests the _listenerClassname method of the component.
		 *
		 * @return null
		 * @access public
		 */
		public function testListenerClassname() {
			$this->assertIdentical(
				$this->Whistle->_listenerClassname('Test'),
				'TestListener'
			);
		}

		/**
		 * Tests the attachListener method of the component. We attach a
		 * pretty plain listener.
		 *
		 * @return null
		 * @access public
		 */
		public function testAttachListenerVanilla() {
			$this->assertIdentical(
				$this->Whistle->_listeners,
				array()
			);

			$this->Whistle->initialize(new Controller());

			// Attach our listener
			$this->assertTrue($this->Whistle->attachListener('Test'));

			// Make sure it got the correct object
			$this->assertIsA(
				$this->Whistle->_objects['Test'],
				'TestListener'
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Test']->errors,
				array()
			);

			// WhistleComponent should gobble this up
			trigger_error('Testing error', E_USER_NOTICE);

			$this->assertIdentical(
				$this->Whistle->_objects['Test']->errors[0]['level'],
				E_USER_NOTICE
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Test']->errors[0]['file'],
				__FILE__
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Test']->errors[0]['message'],
				'Testing error'
			);

		}

		/**
		 * Tests the attachListener method of the component. We attach a
		 * listener with an overridden method and class.
		 *
		 * @return null
		 * @access public
		 */
		public function testAttachListenerOverride() {
			$this->assertIdentical(
				$this->Whistle->_listeners,
				array()
			);

			$this->Whistle->initialize(new Controller());

			// Attach our listener
			$this->assertTrue(
				$this->Whistle->attachListener(
					'Override',
					array(
						'class'  => 'TestListener',
						'method' => 'customError'
					)
				)
			);

			// Make sure it got the correct object
			$this->assertIsA(
				$this->Whistle->_objects['Override'],
				'TestListener'
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Override']->errors,
				array()
			);

			// WhistleComponent should gobble this up
			trigger_error('Testing error', E_USER_NOTICE);

			$this->assertIdentical(
				$this->Whistle->_objects['Override']->errors[0]['level'],
				E_USER_NOTICE
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Override']->errors[0]['file'],
				__FILE__
			);

			$this->assertIdentical(
				$this->Whistle->_objects['Override']->errors[0]['message'],
				'Testing error'
			);
		}

		/**
		 * Tests the isFatal method of the component.
		 *
		 * @return null
		 * @access public
		 */
		public function testIsFatal() {
			$this->assertTrue(
				$this->Whistle->_isFatal(E_ERROR)
			);
			$this->assertTrue(
				$this->Whistle->_isFatal(E_USER_ERROR)
			);
			$this->assertTrue(
				$this->Whistle->_isFatal(E_PARSE)
			);
			$this->assertFalse(
				$this->Whistle->_isFatal(-1)
			);

		}

	}

	// Ensure we have our WhistleComponent available
	App::import('Component', 'Referee.Whistle');

	/**
	 * WhistleComponentProxy
	 *
	 * Allows us to easily access protected methods and member variables
	 * in the WhistleComponent.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class WhistleComponentProxy extends WhistleComponent {

		/**
		 * Allows us access to protected member variables.
		 *
		 * @param string $variable
		 * @return mixed
		 * @access public
		 */
		public function __get($variable) {
			if (isset($this->$variable)) {
				return $this->$variable;
			}
		}

		/**
		 * Allows us access to protected member methods.
		 *
		 * @param string $method
		 * @param array $arguments
		 * @return mixed
		 * @access public
		 */
		public function __call($method, $arguments) {
			if (method_exists($this, $method)) {
				return call_user_func_array(
					array($this, $method),
					$arguments
				);
			}
		}

	}

	/**
	 * TestListener
	 *
	 * Small listener to attach to the WhistleComponent and confirm that
	 * it's operating correctly.
	 *
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class TestListener {

		public $errors = array();

		public $parameters;

		/**
		 * Method called by WhistleComponent. Saves the error to our
		 * member variable and records our current parameters.
		 *
		 * @param array $error
		 * @param array $parameters
		 * @return null
		 * @access public
		 */
		public function error($error, $parameters) {
			$this->errors[] = $error;
			$this->parameters = $parameters;
		}

		/**
		 * A custom method for handling errors, simply passes it off to
		 * the error() method.
		 *
		 * @param array $error
		 * @param array $parameters
		 * @return null
		 * @access public
		 */
		public function customError($error, $parameters) {
			$this->error($error, $parameters);
		}

	}

