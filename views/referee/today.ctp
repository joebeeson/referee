<div style="width: 100%;">
	<?php echo $this->element('errors', compact('errors')); ?>
</div>

<?php
	echo $this->Paginator->numbers();
	echo $this->Paginator->prev('Previous', null, null, array('class' => 'disabled'));
	echo $this->Paginator->next('Next', null, null, array('class' => 'disabled'));
?> 
