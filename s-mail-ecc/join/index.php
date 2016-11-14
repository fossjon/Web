<?php
	$root = "..";
	include($root."/common.php");
	
	require_once($root."/recaptchalib.php");
	
	$hpag[1] = "opacity: 1.0 !important;";
	$user = "";
	$mesg = "";
	
	if (isset($_POST) && isset($_POST["user"]))
	{
		$privatekey = "6LcC8_ASAAAAALOkj7ED4vEsl2_veX1ZntmuJ5W0";
		$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		if (!$resp->is_valid) { $mesg = ("<font class='txtred'>The captcha was wrong.</font>"); }
		
		$user = saferstr($_POST["user"], $ujchrs);
		
		$udir = ($mail."/".$user);
		
		if ($mesg == "")
		{
			if ($user != $_POST["user"])
			{
				$mesg = ("<font class='txtred'>The username contained an invalid character, please review it above!</font>");
			}
		}
		
		if ($mesg == "")
		{
			if ($user == "")
			{
				$mesg = ("<font class='txtred'>The username was empty!</font>");
			}
		}
		
		if ($mesg == "")
		{
			if (file_exists($udir))
			{
				$mesg = ("<font class='txtred'>Sorry, that username already exists...</font>");
			}
		}
		
		if ($mesg == "")
		{
			mkdir($udir);
			if (file_exists($udir))
			{
				system("/usr/bin/sudo ".$perm." www-data nogroup 6770 ".$udir);
				$data = ($user."\n".saferstr($_POST["idnkey"])."\n".base64_encode($_POST["pubkey"])."\n".base64_encode($_POST["prienc"])."\n");
				$file = ($udir."/".$user.".auth");
				file_put_contents($file, $data);
				$mesg = ("<font class='txtgreen'>Success, your account was created!</font> &nbsp; [ <a href='../'>Login</a> ]");
				system("/usr/bin/sudo ".$perm." www-data nogroup 6770 ".$udir);
			}
			else
			{
				$mesg = ("<font class='txtred'>Sorry, an error happened on our side...</font>");
			}
		}
	}
?>

<html>
	<?php include($root."/html/head.html"); ?>
	
	<body>
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<form method="post" id="join" class="form-horizontal" role="form">
			<table style="width: 512px;">
				<tr><td>
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title">Join For Free!</h3>
						</div>
						<div class="panel-body" style="color: #333;">
							<div class="form-group">
								<label for="inputuser" class="col-sm-2 control-label">Username</label>
								<div class="col-sm-8">
									<div class="input-group input-group-sm">
									<input type="text" name="user" id="user" class="form-control" placeholder="Username" value="<?php print($user); ?>" onKeyPress="return subform(event, 'j');" />
									<span class="input-group-addon">@<?php print($name); ?></span>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label for="inputpass" class="col-sm-2 control-label">Password</label>
								<div class="col-sm-8 input-group-sm">
									<input type="password" id="pass" class="form-control" placeholder="Password" onKeyPress="return subform(event, 'j');" />
								</div>
							</div>
							<div class="form-group">
								<label for="inputconf" class="col-sm-2 control-label" style="padding-left: 30px;">Confirm</label>
								<div class="col-sm-8 input-group-sm">
									<input type="password" id="pwdc" class="form-control" placeholder="Confirm" onKeyPress="return subform(event, 'j');" />
								</div>
								<button type="button" class="btn btn-sm btn-default" onclick="subjoin();" >Join!</button>
							</div>
							
							<center>
								<span id="mesg"> &nbsp; <?php print($mesg); ?> &nbsp; </span>
								
								 &nbsp; <br />
								 &nbsp; <br />
								
								<?php
									$publickey = "6LcC8_ASAAAAAGKJJGYvhh5lEOgZF2V0HZXLdxXY";
									//echo recaptcha_get_html($publickey);
								?>
								<script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k=<?php print($publickey); ?>"></script>
								
								<noscript>
									<iframe src="https://www.google.com/recaptcha/api/noscript?k=<?php print($publickey); ?>" height="300" width="500" frameborder="0"></iframe><br>
									<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
									<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
								</noscript>
								
								 &nbsp; <br />
								 &nbsp; <br />
								
								[You can ignore these fields below]
								 &nbsp; <br />
								 &nbsp; <br />
								<font class="txtblue">(Private)</font> &nbsp; (Public)</center>
								 &nbsp; <br />
								 &nbsp; <br />
							</center>
							
							<div class="form-group">
								<label for="inputprikey" class="col-sm-4 control-label"><font class="txtblue">Private-Key</font></label>
								<div class="col-sm-8 input-group-sm">
									<textarea class="form-control" id="prikey" wrap="off" style="height: 128px;" onclick="this.select();" readonly></textarea>
								</div>
							</div>
							<div class="form-group">
								<label for="inputpubkey" class="col-sm-4 control-label">Public-Key</label>
								<div class="col-sm-8 input-group-sm">
									<textarea class="form-control" name="pubkey" id="pubkey" wrap="off" style="height: 128px;" readonly></textarea>
								</div>
							</div>
							<div class="form-group">
								<label for="inputenck" class="col-sm-4 control-label"><font class="txtblue">Cipher-Key</font></label>
								<div class="col-sm-8 input-group-sm">
									<input type="text" id="enckey" class="form-control" onclick="this.select();" readonly />
								</div>
							</div>
							<div class="form-group">
								<label for="inputsign" class="col-sm-4 control-label"><font class="txtblue">Signing-Key</font></label>
								<div class="col-sm-8 input-group-sm">
									<input type="text" id="intkey" class="form-control" onclick="this.select();" readonly />
								</div>
							</div>
							<div class="form-group">
								<label for="inputauth" class="col-sm-4 control-label">Auth-Key</label>
								<div class="col-sm-8 input-group-sm">
									<input type="text" name="idnkey" id="idnkey" class="form-control" readonly />
								</div>
							</div>
							<div class="form-group">
								<label for="inputprienc" class="col-sm-4 control-label">Secured-Key</label>
								<div class="col-sm-8 input-group-sm">
									<textarea class="form-control" name="prienc" id="prienc" wrap="off" style="height: 128px;" readonly></textarea>
								</div>
							</div>
							
						</div>
					</div>
				</td></tr>
			</table>
			</form>
		</center>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
