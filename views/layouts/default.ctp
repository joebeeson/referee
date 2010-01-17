<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>CakePHP Referee Plugin</title>
		<style type="text/css">
			body {
				font-family: verdana,arial,helvetica,sans-serif;
				font-size: 12px;
			}
			h1 {
				padding-bottom: 0px;
			}
			#errors {
				width: 100%;
			}
			td {
				vertical-align: top;
				padding: 4px;
			}
			
			td.level {
				padding-left: 20px;
				font-weight: bold;
			}
			
			/* Severity coloring, backgrounds */
			td.user_warning, td.warning {
				color: #C98300;
				background: url('http://i.imgur.com/WrI0K.gif') no-repeat;
				background-position: 1px 4px;
			}
			td.notice {
				color: #C9B300;
				background: url('http://imgur.com/iqcJv.gif') no-repeat;
				background-position: 1px 4px;
			}
			td.error, td.parse {
				color: #C90000;
				background: url('http://imgur.com/0WLYS.gif') no-repeat;
				background-position: 1px 4px;
			}
			
			/* Row, cell coloring and font weights */
			td.message {
				width: 45%;
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
