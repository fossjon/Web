<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = $_SESSION["auth"][0];
	
	$udir = ($mail."/".$user);
	$emails = scandir($udir);
	rsort($emails);
	
	$npage = 1;
	if (isset($_GET["p"])) { $npage = max(1, intval($_GET["p"])); }
	
	$mpage = "";
	if (isset($_GET["b"])) { $mpage = saferstr($_GET["b"], ""); }
	
	$i = 0; $l = 10; $n = 0;
	$a = (($npage - 1) * $l); $b = ($a + $l);
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		
		if ($mpage == "all") { /* no-op */ }
		else if ($mpage == "outbox") { if (!preg_match("/^.*\.sent$/i", $email)) { continue; } }
		else if ($mpage == "trash") { if (!preg_match("/^.*\.trash$/i", $email)) { continue; } }
		else if (!preg_match("/^.*\.[0-9a-f]{8,}(|\.read)$/i", $email)) { continue; }
		
		if (!preg_match("/^.*\.read(|\..*)$/i", $email)) { $n += 1; }
		if (($a <= $i) && ($i < $b))
		{
			$data = file_get_contents($udir."/".$email);
			$list = explode("\n", $data);
			if (count($list) > 4)
			{
				if ($smails != "") { $smails .= ",\n"; }
				$smails .= "['".$list[0]."', '".$list[1]."', '".$list[2]."', '', '".$list[4]."', '".$email."']";
			}
		}
		$i += 1;
	}
	
	if (isset($_GET["u"])) { print($n." ".$i); die; }
	
	$data = file_get_contents($udir."/".$user.".auth");
	$list = explode("\n", $data);
	if (count($list) > 3)
	{
		$pkey = base64_decode($list[3]);
		$pkey = str_replace("\r", "", $pkey);
		$pkey = str_replace("\n", "", $pkey);
	}
	
	$mode = "i"; $head = "From";
	if ($mpage == "all") { $mode = "a"; }
	if ($mpage == "outbox") { $mode = "o"; $head = "To"; }
	if ($mpage == "trash") { $mode = "d"; }
	
	$pnums = (intval($i / $l) + 1);
	$apage = (" <a href='".$webp."/mail/?b=".$mpage."&p=1'><span class='glyphicon glyphicon-chevron-left' style='top: 2px;'></span></a> ");
	$bpage = (" <a href='".$webp."/mail/?b=".$mpage."&p=".$pnums."'><span class='glyphicon glyphicon-chevron-right' style='top: 2px;'></span></a> ");
	if ($npage > 1) { $apage = ($apage." <a href='".$webp."/mail/?b=".$mpage."&p=".($npage - 1)."'><span class='glyphicon glyphicon-circle-arrow-left' style='top: 2px;'></span></a> "); }
	if ($npage < $pnums) { $bpage = (" <a href='".$webp."/mail/?b=".$mpage."&p=".($npage + 1)."'><span class='glyphicon glyphicon-circle-arrow-right' style='top: 2px;'></span></a> ".$bpage); }
?>

<html>
	<script>
		var view = "<?php print($mode); ?>";
		var mpage = "<?php print($mpage); ?>";
		var npage = "<?php print($npage); ?>";
	</script>
	
	<?php include($root."/html/head.html"); ?>
	
	<body onload="getkeyi(user, '#keyi'); rsapre(); setTimeout(function() { rsadec('list'); }, 0); mailloop();">
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<div class="divtable" style="width: 85% !important;"><table class="table table-condensed table-striped" style="color: #333;">
				<thead>
					<tr><th colspan="4" style="border: 0px;"><span style="float: left;"><?php print($apage.$npage." / ".$pnums.$bpage); ?></span><span style="float: right;"><a href="<?php print($webp); ?>/make/?e=" class="txtgreen">Compose</a></span></th></tr>
					<tr><th colspan="4" style="text-align: center; border: 0px;">Mail &nbsp; &nbsp; <i>[ <?php print($user."@".$name); ?> ]</i> &nbsp; &nbsp; <span class="label label-danger" style="padding-top: 4px; padding-bottom: 3px;" id="numb">0</span></th></tr>
					<tr><th colspan="4" style="text-align: center; border: 0px;">
						<?php if ($mode != "a") { print("<a href='".$webp."/mail/?b=all&p=".$npage."'>All</a>"); } else { print("All"); } ?>
						 &nbsp; 
						<?php if ($mode != "i") { print("<a href='".$webp."/mail/?b=inbox&p=".$npage."'>Inbox</a>"); } else { print("Inbox"); } ?>
						 &nbsp; 
						<?php if ($mode != "o") { print("<a href='".$webp."/mail/?b=outbox&p=".$npage."'>Outbox</a>"); } else { print("Outbox"); } ?>
						 &nbsp; 
						<?php if ($mode != "d") { print("<a href='".$webp."/mail/?b=trash&p=".$npage."'>Deleted</a>"); } else { print("Deleted"); } ?>
						</div>
					</th></tr>
					<tr><th colspan="4" style="border: 0px;"> &nbsp; </th></tr>
					<tr>
						<th class="colmin" style="border-top: 0px;"><center><input type="checkbox" style="margin-top: 0px; margin-bottom: 4px;" /></center></th>
						<th class="colmin" style="border-top: 0px;"><center>Date</center></th>
						<th class="colmin" style="border-top: 0px;"><?php print($head); ?></th>
						<th style="width: 99%; white-space: nowrap; border-top: 0px;">Subject</th>
					</tr>
				</thead>
				<tbody id="mail"></tbody>
			</table></div>
		</center>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
