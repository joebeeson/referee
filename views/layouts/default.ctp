<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>CakePHP Referee Plugin</title>
		<style type="text/css">
			body {
				font-family: verdana,arial,helvetica,sans-serif;
			}
			h1 {
				padding-bottom: 0px;
			}
			#errors {
				width: 100%;
			}
			td {
				vertical-align: top;
				padding: 2px;
			}
			
			/* Severity coloring */
			td.user_warning, td.warning {
				color: red;
			}
			td.notice {
				color: orange;
			}
			
			/* Row, cell coloring and font weights */
			td.message {
				width: 40%;
			}
			tr.odd {
				background: #DDD;
			}
		</style>
	</head>
	<body>
		<?php e($content_for_layout); ?>
	</body>
</html>
