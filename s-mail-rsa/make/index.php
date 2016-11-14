<?php
	$root = "..";
	include($root."/common.php");
	
	if ($auth != 1) { header("Location: ".$webp); die; }
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = $_SESSION["auth"][0];
	
	$dest = "";
	$eadr = saferstr($_GET["e"], $unchrs."@,;");
	
	if (isset($_POST) && isset($_POST["dest"]))
	{
		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) { /* no-op */ }
		else
		{
			if (socket_connect($sock, "127.0.0.1", 25) === false) { /* no-op */ }
			else
			{
				$ekey = saferstr($_POST["ekey"], $unchrs." /,;");
				$ekey = str_replace(";", ",", $ekey);
				$elst = explode(",", $ekey);
				
				$dadr = saferstr($_POST["dest"], $unchrs."@,;");
				$dadr = str_replace(";", ",", $dadr);
				$dlst = explode(",", $dadr);
				
				$subj = saferstr($_POST["subj"], " ~!@#%^&*-=+_<>[]{}();:,.?/`'\$\"\\");
				$auma = trim(file_get_contents($mail."/"."server.auth"));
				
				$data = "";
				$data .= ("EHLO goodsir\n");
				$data .= ("AUTH PLAIN ".$auma."\n");
				$data .= ("MAIL FROM: ".$user."@".$name."\n");
				
				foreach ($dlst as $item)
				{
					$data .= ("RCPT TO: ".$item."\n");
				}
				
				$data .= ("DATA\n");
				
				if ($_POST["type"] == "emsg")
				{
					foreach ($elst as $item)
					{
						$info = explode(" ", $item);
						if (count($info) > 2)
						{
							$data .= ("Zsmsg-".$info[0].": ".$item."\n");
						}
					}
				}
				
				$data .= ("From: ".$user."@".$name."\n");
				$data .= ("To: ".$dadr."\n");
				$data .= ("Subject: ".$subj."\n");
				$data .= ("\n");
				$data .= ($_POST["mesg"]."\n");
				$temp = ($data.".\n"."QUIT\n");
				
				socket_write($sock, $temp, strlen($temp));
				socket_close($sock);
				
				$filedesc = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => array("file", "/dev/null", "w"));
				$fileproc = proc_open($post." sent", $filedesc, $filepipe, "/tmp", array());
				if (is_resource($fileproc))
				{
					fwrite($filepipe[0], $data);
					fclose($filepipe[0]);
					fclose($filepipe[1]);
					proc_close($fileproc);
				}
				
				print("<script>window.history.go(-2);</script>"); die;
			}
		}
	}
?>

<html>
	<?php include($root."/html/head.html"); ?>
	
	<body onload="jQuery('#dest').focus(); getkeyi(user, '#keyi'); procdest({'keyCode':21});">
		<?php include($root."/html/menu.html"); ?>
		
		<table style="width: 100%;">
			<tr>
				<td style="vertical-align: top; padding-left: 5px; padding-right: 5px;"><span id="pkeys"><center> &nbsp; <br />Enter a "To" Address &nbsp; <span class="glyphicon glyphicon-arrow-right" style="top: 2px;"></span></center></span></td>
				
				<td style="width: 80%; padding-right: 5px;">
					<form id="send" method="post" class="form-horizontal" role="form">
					<input type="hidden" name="type" id="type" value="mail" />
					<input type="hidden" name="ekey" id="ekey" value="" />
						<table style="width: 100%;">
							<tr><td>
								<div class="panel panel-primary">
									<div class="panel-heading">
										<center><h3 class="panel-title"><b>
											Compose &nbsp; &nbsp; <i>[ <?php print($user."@".$name); ?> ]</i> &nbsp; &nbsp; <a href="javascript:window.history.back();" class="txtgreen">Back</a>
										</b></h3></center>
									</div>
									<div class="panel-body" style="color: #333;">
										<div class="form-group">
											<label for="inputdest" class="col-sm-2 control-label">To</label>
											<div class="col-sm-6 input-group-sm">
												<input type="text" name="dest" id="dest" class="form-control" placeholder="user@host.com" value="<?php print($eadr); ?>" onkeyup="procdest(event);" />
											</div>
											<label for="inputsend" class="col-sm-3 control-label"> &nbsp; </label>
											<button type="button" class="btn btn-sm btn-success" onfocus="jQuery('#subj').focus();" onclick="subsend();">Send</button>
										</div>
										<div class="form-group">
											<label for="inputhinta" class="col-sm-2 control-label">Helper &nbsp; <span class="glyphicon glyphicon-circle-arrow-right" style="top: 2px;"></span></label>
											<label for="inputhintb" class="col-sm-6 control-label" style="text-align: left;"><span id="hint"> &nbsp; </span></label>
										</div>
										<div class="form-group">
											<label for="inputsubj" class="col-sm-2 control-label">Subject</label>
											<div class="col-sm-10 input-group-sm">
												<input type="text" name="subj" id="subj" class="form-control" placeholder="Subject" onfocus="jQuery('#hint').text('');" />
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-12 input-group-sm">
												<textarea name="mesg" id="mesg" class="form-control" placeholder="Message" style="height: 512px;" onfocus="jQuery('#hint').text('');"></textarea>
											</div>
										</div>
									</div>
								</div>
							</td></tr>
						</table>
					</form>
				</td>
			</tr>
		</table>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
