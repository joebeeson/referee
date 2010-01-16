<div style="width: 100%;">
	<?php echo $this->element('errors', compact('errors')); ?>
</div>

<!-- Pagination //-->
<div class="pagination">
	<?php
		echo $this->Paginator->prev('Previous');
		echo '&nbsp;';
		echo $this->Paginator->numbers();
		echo '&nbsp;';
		echo $this->Paginator->next('Next');
	?>
</div>
