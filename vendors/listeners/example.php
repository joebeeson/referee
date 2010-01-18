<?php

	/**
	 * Retrieve a copy of the LogComponent instance so that we can attach
	 * our listener callback to it.
	 */
	$logComponent = ClassRegistry::getObject('Referee.Log');
	
	/**
	 * The attach() method accepts two parameters. The event type to attach
	 * to and the callback to fire when the event occurs.
	 * 
	 * Here we attach the exampleListener() function and listen for an
	 * E_NOTICE event to be dispatched.
	 */
	$logComponent->attach(E_NOTICE, 'exampleListener');
	
	/**
	 * This is our example listener that we attached above. It will be
	 * called when an E_NOTICE occurs.
	 */
	function exampleListener($string, $file, $line, $context) {
		?>
			<div style="-moz-border-radius: 5px; background: #D1BE2E; padding: 5px;">
				<strong>RefereePlugin: exampleListener() caught an E_NOTICE</strong><br/>
				<em><?php echo $string; ?></em><br/>
				<code><?php echo $file.':'.$line; ?></code>
			</div>
		<?php
	}
