<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>CakePHP Referee Plugin</title>
		<?php
			echo $this->Html->css(array(
				'/referee/css/main.css',
				'/referee/css/table.css',
				'/referee/css/layout.css'
			));
		?>
	</head>
	<body>
	
		<br/>
		<div id="container">
			<?php 
				e($content_for_layout); 
			?>
		</div>
		<br/>
		
	</body>
</html>
