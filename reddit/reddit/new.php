<?php include("auth.php"); ?>

<?php
	if ($auth == 0) { die; }
	
	$llen = strlen($post['link']);
	$hlen = strlen($post['head']);
	$blen = strlen($post['body']);
	
	if ($post['link'] != "")
	{
		if (!preg_match("/^http[s]?:\/\/.+\..+$/i", $post['link']))
		{
			$mesg = "Link should have the format: http(s)://...";
		}
		
		else if (($llen < 1) or (250 < $llen))
		{
			$mesg = "Link should be between 1 and 250 characters!";
		}
		
		else if (!preg_match("/^.+$/i", $post['head']))
		{
			$mesg = "Title should have at least 1 character!";
		}
		
		else if (($hlen < 1) or (250 < $hlen))
		{
			$mesg = "Title should be between 1 and 250 characters!";
		}
		
		else if (($blen < 0) or (2000 < $blen))
		{
			$mesg = "Body should be a maximum of 2000 characters!";
		}
		
		else
		{
			$info = makepost($post);
			
			if (count($info) < 1)
			{
				$mesg = "Error while creating new post!";
			}
			
			else
			{
				$mesg = ("Post success!"." "."[<a href='/p.php?i=".$info["postid"]."'>view</a>]");
				//todo:slow redirect to post
			}
		}
	}
?>

<html>
	<head>
		<title><?php print($title); ?> - New post</title>
		
		<style>
			<?php include("style.php"); ?>
		</style>
		
		<?php include("script.php"); ?>
	</head>
	
	<body>
		<?php include("menu.php"); ?>
		
		<form method="POST">
			Title: <input type="text" name="head" size="80" value="<?php print($post['head']); ?>" /><br/>
			URL: <input type="text" name="link" size="80" value="<?php print($post['link']); ?>" /><br/>
			Body: <textarea name="body" rows="10" cols="60"><?php print($post['body']); ?></textarea>
			<input type="checkbox" name="self" <?php if ($post['self'] == "on") { ?> checked=true <?php } ?> /> Self Post?<br/>
			<input type="submit" value="Submit!" />
			<span class="info"><?php print($mesg); ?></span>
		</form>
	</body>
</html>
