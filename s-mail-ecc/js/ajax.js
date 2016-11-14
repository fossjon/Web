function getkeyi(user, dest)
{
	if (user == "") { return 0; }
	if (user in skeys)
	{
		jQuery(dest+'n').text("["+user+"]");
		jQuery(dest).html(skeys[user]);
		return 0;
	}
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() {
		if ((ajax.readyState == 4) && (ajax.status == 200))
		{
			var keyi = ajax.responseText.split("\n\n");
			if ((keyi.length > 2) && (keyi[0] != "") && (keyi[1] != "") && (keyi[2] != ""))
			{
				skeys[user] = "";
				var l = keyi[0].split("\n");
				for (var i in l) { skeys[user] += (l[i] + "<br/>"); }
				
				pkeys[user] = (keyi[1] + "\n\n" + keyi[2]);
				
				jQuery(dest+'n').text("["+user+"]");
				jQuery(dest).html(skeys[user]);
				
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
				var tpref = "", title = window.document.title;
				if ((lastn == "") || (lastp == ""))
				{
					lastn = nums[0]; lastp = nums[1];
				}
				if ((lastn != nums[0]) || (lastp != nums[1]))
				{
					window.location.href = surls;
				}
				if ((nums[0] != "") && (nums[0] != "0")) { tpref = ("(" + nums[0] + ") "); }
				window.document.title = (tpref + "S-Mail" + " [" + user +"]");
			}
		}
	}
	ajax.open("GET", webp + "/mail/?b=inbox&u=1", true);
	ajax.send();
	setTimeout("mailloop();", 10 * 1000);
}

function markmail(mesgname, mesgstat)
{
	var ajax = new XMLHttpRequest();
	ajax.onreadystatechange = function() { /* no-op */ }
	ajax.open("GET", webp + "/view/?e="+mesgname+"&"+mesgstat+"=true", true);
	ajax.send();
}
