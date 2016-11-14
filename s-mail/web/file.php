<?php
	$root = ".";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$user = $_SESSION["auth"][0];
	
	$i = "";
	for ($x = 0; ($x + 1) < strlen($_GET["i"]); $x += 2)
	{
		$i .= chr(hexdec(substr($_GET["i"], $x, 2)));
	}
	
	$k = "";
	for ($x = 0; ($x + 1) < strlen($_GET["k"]); $x += 2)
	{
		$k .= chr(hexdec(substr($_GET["k"], $x, 2)));
	}
	
	$e = saferstr($_GET["e"], $unchrs);
	$f = saferstr($_GET["f"], $unchrs);
	
	$e = preg_replace("/\.trash$/i", "", $e);
	$e = preg_replace("/\.sent$/i", "", $e);
	$e = preg_replace("/\.read$/i", "", $e);
	
	$udir = ($mail."/".$user);
	
	$emails = scandir($udir);
	$afiles = array(); $attachs = array();
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		if (preg_match("/^.*temp$/i", $email)) { continue; }
		
		if (preg_match("/^".$e.".*\.attach\..*$/i", $email))
		{
			array_push($attachs, $email);
		}
		
		else if (preg_match("/^.*\.attach\..*$/i", $email)) { continue; }
		
		else if (preg_match("/^".$e.".*$/i", $email))
		{
			$data = lastline($udir."/".$email);
			$afiles = explode(" ", trim($data));
		}
	}
	
	foreach ($afiles as $afile)
	{
		$ainfo = explode(" ", base64_decode($afile));
		if (count($ainfo) > 1)
		{
			foreach ($attachs as $attach)
			{
				if (preg_match("/^.*".$ainfo[0]."$/i", $attach) && ($ainfo[0] == $f))
				{
					$data = ""; $name = ""; $buff = "";
					$fobj = fopen($udir."/".$attach, "r");
					while (!feof($fobj))
					{
						$data .= fread($fobj, 4096);
						if ($name == "")
						{
							$p = strpos($data, "\n");
							if ($p === false) { /* no-op */ }
							else
							{
								$name = substr($data, 0, $p);
								header("Content-Type: application/octet-stream");
								header("Content-Disposition: attachment; filename=".$name);
								$data = substr($data, $p + 1);
							}
						}
						if ($name != "")
						{
							$leng = strlen($data);
							for ($x = 0; ($x + 15) < $leng; $x += 16)
							{
								$block = substr($data, $x, 16);
								$ti = $block;
								$buff .= trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $k, $block, MCRYPT_MODE_CBC, $i));
								$blen = strlen($buff);
								for ($y = 0; ($y + 3) < $blen; $y += 4)
								{
									$block = substr($buff, $y, 4);
									print(base64_decode($block));
								}
								$buff = substr($buff, $y);
								$i = $ti;
							}
							$data = substr($data, $x);
							break;
						}
					}
					fclose($fobj);
					die;
				}
			}
		}
	}
?>
