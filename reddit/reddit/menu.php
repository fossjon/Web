<span class="mono"><b><a href="/" class="menu">Reddit</a> <?php print($vers); ?></b></span>
&nbsp;
<?php if ($auth == 1) { ?>
::&nbsp;
<a href="/msg.php" class="menu">Messages</a>
- <a href="/new.php" class="menu">Submit</a>
- [<a href="/u.php?i=<?php print($_SESSION['user']['userid']); ?>" class="menu"><?php print($_SESSION['user']['username']); ?></a>]
&nbsp;
<?php } ?>
<input type="text" name="q" size="30" placeholder="Search" />
&nbsp;
<?php if ($auth == 1) { ?>
<a href="/logout.php" class="menu">Logout</a>
<?php } else { ?>
<a href="/login.php" class="menu">Login</a>
&nbsp;
[<a href="/join.php" class="menu">or Join!</a>]
<?php } ?>
<br/><br/>
