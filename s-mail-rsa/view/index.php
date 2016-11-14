<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = $_SESSION["auth"][0];
	
	$udir = ($mail."/".$user);
	
	$data = file_get_contents($udir."/".$user.".auth");
	$list = explode("\n", $data);
	if (count($list) > 3)
	{
		$pkey = base64_decode($list[3]);
		$pkey = str_replace("\r", "", $pkey);
		$pkey = str_replace("\n", "", $pkey);
	}
	
	$file = saferstr($_GET["e"], $unchrs);
	$file = preg_replace("/\.trash$/i", "", $file);
	$file = preg_replace("/\.sent$/i", "", $file);
	$file = preg_replace("/\.read$/i", "", $file);
	
	$emails = scandir($udir);
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		if (preg_match("/^".$file.".*$/i", $email))
		{
			if (isset($_GET["m"]))
			{
				if ($_GET["m"] == "read") { $repl = ".read"; }
				if ($_GET["m"] == "true") { $repl = ".read"; if (preg_match("/^.*\.read(|\..*)$/i", $email)) { $repl = ""; } }
				$fnew = preg_replace("/^(.*\.[0-9a-f]{8,})(|\.sent|\.trash)$/i", "$1".$repl."$2", preg_replace("/\.read/i", "", $email));
				if ($fnew != $email)
				{
					rename($udir."/".$email, $udir."/".$fnew);
					$email = $fnew;
				}
				if ($_GET["m"] == "true") { break; }
			}
			
			$data = file_get_contents($udir."/".$email);
			$list = explode("\n", $data);
			if (count($list) > 4)
			{
				$smails = ("['".$list[0]."', '".$list[1]."', '".$list[2]."', '".$list[3]."', '".$list[4]."', '']");
			}
			
			break;
		}
	}
?>

<html>
	<?php include($root."/html/head.html"); ?>
	
	<body onload="getkeyi(user, '#keyi'); rsapre(); rsadec('view');">
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<div class="divtable" style="width: 85% !important;"><table class="table table-condensed" style="color: #333;">
				<thead>
					<tr><th colspan="4" style="border: 0px;"><span style="float: left;"><a href="javascript:window.history.back();" class="txtgreen">Back</a></span><span style="float: right;"><a href="javascript:markread('<?php print($file); ?>');" class="txtgreen">Mark/Unmark</a></span></th></tr>
					<tr><th colspan="4" style="text-align: center; border: 0px;">Message &nbsp; &nbsp; <i>[ <?php print($user."@".$name); ?> ]</i></th></tr>
					<tr><th colspan="4" style="text-align: center; border: 0px;">
						<a href="<?php print($webp); ?>/make/?e=">New</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=' + jQuery('#from').text();">Reply</a>
						 &nbsp; 
						<a href="javascript:window.location.href = '<?php print($webp); ?>/make/?e=' + jQuery('#from').text().replace(user + '@' + dnsn, '') + ',' + jQuery('#dest').text().replace(user + '@' + dnsn, '');">Forward</a>
					</th></tr>
				</thead>
				<tbody id="mail">
					<tr><td colspan="4" style="border: 0px;"> &nbsp; </td></tr>
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>Date:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="date"> &nbsp; </span></td></tr>
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>From:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="from"> &nbsp; </span></td></tr>
					<tr><td style="width: 0px; white-space: nowrap; text-align: right; border: 0px;"><b><span>To:</span></b></td><td style="width: 100%; text-align: left; border: 0px;"><span id="dest"> &nbsp; </span></td></tr>
					<tr><td colspan="4" style="border: 0px;"> &nbsp; </td></tr>
					<tr><td colspan="4" style="border: 0px;"><b><span id="subj"> &nbsp; </span></b></td></tr>
					<tr><td colspan="4" style="border: 0px;"> &nbsp; </td></tr>
					<tr><td colspan="4" style="border: 0px;"><span id="mesg"> &nbsp; </span></td></tr>
				</tbody>
			</table></div>
		</center>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
