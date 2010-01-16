<?php
	// Extract our values out for easier usage
	extract($error['Error']);
?>
<tr class="<?php e($key % 2 ? 'odd' : ''); ?>">
	<td class="<?php e($level) ?> level" width="120" >
		<?php e(Inflector::humanize($level)); ?>
	</td>
	<td class="file" >
		<?php e(str_replace(realpath(APP.'../'), '', $file)); ?>:<?php e($line); ?>
	</td>
	<td class="message" width="300">
		<?php e(strip_tags($message)); ?>
	</td>
	<td class="created">
		<?php e($this->Time->timeAgoInWords($created)); ?>
	</td>
</tr>
