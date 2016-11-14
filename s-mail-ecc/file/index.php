<?php
	$root = "..";
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
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		if (preg_match("/^".$e.".*$/i", $email))
		{
			$data = file_get_contents($udir."/".$email);
			$list = explode("\n", $data);
			if (count($list) > 5)
			{
				$files = explode(" ", trim($list[5]));
				foreach ($files as $file)
				{
					if (file_exists($atch."/".$file) && ($file == $f))
					{
						$data = ""; $name = ""; $buff = "";
						$fobj = fopen($atch."/".$file, "r");
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
			break;
		}
	}
?>
