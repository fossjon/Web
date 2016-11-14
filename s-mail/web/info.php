<?php
	$root = ".";
	include($root."/common.php");
	
	if (isset($_GET) && isset($_GET["mode"]))
	{
		if (($_GET["mode"] == "pub") || ($_GET["mode"] == "sum"))
		{
			$user = saferstr($_GET["user"], $unchrs);
			
			$data = "";
			if (file_exists($mail."/".$user."/".$user.".auth"))
			{
				$data = file_get_contents($mail."/".$user."/".$user.".auth");
			}
			$list = explode("\n", $data);
			
			$pkey = "";
			if (count($list) > 3)
			{
				$pkey = base64_decode($list[2]);
			}
			$pkey = str_replace("\r", "", $pkey);
			
			if ($_GET["mode"] == "pub")
			{
				print($pkey); die;
			}
			
			if ($pkey == "") { print($pkey); die; }
			
			if ($_GET["mode"] == "sum")
			{
				$hash = hash("sha256", $pkey, true); $hlen = strlen($hash);
				$adjv = file_get_contents("./words/adjv.txt");
				$adjv = explode("\n", $adjv); $alen = count($adjv);
				$noun = file_get_contents("./words/noun.txt");
				$noun = explode("\n", $noun); $nlen = count($noun);
				$verb = file_get_contents("./words/verb.txt");
				$verb = explode("\n", $verb); $vlen = count($verb);
				$left = -1;
				$indx = 0; $list = array(0, 0, 0, 0); $outp = "";
				for ($x = 0; $x < $hlen; $x += 1)
				{
					if ($left < 0)
					{
						$numb = -1;
						if (($x + 1) < $hlen)
						{
							$pres = ord(substr($hash, $x, 1));
							$next = ord(substr($hash, $x + 1, 1));
							$numb = (($pres << 4) + ($next >> 4));
							$left = ($next & 0xf);
							$x += 1;
						}
					}
					else
					{
						$pres = ord(substr($hash, $x, 1));
						$numb = (($left << 8) + $pres);
						$left = -1;
					}
					$list[$indx] = $numb; $indx = (($indx + 1) % 4);
					if ($indx == 0)
					{
						while ($adjv[$list[0] % $alen] == "") { $list[0] += 1; }
						while ($noun[$list[1] % $nlen] == "") { $list[1] += 1; }
						while ($verb[$list[2] % $vlen] == "") { $list[2] += 1; }
						while ($adjv[$list[3] % $alen] == "") { $list[3] += 1; }
						$outp .= ($adjv[$list[0] % $alen]." ".$noun[$list[1] % $nlen]." ".$verb[$list[2] % $vlen]." ".$adjv[$list[3] % $alen]."\n");
					}
				}
				print(trim($outp)."\n\n".$pkey); die;
			}
		}
	}
?>
