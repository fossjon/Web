function hint(e, v)
{
	if (v == "") { return ""; }
	if (e.keyCode == 13)
	{
		if (v.match(/^.*@.*$/)) { /* no-op */ }
		else
		{
			jQuery('#hint').text("");
			return (v + "@" + dnsn);
		}
	}
	else
	{
		if (v.match(/^.*@.*$/)) { jQuery('#hint').text(""); }
		else { jQuery('#hint').text("Press enter for [" + v + "@" + dnsn + "]"); }
	}
	return "";
}

function procdest(e)
{
	var emach = jQuery('#dest').val().match(/^([0-9A-Za-z]+)@*$/);
	var fmach = jQuery('#dest').val().match(/^.*[,; ]([0-9A-Za-z]+)@*$/);
	if (fmach) { var ehint = hint(e, fmach[1]); }
	else if (emach) { var ehint = hint(e, emach[1]); }
	else { var ehint = hint(e, jQuery('#dest').val()); }
	if (ehint != "")
	{
		jQuery('#dest').val(jQuery('#dest').val().replace(/@*$/, "") + "@" + dnsn + ", ");
	}
	var emails = jQuery('#dest').val().replace(/[,;]/g, " ").split(" ");
	var regx = new RegExp("^(.*)@" + dnsn + "$", "i");
	for (var i in emails)
	{
		var mach = emails[i].match(regx);
		if (mach)
		{
			getkeyi(mach[1], "#keyf");
		}
	}
	var hkeys = "";
	for (var k in skeys)
	{
		if (k == user) { continue; }
		hkeys += ("<div class='alert alert-info'><center><b>Pub Key ["+k+"]</b></center><br /><span style='font-family: monospace;'>"+skeys[k]+"</span></div>");
	}
	if (hkeys != "") { jQuery('#pkeys').html(hkeys); }
}

function subsend()
{
	var emails = jQuery('#dest').val().replace(/[,;]/ig, " ").replace(/ +/ig, " ").split(" ");
	var regx = new RegExp("^(.*)@" + dnsn + "$", "i");
	var flag = 1;
	
	for (var i in emails)
	{
		if (emails[i] == "") { continue; }
		if (!emails[i].match(regx))
		{
			flag = 0;
		}
	}
	
	if (jQuery('#dest').val() == "") { jQuery('#dest').focus(); return 0; }
	if (jQuery('#subj').val() == "") { jQuery('#subj').focus(); return 0; }
	if (jQuery('#mesg').val() == "") { jQuery('#mesg').focus(); return 0; }
	
	if (flag == 1)
	{
		jQuery('#type').val("emsg");
		
		var iv = "";
		for (var y = 0; y < 16; ++y) { iv += String.fromCharCode(Math.floor(Math.random() * 256)); }
		
		var key = "";
		for (var y = 0; y < 32; ++y) { key += String.fromCharCode(Math.floor(Math.random() * 256)); }
		
		var subj = aesenc(iv, key, jQuery('#subj').val());
		var smsg = subj.substr(32).replace(/\n/g, "").hexTOstr();
		jQuery('#subj').val(window.btoa(smsg));
		
		var mesg = aesenc(iv, key, jQuery('#mesg').val()).replace(/\n/g, "");
		var mmsg = mesg.substr(32).replace(/\n/g, "").hexTOstr();
		jQuery('#mesg').val(window.btoa(mmsg));
		
		var sleft = new BigInteger("256", 10);
		var keyl = new BigInteger("0", 10), keyr = new BigInteger("0", 10);
		for (var y = 0; y < 16; ++y)
		{
			var ktmpl = new BigInteger(""+key.charCodeAt(y)+"", 10);
			var ktmpr = new BigInteger(""+key.charCodeAt(y+16)+"", 10);
			keyl = keyl.multiply(sleft).add(ktmpl);
			keyr = keyr.multiply(sleft).add(ktmpr);
		}
		
		emails.push(user + "@" + dnsn);
		
		var eout = "", eivr = window.btoa(iv);
		for (var i in emails)
		{
			var mach = emails[i].match(regx);
			if (!mach) { continue; }
			
			var plist = form_pub(pkeys[mach[1]]);
			var bkey = pub_enc(plist[0], plist[1], [keyl, keyr]);
			
			eout += (mach[1] + " " + eivr + " " + bkey + ",");
		}
		jQuery('#ekey').val(eout);
		
		jQuery('#send').submit();
	}
	
	else
	{
		jQuery('#send').submit();
	}
}

function subauth()
{
	var encr = ("" + CryptoJS.SHA256(jQuery('#pass').val()) + "");
	var sign = ("" + CryptoJS.SHA256(encr) + "");
	var auth = ("" + CryptoJS.SHA256(sign) + "");
	localStorage.setItem("encr", encr);
	jQuery('#pass').val(auth);
	jQuery('#auth').submit();
}

function subjoin()
{
	var prem = "<font class='txtred'>", mesg = "", post = "</font>";
	if (jQuery('#user').val() == "")
	{
		jQuery('#mesg').html(prem + "Your username is empty!" + post);
		return 0;
	}
	if (jQuery('#pass').val() == "")
	{
		jQuery('#mesg').html(prem + "Your password is empty!" + post);
		return 0;
	}
	if (jQuery('#pass').val() != jQuery('#pwdc').val())
	{
		jQuery('#mesg').html(prem + "Your passwords do not match!" + post);
		return 0;
	}
	if (keyobj == null)
	{
		jQuery('#mesg').html("<font class='txtblue'>" + "Generating your keys now, please wait..." + "</font>");
		keygen();
		setTimeout("subjoin();", 1000);
		return 0;
	}
	if (jQuery('#prienc').val() != "")
	{
		jQuery('#mesg').html("<font class='txtblue'>" + "The form will be submitted in 10 seconds..." + "</font>");
		setTimeout("jQuery('#join').submit();", 10 * 1000);
		return 0;
	}
	setTimeout("subjoin();", 1000);
}

function subform(e, w)
{
	if (e && (e.keyCode == 13))
	{
		if (w == "j") { subjoin(); }
		if (w == "a") { subauth(); }
	}
}
