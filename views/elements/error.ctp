<?php
	// Extract our values out for easier usage
	extract($error['Error']);
?>
<tr class="<?php e($level) ?> <?php e($key % 2 ? 'odd' : ''); ?>">
	<td class="<?php e($level) ?> level" width="120" >
		<?php e(Inflector::humanize(str_replace('user_warning', 'Warning', $level))); ?>
	</td>
	<td class="file">
		<?php e(str_replace(realpath(APP.'../'), '', $file)); ?>:<?php e($line); ?>
	</td>
	<td class="url" width="300">
		<?php e($url); ?>
	</td>
	<td class="created">
		<?php e($this->Time->timeAgoInWords($created, array('end' => '+1 day'))); ?>
	</td>
</tr>
<tr class="<?php e($key % 2 ? 'odd' : ''); ?>">
	<td colspan="4" style="font-style: italic; color: #666; padding-top: 1px;">
		<?php echo strip_tags($message); ?>
	</td>
</tr>
