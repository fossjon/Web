<?php
	date_default_timezone_set("America/Toronto");
	session_start();
	
	$auth = 0;
	$name = "quickchatr.com";
	$hpag = array("", "", "", "", "");
	
	$webp = "/s-mail";
	$atch = "/opt/data";
	$mail = "/opt/mail";
	$perm = "/var/www/s-mail/sys/pperm.py";
	$post = "/var/www/s-mail/sys/pmail.py";
	
	$pkey = ""; $smails = "";
	$unchrs = "^%-+=?_."; $ujchrs = "";
	
	function saferstr($inputstr, $extrachr="")
	{
		$charlist = ("0123456789"."ABCDEFGHIJKLMNOPQRSTUVWXYZ"."abcdefghijklmnopqrstuvwxyz".$extrachr);
		$outpstri = "";
		$l = strlen($inputstr);
		for ($x = 0; $x < $l; $x += 1)
		{
			$c = substr($inputstr, $x, 1);
			$p = strpos($charlist, $c);
			if ($p === false) { continue; }
			$outpstri .= $c;
		}
		return $outpstri;
	}
	
	if (isset($_SESSION) && isset($_SESSION["auth"]))
	{
		$auth = 1;
	}
?>
