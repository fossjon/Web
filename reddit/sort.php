Rating:
<?php
	foreach ($sort as $k => $v)
	{
		?>
		<?php if ($k != $pget['s']) { ?><a href="<?php print(gena('s', $k)); ?>" class="menu"><?php } ?><?php print($k); ?><?php if ($k != $pget['p']) { ?></a><?php } ?>
		<?php
	}
?>
&nbsp;|&nbsp;
Sort:
<select name="d" onchange="location.href = this.options[this.selectedIndex].value;">
<?php
	foreach ($dirs as $k => $v)
	{
		?>
		<option value="<?php print(gena('d', $k)); ?>" <?php if ($k == $pget['d']) { ?>selected=true<?php } ?>><?php print($k); ?></option>
		<?php
	}
?>
</select>
&nbsp;|&nbsp;
Page:
<a href="<?php print(gena('p', strval(intval($pget['p'])-1))); ?>" class="menu">Prev</a>
&nbsp;
<a href="<?php print(gena('p', strval(intval($pget['p'])+1))); ?>" class="menu">Next</a>
<br/><br/>
