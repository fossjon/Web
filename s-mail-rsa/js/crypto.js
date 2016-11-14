function aesdec(iiv, ikey, imesg)
{
	var outp = "";
	var iv = new Array(16), block = new Array(16);
	for (var x = 0; x < imesg.length; x=x)
	{
		var key = new Array(32);
		for (var y = 0; y < 32; ++y)
		{
			key[y] = ikey.charCodeAt(y);
		}
		
		var tmp = new Array(16);
		for (var y = 0; y < 16; ++y)
		{
			if (outp == "") { iv[y] = iiv.charCodeAt(y); }
			if (x < imesg.length)
			{
				block[y] = imesg.charCodeAt(x);
				tmp[y] = block[y];
				++x;
			}
		}
		
		AES_Init();
		AES_ExpandKey(key);
		AES_Decrypt(block, key);
		AES_Done();
		
		for (var y = 0; y < 16; ++y)
		{
			block[y] = (block[y] ^ iv[y]);
			outp += String.fromCharCode(block[y]);
			iv[y] = tmp[y];
		}
	}
	return outp;
}

function aesenc(iiv, ikey, imesg)
{
	var outp = "";
	var iv = new Array(16), block = new Array(16);
	for (var x = 0; x < imesg.length; x=x)
	{
		var key = new Array(32);
		for (var y = 0; y < 32; ++y)
		{
			key[y] = ikey.charCodeAt(y);
		}
		
		for (var y = 0; y < 16; ++y)
		{
			if (outp == "") { iv[y] = iiv.charCodeAt(y); }
			block[y] = 0;
			if (x < imesg.length)
			{
				block[y] = imesg.charCodeAt(x);
				++x;
			}
			block[y] = (block[y] ^ iv[y]);
		}
		
		AES_Init();
		AES_ExpandKey(key);
		AES_Encrypt(block, key);
		AES_Done();
		
		if (outp == "")
		{
			for (var y = 0; y < 16; ++y)
			{
				if (iv[y] < 0x10) { outp += "0"; }
				outp += iv[y].toString(16);
			}
			outp += "\n";
		}
		
		for (var y = 0; y < 16; ++y)
		{
			if (block[y] < 0x10) { outp += "0"; }
			outp += block[y].toString(16);
			iv[y] = block[y];
		}
		outp += "\n";
	}
	return outp;
}

var rdecindx = 0, rdecdata = "", rdecpkey = null;

function rsadec(mode)
{
	if (rdecpkey == null)
	{
		rdecpkey = new JSEncrypt();
		rdecpkey.setPrivateKey(prikey);
	}
	
	var i = rdecindx;
	if (i < mail.length)
	{
		var tmpivr = window.atob(mail[i][0]);
		var tmpkey = window.atob(rdecpkey.decrypt(mail[i][1]));
		var tmpmsg = window.atob(mail[i][2]);
		
		tmpmsg = aesdec(tmpivr, tmpkey, tmpmsg);
		
		var seclist = mail[i][4].split(" ");
		if (seclist.length > 2)
		{
			var smpivr = window.atob(seclist[1]);
			var smpkey = window.atob(rdecpkey.decrypt(seclist[2]));
		}
		
		var newa = "<b>", newb = "</b>";
		if (mail[i][5].match(/^.*\.read$/) || mail[i][5].match(/^.*\.read\..*$/))
		{
			newa = ""; newb = "";
		}
		
		var tmpinf = tmpmsg.split("\n");
		for (var j in tmpinf)
		{
			var wide = "width: 0px;", style = "white-space: nowrap;";
			var taga = "<span>", tagb = "</span>", tagc = "";
			
			if (j == 0)
			{
				tmpinf[j] = "<input type='checkbox' />";
			}
			
			if (j == 1)
			{
				tmpinf[j] = tmpinf[j].formDate();
			}
			
			if (j == 4)
			{
				tmpinf[j] = window.atob(tmpinf[j].rstrTrim());
				if (seclist.length > 2)
				{
					tmpinf[j] = window.atob(tmpinf[j].rstrTrim());
					tmpinf[j] = aesdec(smpivr, smpkey, tmpinf[j]);
				}
				wide = "width: 99%;"; tagc = "";
				taga = "<span>"; tagb = "</span>";
			}
			
			if (mode == "list")
			{
				if (j == 3) { tmpinf[3] = (tmpinf[3].substr(0, 24) + "..."); }
				var t = "";
				t += ("<td "+tagc+" style='"+style+wide+"'>");
				t += (taga+newa);
				t += (tmpinf[j]);
				t += (newb+tagb);
				t += ("</td>");
				tmpinf[j] = t;
			}
		}
		
		if (mode == "list")
		{
			var cold = tmpinf[2];
			if (view == "o") { cold = tmpinf[3]; }
			rdecdata += ("<tr style='cursor: pointer;' onclick='window.location.href = \""+webp+"/view/?e="+mail[i][5]+"&m=read\"'>"+tmpinf[0]+tmpinf[1]+cold+tmpinf[4]+"</tr>\n");
			if (i == 0) { jQuery('#mail').html("<tr><td colspan='4'><center>Loading... <img src='"+webp+"/img/load.gif' /></center></td></tr>"); }
		}
		
		if (mode == "view")
		{
			var prea = "<span>", preb = "</span>";
			var head = (" style='width: 0px; white-space: nowrap; text-align: right; border: 0px;' ");
			var valu = (" style='width: 100%; text-align: left; border: 0px;' ");
			
			jQuery('#date').text(tmpinf[1]);
			jQuery('#from').html("<a href='"+webp+"/make/?e="+tmpinf[2]+"'>"+tmpinf[2]+"</a>");
			jQuery('#dest').text(tmpinf[3]);
			jQuery('#subj').text(tmpinf[4]);
			
			tmpmsg = window.atob(mail[i][3]);
			tmpmsg = aesdec(tmpivr, tmpkey, tmpmsg);
			tmpmsg = window.atob(tmpmsg.rstrTrim());
			if (seclist.length > 2)
			{
				tmpmsg = window.atob(tmpmsg.rstrTrim());
				tmpmsg = aesdec(smpivr, smpkey, tmpmsg);
			}
			
			jQuery('#mesg').text(tmpmsg);
			jQuery('#mesg').html(jQuery('#mesg').html().replace(/\n/g, "<br />"));
		}
		
		rdecindx += 1;
		setTimeout(function() { rsadec(mode); }, 0);
	}
	
	else
	{
		if (mode == "list")
		{
			if (rdecdata != "") { jQuery('#mail').html(rdecdata); }
		}
	}
}

function rsapre()
{
	var deckey = localStorage.getItem("encr");
	var tmpivr = "", tmpkey = deckey.hexTOstr(), tmpmsg = enckey.hexTOstr();
	
	tmpivr = tmpmsg.substr(0, 16);
	tmpmsg = tmpmsg.substr(16);
	
	prikey = aesdec(tmpivr, tmpkey, tmpmsg);
}

function rsagen()
{
	rsaobj = new JSEncrypt({default_key_size:2048});
	rsaobj.getKey(function() {
		prikey = rsaobj.getPrivateKey();
		jQuery('#prikey').html(prikey);
		
		pubkey = rsaobj.getPublicKey();
		jQuery('#pubkey').html(pubkey);
		
		var iv = "";
		for (var y = 0; y < 16; ++y) { iv += String.fromCharCode(Math.floor(Math.random() * 256)); }
		
		var encr = ("" + CryptoJS.SHA256(jQuery('#pass').val()) + "");
		jQuery('#enckey').val(encr);
		
		var sign = ("" + CryptoJS.SHA256(encr) + "");
		jQuery('#intkey').val(sign);
		
		var auth = ("" + CryptoJS.SHA256(sign) + "");
		jQuery('#idnkey').val(auth);
		
		var key = encr.hexTOstr();
		var outp = aesenc(iv, key, prikey);
		jQuery('#prienc').val(outp);
	});
}
