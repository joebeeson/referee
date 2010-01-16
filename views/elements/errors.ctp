<table cellspacing="0">
	<tr style="font-weight: bold;">
		<td>Severity</td>
		<td>Location</td>
		<td>Message</td>
		<td>Created</td>
	</tr>
	<?php
		// Loop over each error and display them out
		foreach ($errors as $key=>$error) {
			echo $this->element('error', compact('error', 'key'));
		}
	?>
</table>
