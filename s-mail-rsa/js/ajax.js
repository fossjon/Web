function getkeyi(user, dest)
{
	if (user == "") { return 0; }
	if (user in skeys)
	{
		jQuery(dest+'n').text("["+user+"]");
		jQuery(dest).html(skeys[user].replace(/\n/g, "<br/>"));
		return 0;
	}
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if ((ajax.readyState == 4) && (ajax.status == 200))
		{
			var keyi = ajax.responseText.split("\n\n");
			if ((keyi.length > 1) && (keyi[0] != "") && (keyi[1] != ""))
			{
				skeys[user] = keyi[0];
				pkeys[user] = keyi[1];
				
				jQuery(dest+'n').text("["+user+"]");
				jQuery(dest).html(skeys[user].replace(/\n/g, "<br/>"));
				
				if (dest == "#keyf") { procdest({'keyCode':21}); }
			}
		}
	}
	ajax.open("GET", webp + "/info.php?mode=sum&user=" + user, true);
	ajax.send();
}

var lastn = "", lastp = "";

function mailloop()
{
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if ((ajax.readyState == 4) && (ajax.status == 200))
		{
			var nums = ajax.responseText.split(" ");
			if ((nums.length > 1) && (nums[0] != "") && (nums[1] != ""))
			{
				jQuery('#numb').text(nums[0]);
				var title = window.document.title;
				if ((lastn == "") || (lastp == ""))
				{
					lastn = nums[0];
					lastp = nums[1];
				}
				else
				{
					title = title.replace(/^[^ ]+ /i, "");
				}
				if ((lastn != nums[0]) || (lastp != nums[1])) { window.location.href = (webp + "/mail/?b=" + mpage + "&p=" + npage); }
				window.document.title = ("(" + nums[0] + ") " + title);
			}
		}
	}
	ajax.open("GET", webp + "/mail/?b="+mpage+"&u=1", true);
	ajax.send();
	setTimeout("mailloop();", 10 * 1000);
}

function markread(mesgname)
{
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		/* no-op */
	}
	ajax.open("GET", webp + "/view/?e="+mesgname+"&m=true", true);
	ajax.send();
}
