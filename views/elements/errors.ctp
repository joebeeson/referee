<table cellspacing="0" id="errors">
	<?php
		$rows = array(array(
			'<strong>Severity</strong>',
			'<strong>Location</strong>',
			'<strong>Message</strong>',
			'<strong>Created</strong>',
		));
		foreach ($errors as $error) {
			$rows[] = array(
				Inflector::humanize($error['Error']['level']),
				$error['Error']['file'].':'.$error['Error']['line'],
				strip_tags($error['Error']['message']),
				$this->Time->timeAgoInWords($error['Error']['created'])
			);
		}
		echo $this->Html->tableCells($rows, 'style="background: #DDD;"', null, true);
	?>
</table>
