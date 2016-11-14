<?php
	$root = ".";
	include($root."/common.php");
	
	$hpag[0] = "opacity: 1.0 !important;";
	$user = "";
	$mesg = "";
	
	if (isset($_POST) && isset($_POST["user"]))
	{
		$user = saferstr($_POST["user"], $unchrs);
		
		$udir = ($mail."/".$user);
		$file = ($udir."/".$user.".auth");
		
		if (file_exists($file))
		{
			$data = file_get_contents($file);
			$list = explode("\n", $data);
			if (count($list) > 3)
			{
				if ($_POST["pass"] == $list[1])
				{
					$_SESSION["auth"] = $list;
					$auth = 1;
				}
			}
		}
		
		if ($auth == 0)
		{
			$mesg = ("<font class='txtred'>Incorrect login!</font>");
		}
	}
	
	if ($auth == 1)
	{
		header("Location: ".$webp."/mail"); die;
	}
?>

<html>
	<script>
		localStorage.setItem("encr", "*");
	</script>
	
	<?php include($root."/html/head.html"); ?>
	
	<body onload="jQuery('#user').focus();">
		<?php include($root."/html/menu.html"); ?>
		
		<center>
			<table style="width: 75%;">
				<tr><td style="width: 13%; white-space: nowrap;"> &nbsp; </td><td style="white-space: nowrap;">
					<form method="post" id="auth" class="form-inline" role="form">
						<div class="form-group col-sm-5" style="padding-left: 5px; padding-right: 5px;">
							<div class="input-group input-group-sm">
							<input type="text" name="user" id="user" value="<?php print($user); ?>" onKeyPress="return subform(event, 'a');" class="form-control" placeholder="Username" />
							<span class="input-group-addon">@<?php print($name); ?></span>
							</div>
						</div>
						<div class="form-group input-group-sm col-sm-4" style="padding-left: 5px; padding-right: 5px;">
							<input type="password" name="pass" id="pass" onKeyPress="return subform(event, 'a');" class="form-control" placeholder="Password" style="width: 100%;" />
						</div>
						<div class="form-group col-sm-1" style="padding-left: 5px; padding-right: 5px;">
							<button type="button" class="btn btn-sm btn-default" onclick="subauth();">Sign In</button>
						</div>
					</form>
				</td><td style="width: 0%; white-space: nowrap;"> &nbsp; </td></tr>
				
				<tr><td colspan="3"><center><span id="mesg"> &nbsp; <br /><?php print($mesg); ?><br /> &nbsp; </span></center></td></tr>
				
				<tr><td colspan="3">
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title">Home Page News! - Secure Email [S-Mail]</h3>
						</div>
						<div class="panel-body" style="color: #333;">
							 &nbsp; <br />
							This site aims to provide you with free, standard email service which is:
							 &nbsp; <br />
							 &nbsp; <br />
							<ul>
								<li>Still fairly easy to use!</li>
								<li>End-to-End secure!</li>
								<li>Compatible with anyone & everyone!</li>
								<li>Web/API based (Clients can be made also)</li>
								<li>and...</li>
								<li>Open source (trust but verify!)</li>
							</ul>
							
							 &nbsp; <br />
							 &nbsp; <br />
							
							What are the downsides to a system like this?
							 &nbsp; <br />
							 &nbsp; <br />
							<ul>
								<li>Higher data/processing overheads lead to slower speeds</li>
								<li>No server-side searching</li>
								<li>No crowd-sourced spam filtering</li>
								<li>JavaScript/Client-Script Heavy</li>
							</ul>
							
							 &nbsp; <br />
							 &nbsp; <br />
							
							Tip: Choose a good passphrase & verify public key IDs, your security depends on it!
						</div>
					</div>
				</td></tr>
				
				<tr><td colspan="3"> &nbsp; </td></tr>
				
				<tr><td colspan="3">
					<div class="panel panel-info">
						<div class="panel-heading">
							<h3 class="panel-title">Proof Of Work</h3>
						</div>
						<div class="panel-body" style="color: #333;">
							<a href="<?php print($webp); ?>/img/smail.svg"><img src="<?php print($webp); ?>/img/smail.svg" style="width: 100%; border: 1px solid black;" /></a>
						</div>
					</div>
				</td></tr>
			</table>
		</center>
		
		<?php include($root."/html/foot.html"); ?>
	</body>
</html>
