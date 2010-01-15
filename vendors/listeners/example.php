<?php
	// Retrieve an instance of the LogComponent from ClassRegistry
	$logComponent = ClassRegistry::getObject('Journal.Log');
	
	/**
	 * ::attach() can accept any valid callback. This means you could
	 * attach a class method, static or otherwise as well.
	 * @see http://php.net/manual/en/language.pseudo-types.php
	 * 
	 * Here we attach just a simple little function and wait for any
	 * E_NOTICE events to be fire from the component.
	 */
	$logComponent->attach(E_NOTICE, 'exampleListener');
	
	/**
	 * This is our example listener that we attached above. All listeners
	 * will be passed the following arguments to use how they see fit.
	 */
	function exampleListener($string, $file, $line, $context) {
		echo "exampleListener() was notified of an E_NOTICE in $file";
		die;
	}

	/**
	 * Remove the comment from the following line to throw an E_NOTICE 
	 */
	//echo $undeclared_variable_to_cause_an_ENOTICE;
