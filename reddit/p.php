<?php include("auth.php"); ?>

<?php
	$i = intval($pget['i']);
	
	if ($auth != 0)
	{
		if ($post['body'] != "")
		{
			$p = intval($post['post']);
			$r = intval($post['reply']);
			
			$c = getpostn($p);
			if (count($c) > 0)
			{
				$notes = getnotes($p, $r);
				if (($r == 0) or (count($notes) > 0))
				{
					$c = "";
					if ($r != 0) { $c = ($notes[0]["chain"]." ".strval($r)); }
					makenote($post, $c);
				}
			}
		}
	}
?>

<html>
	<head>
		<title><?php print($title); ?> - p/<?php print($pget['i']); ?></title>
		
		<style>
			<?php include("style.php"); ?>
		</style>
		
		<?php include("script.php"); ?>
	</head>
	
	<body>
		<?php include("menu.php"); ?>
		<?php include("sort.php"); ?>
		
		<?php
			formpost("postid = '".$i."'", array("postbody"), "", "", 0, 1, false);
			
			if ($auth != 0)
			{
		?>
				<a href="javascript:void();" onclick="document.getElementById('postnote').style.display = 'block'; this.style.display = 'none';" class="menu">Reply<br/></a>
				<div id="postnote" style="display:none;"><form method="POST">
					<input type="hidden" name="post" value="<?php print($info['postid']); ?>" />
					<input type="hidden" name="reply" value="0" />
					<textarea name="body" rows="10" cols="45"></textarea>
					<input type="submit" value="Comment" /><br/>
				</form></div>
				<br/>
		<?php
			}
			
			function pre($n)
			{
				return str_repeat(str_repeat("&nbsp;", 2), $n);
			}
		?>
		
		Comments:
		<hr/>
		
		<?php
			$col = array(
				"comments.userid", "username", "comments.noteid", "replyid", "notebody", "chain", "notemade",
				"(SELECT SUM(voteval) FROM votes WHERE votes.noteid = comments.noteid) AS votenum"
			);
			
			if ($auth == 1) { array_push($col, "(SELECT voteval FROM votes WHERE votes.noteid = comments.noteid AND votes.userid = '".$_SESSION["user"]["userid"]."') AS uservote"); }
			else { array_push($col, "0 AS uservote"); }
			
			$out = getposts("comments", $col, "comments.postid = '".$i."'", "notemade", "ASC", 0, 100);
			
			// get any missing links in the comment reply chain
			$nums = array();
			foreach ($out as $o)
			{
				array_push($nums, strval($o["noteid"]));
				$s = explode(" ", $o["chain"]);
				foreach ($s as $c)
				{
					$i = intval($c);
					if ($i > 0)
					{
						if (!in_array($c, $nums))
						{
							array_push($nums, $c);
						}
					}
				}
			}
			$out = getposts("comments", $col, "comments.noteid IN (".implode(",", $nums).")", "notemade", "ASC", 0, 999);
			
			// build the comment thread out sideways like a 3D printer
			$ord = array();
			$block = array();
			$ids = array();
			$level = array();
			
			// process all root comment posts first
			for ($x = 0; $x < count($out); $x++)
			{
				if ($out[$x]["replyid"] == 0)
				{
					array_push($ord, $out[$x]);
					array_push($block, $x);
					array_push($ids, $out[$x]["noteid"]);
					array_push($level, 0);
				}
			}
			$l = 1;
			
			// process all child comment posts last
			while (count($out) != count($ord))
			{
				$tmp = array();
				for ($x = 0; $x < count($out); $x++)
				{
					if ((!in_array($x, $block)) and in_array($out[$x]["replyid"], $ids))
					{
						$i = 0;
						for ($y = 0; $y < count($ord); $y++)
						{
							if ($out[$x]["replyid"] == $ord[$y]["noteid"])
							{
								$i = $y;
							}
						}
						// check if the next comment is a reply to this comment item - if so, then slide, ending insert
						$y = $i;
						while (((y+1) < count($ord)) and ($ord[$y+1]["replyid"] == $out[$x]["replyid"]))
						{
							$y += 1;
						}
						array_splice($ord, $y + 1, 0, array($out[$x]));
						array_splice($level, $y + 1, 0, array($l));
						array_push($tmp, array($x, $out[$x]["noteid"]));
					}
				}
				foreach ($tmp as $i)
				{
					array_push($block, $i[0]);
					array_push($ids, $i[1]);
				}
				$l += 1;
			}
			
			// output the comment thread with the proper indenting levels
			$open = 0;
			for ($x = 0; $x < count($ord); $x++)
			{
				$u = "lo"; if ($ord[$x]["uservote"] > 0) { $u = "hi"; }
				$d = "lo"; if ($ord[$x]["uservote"] < 0) { $d = "hi"; }
				
				print("<br/><a name='".$ord[$x]["noteid"]."'></a><table class='struct'>\n");
					
					print("<tr><td align='center' valign='top' class='vote'>\n");
						print("<img src='img/up".$u.".png' style='width:12px;' onmouseover=\"this.src='img/uphi.png'; this.style.cursor='hand';\" onmousedown=\"vote(this,'note','".$ord[$x]["noteid"]."','1','".$u."');\" onmouseout=\"this.src='img/up".$u.".png'; this.style.cursor='pointer';\"/><br/>");
						print("<span style='color:green;'>".$ord[$x]["votenum"]."</span><br/>\n");
						print("<img src='img/down".$d.".png' style='width:12px;' onmouseover=\"this.src='img/downhi.png'; this.style.cursor='hand';\" onmousedown=\"vote(this,'note','".$ord[$x]["noteid"]."','-1','".$d."');\" onmouseout=\"this.src='img/down".$d.".png'; this.style.cursor='pointer';\"/>\n");
					print("</td>\n");
					print("<td align='left' valign='top'>\n");
						print("<table class='note'><tr><td>\n");
						print("<a href='/u.php?i=".$ord[$x]["userid"]."' class='menu'>".$ord[$x]["username"]."</a> ");
						print("said on ".$ord[$x]["notemade"].":<br/><br/>\n");
						print(preg_replace("/\n/", "<br/>\n", $ord[$x]["notebody"]));
						print("</td></tr></table>\n");
						print("<a href='#".$ord[$x]["replyid"]."' class='menu'>parent</a>\n");
						print("&nbsp;");
						print("<a href='#".$ord[$x]["noteid"]."' class='menu'>link</a>\n");
						if ($auth != 0)
						{
							print("&nbsp;");
							print("<a href='javascript:void();' onclick=\"document.getElementsByName('reply')[0].value = '".$ord[$x]["noteid"]."'; document.getElementById('reply".$x."').innerHTML = ('<br/><br/>' + document.getElementById('postnote').innerHTML); document.getElementsByName('reply')[0].value = '0';\" class='menu'>reply</a>\n");
							print("<span id='reply".$x."'></span>\n");
						}
					print("</td></tr>\n");
					// leave a row under open for the next child comment
					print("<tr><td></td><td align='left' valign='top'>\n");
				
				if ((($x+1) < count($ord)) and ($level[$x+1] > $level[$x]))
				{
					$open += 1;
				}
				else
				{
					print("</td></tr></table>\n");
					$y = $level[$x];
					while ($level[$x+1] < $y)
					{
						print("</td></tr></table>\n");
						$y -= 1; $open -= 1;
					}
				}
			}
		?>
	</body>
</html>
