<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth == 1) { unset($_SESSION["auth"]); }
	
	header("Location: ".$webp); die;
?>
