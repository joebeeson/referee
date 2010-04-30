<?php

	// Required files for testing
	App::import('Component', 'Referee.Whistle');
	App::import('Core', 'Controller');

	/**
	 * WhistleComponentTest
	 * Tests the Whistle component for the Referee plugin.
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	final class WhistleComponentTest extends CakeTestCase {
		
		/**
		 * Performed piror to each test method is executed. Sets up our test
		 * environment for the following test.
		 * @return null
		 * @access public
		 */
		public function startTest() {
			$this->Whistle = new WhistleComponent();
			
			// When running via the TestShell, it handles our initialization
			if (php_sapi_name() != 'cli') {
				$this->Whistle->initialize();
			}
		}
		
		/**
		 * Performed after each test method is executed. Resets our environment
		 * for the next test.
		 * @return null
		 * @access public
		 */
		public function endTest() {
			unset($this->Whistle);
			ClassRegistry::flush();
		}
		
		/**
		 * Tests that the listeners and the Observable class is doing its job.
		 * @return null
		 * @access public
		 */
		public function testListeners() {
			
			// Ready our listener class...
			$Listener = new TestListenerClass();
			$this->Whistle->attach(E_NOTICE, array($Listener, 'error'));
			
			// Setup and fire off our error and a couple others...
			$uniqueErrorMessage = uniqid();
			@$this->Whistle->__error(E_NOTICE, $uniqueErrorMessage, __FILE__, __LINE__, array());
			@$this->Whistle->__error(E_USER_NOTICE, 'Not unique message', __FILE__, __LINE__, array());

			// We should only have one error caught
			$this->assertIdentical(
				count($Listener->errors),
				1
			);
			
			// We should have the correct error level...
			$this->assertIdentical(
				WhistleComponent::translateError($Listener->errors[0]['level']),
				'notice'
			);
			
			// The message should match the one we have
			$this->assertIdentical(
				$Listener->errors[0]['message'],
				$uniqueErrorMessage
			);
			
			// Now lets attach it for every single error and reset..
			$this->Whistle->attach('*', array($Listener, 'error'));
			$Listener->errors = array();
			
			// Regenerate our unique error message and start firing off errors
			$uniqueErrorMessage = uniqid();
			@$this->Whistle->__error(E_USER_NOTICE, $uniqueErrorMessage, __FILE__, __LINE__, array());
			
			// We should have the correct error level...
			$this->assertIdentical(
				WhistleComponent::translateError($Listener->errors[0]['level']),
				'user_notice'
			);
			
			// The message should match the one we have
			$this->assertIdentical(
				$Listener->errors[0]['message'],
				$uniqueErrorMessage
			);
			
			// We should only have one error caught
			$this->assertIdentical(
				count($Listener->errors),
				1
			);
			
		}
		
		/**
		 * Tests that the component is accurately storing errors away.
		 * @return null
		 * @access public
		 */
		public function testErrorStorage() {
			
			// We shouldn't have any errors yet...
			$this->assertIdentical(
				$this->Whistle->getErrors(),
				array()
			);
			
			/**
			 * We emulate the class catching an error. We use the error suppressor
			 * so that the test isn't littered with error messages since it would
			 * still pass them on. Also, we don't want to scare people.
			 */
			$uniqueErrorMessage = uniqid();
			@$this->Whistle->__error(E_NOTICE, $uniqueErrorMessage, __FILE__, __LINE__, array());
			$errors = $this->Whistle->getErrors();
			
			// Make sure that hte message matches the one we passed
			$this->assertIdentical(
				$errors[0]['message'], 
				$uniqueErrorMessage
			);
			
			// We sent an E_NOTICE, we should expect the level to be 'notice'
			$this->assertIdentical(
				$errors[0]['level'],
				'notice'
			);
		}
		
		/**
		 * Tests for the WhistleComponent::isFatal() method
		 * @return null
		 * @access public
		 */
		public function testIsFatal() {
			
			
			
			// E_ERROR is certainly a fatal error
			$this->assertTrue(WhistleComponent::isFatal(E_ERROR));
			
			// E_NOTICE isn't a fatal error
			$this->assertFalse(WhistleComponent::isFatal(E_NOTICE));
			
			// Sanity checks...
			$this->assertFalse(WhistleComponent::isFatal(new Exception));
			$this->assertFalse(WhistleComponent::isFatal());
			$this->assertFalse(WhistleComponent::isfatal(''));
			$this->assertFalse(WhistleComponent::isfatal(-1));
		}
		
		/**
		 * Tests for the WhistleComponent::translateError method
		 * @return null
		 * @access public
		 */
		public function testTranslateError() {
			$this->assertIdentical(
				$this->Whistle->translateError(E_ERROR),
				'error'
			);
			$this->assertIdentical(
				$this->Whistle->translateError(E_NOTICE),
				'notice'
			);
			$this->assertIdentical(
				$this->Whistle->translateError(E_WARNING),
				'warning'
			);
			
			// Sanity checks...
			$this->assertIdentical(
				$this->Whistle->translateError(false),
				null
			);
			$this->assertIdentical(
				$this->Whistle->translateError(-1),
				null
			);
			
		}
		
	}
	
	/**
	 * TestListenerClass
	 * Used to help us determine if the listener system is working correctly
	 * @author Joe Beeson <jbeeson@gmail.com>
	 */
	class TestListenerClass {
		
		/**
		 * Holds any errors we've caught
		 * @var array
		 * @access public
		 */
		public $errors = array();
		
		/**
		 * Method that is called when an error is passed along
		 * @param string $level
		 * @param string $message
		 * @param string $file
		 * $param integer $line
		 * @param array $context
		 * @return null
		 * @access public
		 */
		public function error($level, $message, $file, $line, $context) {
			$this->errors = am(
				$this->errors,
				array(compact('level', 'message', 'file', 'line'))
			);
		}
		
	}
