<?php
	extract($error['Error']);
?>
<tr>
	<td class="<?php e($level) ?> level" width="120" valign="top">
		<?php e(Inflector::humanize($level)); ?>
	</td>
	<td class="file" valign="top">
		<?php e(str_replace(realpath(APP.'../'), '', $file)); ?>:<?php e($line); ?>
	</td>
	<td class="message" width="300" valign="top">
		<?php e(strip_tags($message)); ?>
	</td>
	<td class="created" valign="top">
		<?php e($this->Time->timeAgoInWords($created)); ?>
	</td>
</tr>
