<?php include("auth.php"); ?>

<?php
	$ulen = strlen($post['user']);
	$plen = strlen($post['pass']);
	$elen = strlen($post['mail']);
	
	if ($post['user'] != "")
	{
		if (!preg_match("/^[0-9A-Za-z]+$/i", $post['user']))
		{
			$mesg = "Username should be at least 1 number or letter!";
		}
		
		else if (($ulen < 1) or (20 < $ulen))
		{
			$mesg = "Username should be between 1 and 20 characters!";
		}
		
		else if (($plen < 8) or (512 < $plen))
		{
			$mesg = "Password should be between 8 and 512 characters!";
		}
		
		else if ($post['pass'] != $post['cpwd'])
		{
			$mesg = "Confirmation password did not match!";
		}
		
		else if (!preg_match("/^[0-9A-Za-z._-]+@[0-9A-Za-z]+\.[A-Za-z]+$/i", $post['mail']))
		{
			$mesg = "Email should be in the format [user@host.com]!";
		}
		
		else if (($elen < 1) or (64 < $elen))
		{
			$mesg = "Email should be between 1 and 64 characters!";
		}
		
		else if (count(getusr($post['user'])) > 0)
		{
			$mesg = "User already taken!";
		}
		
		else if (makeuser($post) != 1)
		{
			$mesg = "Error while creating user!";
		}
		
		else
		{
			$mesg = ("Sign up success!"." "."[<a href='/login.php'>login</a>]");
			//todo:slow redirect to login
		}
	}
?>

<html>
	<head>
		<title><?php print($title); ?> - Join</title>
		
		<style>
			<?php include("style.php"); ?>
		</style>
		
		<?php include("script.php"); ?>
	</head>
	
	<body>
		<?php include("menu.php"); ?>
		
		<form method="POST">
			Username: <input type="text" name="user" size="40" value="<?php print($post['user']); ?>" /><br/>
			Password: <input type="password" name="pass" size="40" /><br/>
			Confirm: <input type="password" name="cpwd" size="40" /><br/>
			Email: <input type="text" name="mail" size="40" value="<?php print($post['mail']); ?>" />
			<input type="submit" value="Join!" /><br/>
			<span class="info"><?php print($mesg); ?></span>
		</form>
	</body>
</html>
