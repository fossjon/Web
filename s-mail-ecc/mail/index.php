<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = $_SESSION["auth"][0];
	
	$udir = ($mail."/".$user);
	$emails = scandir($udir);
	
	if (isset($_GET["empty"]) && ($_GET["empty"] == "true"))
	{
		foreach ($emails as $email)
		{
			if (preg_match("/^.*\.trash$/i", $email))
			{
				$pntr = 0; $flag = 0;
				$afile = "";
				$fobj = fopen($udir."/".$email, "r");
				
				while (1)
				{
					$pntr -= 1; fseek($fobj, $pntr, SEEK_END);
					$char = fgetc($fobj);
					if ($char === false) { break; }
					if (ord($char) > 30) { $flag = 1; }
					else if ($flag != 0) { break; }
					if ($flag == 1) { $afile = ($char.$afile); }
				}
				
				$afiles = explode(" ", $afile);
				foreach ($afiles as $afilei)
				{
					if (trim($afilei) == "") { continue; }
					if (file_exists($atch."/".$afilei))
					{
						unlink($atch."/".$afilei);
					}
				}
				
				unlink($udir."/".$email);
			}
		}
	}
	
	$npage = 1;
	if (isset($_GET["p"])) { $npage = max(1, intval($_GET["p"])); }
	
	$mpage = "";
	if (isset($_GET["b"])) { $mpage = saferstr($_GET["b"], ""); }
	
	$spage = ""; $dchg = "";
	if (isset($_GET["s"])) { $spage = saferstr($_GET["s"], "-"); }
	if (($spage == "") || ($spage == "-d")) { rsort($emails); $dchg = "d"; }
	else if ($spage == "d") { sort($emails); $dchg = "-d"; }
	
	$i = 0; $l = 10; $n = 0;
	$a = (($npage - 1) * $l); $b = ($a + $l);
	$read = array();
	foreach ($emails as $email)
	{
		if (($email == ".") || ($email == "..")) { continue; }
		if (preg_match("/^.*auth$/i", $email)) { continue; }
		
		if ($mpage == "all") { /* no-op */ }
		else if ($mpage == "outbox") { if (!preg_match("/^.*\.sent$/i", $email)) { continue; } }
		else if ($mpage == "trash") { if (!preg_match("/^.*\.trash$/i", $email)) { continue; } }
		else if (!preg_match("/^.*\.[0-9a-f]{8,}(|\.read)$/i", $email)) { continue; }
		
		$fnew = 0;
		if (!preg_match("/^.*\.read(|\..*)$/i", $email)) { $n += 1; $fnew = 1; }
		if (isset($_GET["u"])) { $i += 1; continue; }
		
		if ($spage == "n")
		{
			if ($fnew == 0) { array_push($read, $email); continue; }
		}
		
		if (($a <= $i) && ($i < $b))
		{
			$data = file_get_contents($udir."/".$email);
			$list = explode("\n", $data);
			if (count($list) > 5)
			{
				if ($smails != "") { $smails .= ",\n"; }
				$smails .= "['".$list[0]."', '".$list[1]."', '".$list[2]."', '', '".trim($list[4])."', '".$email."', '".trim($list[5])."']";
			}
		}
		$i += 1;
	}
	
	if ($spage == "n")
	{
		$x = 0; $m = count($read);
		while (($i < $b) && ($x < $m))
		{
			$data = file_get_contents($udir."/".$read[$x]);
			$list = explode("\n", $data);
			if (count($list) > 5)
			{
				if ($smails != "") { $smails .= ",\n"; }
				$smails .= "['".$list[0]."', '".$list[1]."', '".$list[2]."', '', '".trim($list[4])."', '".$read[$x]."', '".trim($list[5])."']";
			}
			$i += 1; $x += 1;
		}
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
	
	$surls = ($webp."/mail/?b=".$mpage."&p=".$npage."&s=".$spage);
	
	$mode = "i"; $head = "From";
	if ($mpage == "all") { $mode = "a"; }
	if ($mpage == "outbox") { $mode = "o"; $head = "To"; }
	if ($mpage == "trash") { $mode = "d"; }
	
	$pnums = (intval($i / $l) + 1);
	$apage = (" <a href='".str_replace("&p=", "&q=", $surls)."&p=1'><span class='glyphicon glyphicon-chevron-left txtgreen' style='top: 2px;'></span></a> ");
	$bpage = (" <a href='".str_replace("&p=", "&q=", $surls)."&p=".$pnums."'><span class='glyphicon glyphicon-chevron-right txtgreen' style='top: 2px;'></span></a> ");
	if ($npage > 1) { $apage = ($apage." <a href='".str_replace("&p=", "&q=", $surls)."&p=".($npage - 1)."'><span class='glyphicon glyphicon-circle-arrow-left txtgreen' style='top: 2px;'></span></a> "); }
	if ($npage < $pnums) { $bpage = (" <a href='".str_replace("&p=", "&q=", $surls)."&p=".($npage + 1)."'><span class='glyphicon glyphicon-circle-arrow-right txtgreen' style='top: 2px;'></span></a> ".$bpage); }
?>

<html>
	<script>
		var view = "<?php print($mode); ?>";
		var surls = "<?php print($surls); ?>";
		
		function rm()
		{
			if (confirm("Are you sure you'd like to empty the deleted items?"))
			{
				location.href = (surls + "&empty=true");
			}
		}
	</script>
	
	<?php include($root."/html/head.html"); ?>
	
	<body onload="getkeyi(user, '#keyi'); keypre(); setTimeout(function() { msgdec('list'); }, 0); mailloop();">
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<div class="divtable" style="width: 85% !important;"><table class="table table-condensed table-striped table-hover" style="color: #333;">
				<thead>
					<tr><th colspan="7" style="border: 0px;">
						<table style="width: 100%; color: #333;"><tr>
							<th style="text-align: left; width: 30%;"><?php print($apage.$npage." / ".$pnums.$bpage); ?></th>
							<th style="text-align: center;">Mail for &nbsp; <i>[ <?php print($user."@".$name); ?> ]</i> &nbsp; &nbsp; <span class="label label-success" style="padding-top: 4px; padding-bottom: 3px;" id="numb">0</span></th>
							<th style="text-align: right; width: 30%;">
								<button type="button" class="btn btn-sm btn-success" style="padding-top: 2px; padding-bottom: 1px;" onclick="location.href = '<?php print($webp); ?>/make/';"><span class="glyphicon glyphicon-plus-sign" style="top: 2px;"></span> &nbsp; New</button>
								 &nbsp; 
								<select id="mailmark">
									<option name="m">Mark Read / Unread</option>
									<option name="d">Delete / Restore</option>
								</select>
								 &nbsp; 
								<button type="button" class="btn btn-sm btn-primary" style="padding-top: 2px; padding-bottom: 1px;" onclick="jQuery('input:checkbox[name=chkmail]:checked').each(function() { markmail(jQuery(this).attr('id'), jQuery('#mailmark').find(':selected').attr('name')); }); window.location.href = surls;">Go</button>
							</th>
						</tr></table>
					</th></tr>
					<tr><th colspan="7" style="text-align: center; border: 0px;">
						<?php if ($mode != "a") { print("<a href='".$webp."/mail/?b=all'>All</a>"); } else { print("All"); } ?>
						 &nbsp; 
						<?php if ($mode != "i") { print("<a href='".$webp."/mail/?b=inbox'>Inbox</a>"); } else { print("Inbox"); } ?>
						 &nbsp; 
						<?php if ($mode != "o") { print("<a href='".$webp."/mail/?b=outbox'>Outbox</a>"); } else { print("Outbox"); } ?>
						 &nbsp; 
						<?php if ($mode != "d") { print("<a href='".$webp."/mail/?b=trash'>Deleted</a>"); } else { print("Deleted &nbsp; [ <a href='javascript:rm();'>Empty</a> ]"); } ?>
						</div>
					</th></tr>
					<tr><th colspan="7" style="border: 0px;"> &nbsp; </th></tr>
					<tr>
						<th class="colmin" style="border-top: 0px; padding-bottom: 6px;"><input type="checkbox" style="margin-top: 0px; margin-bottom: 4px;" onclick="jQuery('*').find(':checkbox').prop('checked', jQuery(this).prop('checked'));" /></th>
						<th class="colmin" style="border-top: 0px; padding-bottom: 10px;"><a href="<?php print(str_replace('&s=', '&t=', $surls).'&s=n'); ?>"><span class="glyphicon glyphicon-sort"></span></a></th>
						<th class="colmin" style="border-top: 0px; padding-bottom: 10px;"><span class="glyphicon glyphicon-file" style="color: #555555;"></span></th>
						<th class="colmin" style="border-top: 0px; padding-bottom: 10px;"><img src="<?php print($webp); ?>/img/lock.png" /></th>
						<th class="colmin" style="border-top: 0px;">Date <a href="<?php print(str_replace('&s=', '&t=', $surls).'&s='.$dchg); ?>"><span class="glyphicon glyphicon-sort"></span></a></th>
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
