<?php
	function nums($inpt)
	{
		$l = strlen($inpt);
		$n = "0123456789";
		$o = "";
		for ($x = 0; $x < $l; $x += 1)
		{
			$c = substr($inpt, $x, 1);
			if (strpos($n, $c) === false) { /* no-op */ }
			else { $o = ($o.$c); }
		}
		return $o;
	}
	
	if ($_POST["mode"] == "auth")
	{
		$f = @fopen("/opt/pkey.txt", "r");
		
		if ($f) { /* no-op */ }
		else { die; }
		
		$e = fgets($f, 4096);
		$d = fgets($f, 4096);
		$m = fgets($f, 4096);
		
		fclose($f);
		
		$data = explode("\n", $_POST["data"]);
		$emsg = base64_decode(trim($data[0]));
		$ekey = explode(" ", trim($data[1]));
		
		$sign = substr($emsg, 0, 32);
		$init = substr($emsg, 32, 16);
		$data = substr($emsg, 48);
		
		if (count($ekey) > 1)
		{
			$e = $ekey[1];
			#$k = bcpowmod(nums($e), nums($d), nums($m)); - why is this sooo slow for...
			$k = trim(exec("python -c 'print(pow(".nums($e).",".nums($d).",".nums($m)."))'"));
			$skey = substr($k, 4, -4);
			$skey = hash("sha256", $skey, true);
			
			if (hash("sha256", $skey.$init.$data.$skey, true) == $sign)
			{
				$mesg = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $skey, $data, MCRYPT_MODE_CBC, $init);
				$mesg = explode("\0", $mesg);
				if (count($mesg) > 1)
				{
					print($mesg[0]." ".$mesg[1]."\n");
				}
			}
		}
	}
?>
