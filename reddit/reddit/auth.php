<?php include("cred.php"); ?>

<?php
	session_start();
	//todo:session expire time
	
	$auth = 0;
	if (array_key_exists("auth", $_SESSION)) { $auth = $_SESSION["auth"]; }
	
	$mesg = "";
	$post = array(
		"user" => "", "pass"  => "", "cpwd" => "", "mail" => "",
		"link" => "", "head"  => "", "body" => "", "self" => "",
		"post" => "", "reply" => "", "type" => "", "num"  => "", "val" => ""
	);
	$pget = array("i" => "", "p" => "0", "q" => "", "s" => "top", "d" => "dsc");
	$repl = array(
		"&"  => "&#38;", "<"  => "&#60;", ">"  => "&#62;",
		"'"  => "&#39;", "\"" => "&#34;", "\\" => "&#92;",
		"\0" => "",      "\t" => "",      "\r" => ""
	);
	
	function safe($str, $map)
	{
		$out = "";
		$leng = strlen($str);
		for ($x = 0; $x < $leng; $x++)
		{
			$char = substr($str, $x, 1);
			if (array_key_exists($char, $map)) { $out .= $map[$char]; }
			else { $out .= $char; }
		}
		return $out;
	}
	
	function rnds($size, $chrs="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz")
	{
		$str = "";
		$len = strlen($chrs);
		while ($size > 0)
		{
			$str .= $chrs[mt_rand(0, $len - 1)];
			$size -= 1;
		}
		return $str;
	}
	
	function getusr($user)
	{
		$out = array();
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return $out; }
		$sql = ("SELECT * FROM logins WHERE username = '".$user."'");
		$result = $conn->query($sql);
		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
			{
				foreach ($row as $k => $v) { $out[$k] = $v; }
			}
		}
		$conn->close();
		return $out;
	}
	
	function makeuser($info)
	{
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return -1; }
		$sbeg = rnds(8);
		$send = rnds(8);
		$sql = ("INSERT INTO logins (username, hashsalt, hashpass, email) VALUES ('".$info["user"]."', '".$sbeg.";".$send."', '".hash("sha256", $sbeg.$info["pass"].$send)."', '".$info["mail"]."')");
		if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -2; }
		if (count(getusr($info["user"])) < 1) { $conn->close(); return -3; }
		$conn->close(); return 1;
	}
	
	function getpostn($num, $select="")
	{
		$out = array();
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return $out; }
		if ($select != "") { $select = ("*,".$select); }
		else { $select = "*"; }
		$sql = ("SELECT ".$select." FROM posts JOIN logins ON logins.userid = posts.userid");
		if ($num > 0)
		{
			$sql .= (" WHERE posts.postid = '".$num."'");
		}
		else
		{
			$sql .= (" WHERE logins.userid = '".$_SESSION["user"]["userid"]."'");
		}
		if ($num > 0) { /* no-op */ }
		else { $sql .= (" ORDER BY posts.postid DESC LIMIT 1"); }
		$result = $conn->query($sql);
		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
			{
				foreach ($row as $k => $v) { $out[$k] = $v; }
			}
		}
		$conn->close();
		return $out;
	}
	
	function makepost($info)
	{
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return -1; }
		$sql = ("INSERT INTO posts (userid, subsid, postlink, posthead, postbody, postself) VALUES ('".$_SESSION["user"]["userid"]."', 0, '".$info["link"]."', '".$info["head"]."', '".$info["body"]."', '".$info["self"]."')");
		if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -2; }
		$idnum = $conn->insert_id;
		$sql = ("INSERT INTO votes (userid, postid, noteid, voteval) VALUES ('".$_SESSION["user"]["userid"]."', '".$idnum."', 0, 1)");
		if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -3; }
		$conn->close();
		return getpostn($idnum);
	}
	
	function getnotes($postid, $noteid=0, $skip=0, $limit=100)
	{
		$out = array();
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return $out; }
		$sql = ("SELECT * FROM comments");
		$tmp = "";
		if ($postid > 0) { if ($tmp != "") { $tmp .= (" AND"); } $tmp .= (" postid = '".$postid."'"); }
		if ($noteid > 0) { if ($tmp != "") { $tmp .= (" AND"); } $tmp .= (" noteid = '".$noteid."'"); }
		if ($tmp != "") { $sql .= (" WHERE".$tmp); }
		$sql .= (" LIMIT ".$skip.", ".$limit);
		$result = $conn->query($sql);
		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
			{
				$tmp = array();
				foreach ($row as $k => $v) { $tmp[$k] = $v; }
				array_push($out, $tmp);
			}
		}
		$conn->close();
		return $out;
	}
	
	function makenote($info, $chain)
	{
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return -1; }
		$sql = ("INSERT INTO comments (userid, postid, replyid, notebody, chain) VALUES ('".$_SESSION["user"]["userid"]."', '".$info["post"]."', '".$info["reply"]."', '".$info["body"]."', '".$chain."')");
		if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -2; }
		$idnum = $conn->insert_id;
		$sql = ("INSERT INTO votes (userid, postid, noteid, voteval) VALUES ('".$_SESSION["user"]["userid"]."', 0, '".$idnum."', 1)");
		if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -3; }
		$conn->close();
		return getnotes($info["post"], $idnum);
	}
	
	function getposts($table, $cols, $filter="", $sort="", $dirs="", $skip=0, $limit=5, $union=false)
	{
		$out = array();
		$map = array(
			"posts"=>array("made"=>"postmade", "id"=>"postid"),
			"comments"=>array("made"=>"notemade", "id"=>"noteid")
		);
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { /* no-op */ }
		else
		{
			$sql = ("SELECT ".implode(",", $cols)." FROM ".$table);
			$sql .= (" LEFT JOIN logins ON logins.userid = ".$table.".userid");
			if ($table == "posts") { $sql .= (" LEFT JOIN subs ON subs.subsid = posts.subsid"); }
			if ($filter != "") { $sql .= (" WHERE ".$filter); }
			$sql .= (" GROUP BY ".$table.".".$map[$table]["id"]);
			if ($sort != "") { $sql .= (" ORDER BY ".$sort); }
			if ($dirs != "") { $sql .= (" ".$dirs); }
			$sql .= (" LIMIT ".$skip.", ".$limit);
			if ($union == true) { $sql = ("(".$sql.") UNION (".str_replace(" WHERE ".$filter, "", $sql).")"); }
			//print($sql."<br/>\n");
			$result = $conn->query($sql);
			if ($result->num_rows > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					$tmp = array();
					foreach ($row as $k => $v) { $tmp[$k] = $v; }
					array_push($out, $tmp);
				}
			}
			$conn->close();
		}
		return $out;
	}
	
	function formpost($select, $cols=array(), $sort="", $dirs="", $skip=0, $limit=10, $union=false)
	{
		global $auth;
		$col = array(
			"posts.postid", "postlink", "posthead", "postmade", "postself", "posts.userid", "username", "subs.subsid", "subsname",
			"(SELECT SUM(voteval) FROM votes WHERE votes.postid = posts.postid) AS votenum",
			"(SELECT COUNT(comments.noteid) FROM comments WHERE comments.postid = posts.postid) AS notenum"
		);
		
		if ($auth == 1) { array_push($col, "(SELECT voteval FROM votes WHERE votes.postid = posts.postid AND votes.userid = '".$_SESSION["user"]["userid"]."') AS uservote"); }
		else { array_push($col, "0 AS uservote"); }
		
		foreach ($cols as $c) { array_push($col, $c); }
		
		$x = 0; $limit = 10;
		$out = getposts("posts", $col, $select, $sort, $dirs, 0, $limit, $union);
		foreach ($out as $o)
		{
			if ($x >= $limit) { break; } $x += 1;
			
			//http://www.flaticon.com/free-icon/up-arrow_60708
			//http://www.flaticon.com/free-icon/down-arrow_60928
			
			$u = "lo"; if ($o["uservote"] > 0) { $u = "hi"; }
			$d = "lo"; if ($o["uservote"] < 0) { $d = "hi"; }
			
			$n = $o["notenum"]; $s = "";
			if ($n != 1) { $s = "s"; }
			
			print("<table>\n");
			print("<tr>\n");
				print("<td align='center' valign='middle' class='vote'>\n");
					print("<img src='img/up".$u.".png' style='width:12px;' onmouseover=\"this.src='img/uphi.png'; this.style.cursor='hand';\" onmousedown=\"vote(this,'post','".$o["postid"]."','1','".$u."');\" onmouseout=\"this.src='img/up".$u.".png'; this.style.cursor='pointer';\"/><br/>\n");
					print("<span style='color:green;'>".$o["votenum"]."</span><br/>\n");
					print("<img src='img/down".$d.".png' style='width:12px;' onmouseover=\"this.src='img/downhi.png'; this.style.cursor='hand';\" onmousedown=\"vote(this,'post','".$o["postid"]."','-1','".$d."');\" onmouseout=\"this.src='img/down".$d.".png'; this.style.cursor='pointer';\"/>\n");
				print("</td>");
				print("<td align='left' valign='middle'>\n");
					print("<a href='".$o["postlink"]."' class='menu'>".$o["posthead"]."</a><br/>\n");
					print("by <a href='/u.php?i=".$o["userid"]."' class='menu'>".$o["username"]."</a> ");
					print("on ".$o["postmade"]." in <a href='/r.php?i=".$o["subsid"]."' class='menu'>r/".$o["subsname"]."</a> ");
					print("[<a href='/p.php?i=".$o["postid"]."' class='menu'>".$n." comment".$s."</a>]\n");
				print("</td>\n");
			print("</tr>\n");
			
			if (in_array("postbody", $col))
			{
				print("<tr><td></td><td><table class='note'><tr><td>".preg_replace("/\n/", "<br/>\n", $o['postbody'])."</td></tr></table></td></tr>\n");
			}
			
			print("</table><br/>\n");
		}
	}
	
	function gena($srch, $repl)
	{
		global $args;
		$o = "";
		foreach ($args as $k => $v)
		{
			if ($o != "") { $o .= "&"; }
			if ($k == $srch) { $o .= ($k."=".$repl); }
			else { $o .= ($k."=".$v); }
		}
		return ("?".$o);
	}
	
	function vote($type, $num, $val)
	{
		global $mysql_host, $mysql_user, $mysql_pass, $mysql_database;
		$conn = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_database);
		if ($conn->connect_error) { return -1; }
		$i = intval($num);
		if ($i > 0)
		{
			$pnum = 0; $nnum = 0;
			if ($type == "post")
			{
				$pnum = $i;
				$tmp = getpostn($i);
			}
			else
			{
				$nnum = $i;
				$tmp = getnotes(0, $i);
			}
			if (count($tmp) > 0)
			{
				$v = intval($val);
				if ((-2 < $v) and ($v < 2))
				{
					$sql = ("DELETE FROM votes WHERE userid = '".$_SESSION["user"]["userid"]."'");
					if ($pnum > 0) { $sql .= (" AND postid = '".$pnum."'"); }
					else { $sql .= (" AND noteid = '".$nnum."'"); }
					if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -2; }
					$sql = ("INSERT INTO votes (userid, postid, noteid, voteval) VALUES ('".$_SESSION["user"]["userid"]."', '".$pnum."', '".$nnum."', '".$v."')");
					if ($conn->query($sql) === TRUE) { /* no-op */ } else { $conn->close(); return -3; }
				}
			}
		}
		$conn->close();
		return 1;
	}
	
	if (isset($_POST))
	{
		foreach ($_POST as $k => $v)
		{
			$post[$k] = safe(stripslashes($v), $repl);
		}
	}
	
	if (isset($_GET))
	{
		foreach ($_GET as $k => $v)
		{
			$pget[$k] = safe(stripslashes($v), $repl);
		}
	}
	
	//todo:csrf token generation (timed expirey, rate limited)
	$vers = ("v:1.3.1.15"."-".rnds(4, "0123456789abcdefghijklmnopqrstuvwxyz"));
	$title = "Reddit - The front page of the internet";
	$sort = array("top" => "votenum", "hot" => "notenum", "new" => "postmade");
	$dirs = array("dsc" => "DESC", "asc" => "ASC");
	$args = array("p" => $pget["p"], "s" => $pget["s"], "d" => $pget["d"]);
	
	if ($auth == 1)
	{
		if ($post["type"] != "")
		{
			vote($post["type"], $post["num"], $post["val"]);
			die;
		}
	}
?>
