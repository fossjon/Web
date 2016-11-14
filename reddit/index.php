<?php include("auth.php"); ?>

<html>
	<head>
		<title><?php print($title); ?></title>
		
		<style>
			<?php include("style.php"); ?>
		</style>
		
		<?php include("script.php"); ?>
	</head>
	
	<body>
		<?php include("menu.php"); ?>
		<?php include("sort.php"); ?>
		
		Posts:
		<hr/>
		
		<?php
			$subs = array("default");
			formpost("postmade >= CURDATE()", array(), "votenum", "DESC", 0, 15, true);
		?>
	</body>
</html>
