<table cellspacing="0" id="errors">
	<tr class="header">
		<td><?php echo $paginator->sort('Severity', 'severity'); ?></td>
		<td><?php echo $paginator->sort('Location', 'location'); ?></td>
		<td><?php echo $paginator->sort('URL', 'url'); ?></td>
		<td><?php echo $paginator->sort('Created', 'created'); ?></td>
	</tr>
	<?php
		// Loop over each error and display them out
		foreach ($errors as $key=>$error) {
			echo $this->element('error', compact('error', 'key'));
		}
	?>
</table>
