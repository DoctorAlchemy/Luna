<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<link rel="stylesheet" type="text/css" href="themes/Luna/style.css" />
		<link type="text/css" rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo $lang['Maintenance'] ?></title>
	</head>
	<body>
		<div class="container">
			<div class="panel panel-danger">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo $lang['Maintenance'] ?></h3>
				</div>
				<div class="panel-body">
					<?php echo $message ?>
				</div>
			</div>
		</div>
	</body>
</html>