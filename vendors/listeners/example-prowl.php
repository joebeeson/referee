<?php

	/**
	 * An example on how to use the awesome iPhone notification service
	 * Prowl to alert you when an error occurs
	 * @see http://prowl.weks.net/
	 * @author Joe Beeson <joe@joebeeson.com>
	 */
	
	// Get our LogComponent and attach the prowlNotification() method for errors
	$logComponent = ClassRegistry::getObject('Referee.Log');
	
	/**
	 * Uncomment the following line to attach the listener
	 */
	//$logComponent->attach(E_ERROR, 'prowlNotification');
	
	// We'll need the Prowl library 
	App::import('Lib', 'Referee.prowl');
	
	/**
	 * Sends a Prowl notification when fired
	 * @param integer $level
	 * @param string $string
	 * @param string $file
	 * @param integer $line
	 * @param array $context
	 * @return null
	 * @access public
	 */
	function prowlNotification($level, $string, $file, $line, $context) {
		$prowlObject = new Prowl('your-api-key');
		$prowlObject->notify('Referee CakePHP', 'Fatal Error', "$file:$line\n$string");
	}
