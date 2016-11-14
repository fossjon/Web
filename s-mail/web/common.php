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
	
	function lastline($filename)
	{
		$pntr = 0; $flag = 0;
		$line = "";
		$fobj = fopen($filename, "r");
		
		while (1)
		{
			$pntr -= 1; fseek($fobj, $pntr, SEEK_END);
			$char = fgetc($fobj);
			if ($char === false) { break; }
			if (ord($char) > 30) { $flag = 1; }
			else if ($flag != 0) { break; }
			if ($flag == 1) { $line = ($char.$line); }
		}
		
		fclose($fobj);
		return $line;
	}
	
	function getemail($file, $udir)
	{
		global $unchrs;
		
		$found = ""; $smails = "";
		
		$file = saferstr($file, $unchrs);
		$file = preg_replace("/\.trash$/i", "", $file);
		$file = preg_replace("/\.sent$/i", "", $file);
		$file = preg_replace("/\.read$/i", "", $file);
		
		$emails = scandir($udir);
		$afiles = array(); $attachs = array();
		foreach ($emails as $email)
		{
			if (($email == ".") || ($email == "..")) { continue; }
			if (preg_match("/^.*auth$/i", $email)) { continue; }
			if (preg_match("/^.*temp$/i", $email)) { continue; }
			
			if (preg_match("/^".$file.".*\.attach\..*$/i", $email))
			{
				array_push($attachs, $email);
			}
			
			else if (preg_match("/^.*\.attach\..*$/i", $email)) { continue; }
			
			else if (preg_match("/^".$file.".*$/i", $email))
			{
				if ($smails == "")
				{
					$data = file_get_contents($udir."/".$email);
					$list = explode("\n", $data);
					if (count($list) > 5)
					{
						$found = $email;
						$smails = ("['".$list[0]."', '".$list[1]."', '".$list[2]."', '".$list[3]."', '".trim($list[4])."', '".$email."', '".trim($list[5])."']");
						$afiles = explode(" ", trim($list[5]));
					}
				}
			}
		}
		
		$fnames = "file";
		foreach ($afiles as $afile)
		{
			$ainfo = explode(" ", base64_decode($afile));
			if (count($ainfo) > 1)
			{
				foreach ($attachs as $attach)
				{
					if (preg_match("/^.*".$ainfo[0]."$/i", $attach))
					{
						$f = fopen($udir."/".$attach, "r");
						$fnames .= (" ".base64_encode(trim(fgets($f))));
						fclose($f);
					}
				}
			}
		}
		
		return array($file, $found, $smails, $fnames);
	}
	
	if (isset($_SESSION) && isset($_SESSION["auth"]))
	{
		$auth = 1;
	}
?>
