<?php include("auth.php"); ?>

<?php
	$_SESSION["auth"] = 0;
	$_SESSION["user"] = array();
	header("Location: /");
	die;
?>
