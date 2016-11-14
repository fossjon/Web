<?php include("auth.php"); ?>

<?php
	if ($post['user'] != "")
	{
		$info = getusr($post['user']);
		
		if (count($info) < 1)
		{
			$mesg = "Incorrect login!";
		}
		
		else
		{
			$salt = explode(";", $info["hashsalt"]);
			
			if (hash("sha256", $salt[0].$post['pass'].$salt[1]) != $info["hashpass"])
			{
				$mesg = "Incorrect login!";
			}
			
			else
			{
				$_SESSION["user"] = $info;
				$_SESSION["auth"] = 1;
				header("Location: /");
				die;
			}
		}
	}
?>

<html>
	<head>
		<title><?php print($title); ?> - Login</title>
		
		<style>
			<?php include("style.php"); ?>
		</style>
		
		<?php include("script.php"); ?>
	</head>
	
	<body>
		<?php include("menu.php"); ?>
		
		<form method="POST">
			Username: <input type="text" name="user" size="40" value="<?php print($post['user']); ?>" /><br/>
			Password: <input type="password" name="pass" size="40" />
			<input type="submit" value="Login" /><br/>
			<span class="error"><?php print($mesg); ?></span>
		</form>
	</body>
</html>
