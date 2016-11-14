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
		
		for (var y = 0; y < 16; ++y)
		{
			if (block[y] < 0x10) { outp += "0"; }
			outp += block[y].toString(16);
			iv[y] = block[y];
		}
		outp += "\n";
	}
	return (iiv.strTOhex() + "\n" + outp);
}

var rdecindx = 0, rdecdata = "";

function msgdec(mode)
{
	var i = rdecindx;
	if (i < mail.length)
	{
		var tmpivr = window.atob(mail[i][0]);
		var tmpkey = form_pub(window.atob(mail[i][1]));
		tmpkey = pri_dec(tmpkey[0], tmpkey[1], prikey);
		var tmpmsg = window.atob(mail[i][2]);
		tmpmsg = aesdec(tmpivr, tmpkey, tmpmsg);
		
		var seclist = mail[i][4].split(" ");
		if (seclist.length > 2)
		{
			var smpivr = window.atob(seclist[1]);
			var smpkey = form_pub(window.atob(seclist[2]));
			smpkey = pri_dec(smpkey[0], smpkey[1], prikey);
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
			
			if (j == 1)
			{
				tmpinf[j] = tmpinf[j].formDate();
			}
			
			if (j == 4)
			{
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
				t += ("<td "+tagc+" style='cursor: pointer; "+style+wide+"' onclick='window.location.href = \""+webp+"/view/?e="+mail[i][5]+"&m=read\"'>");
				t += (taga+newa);
				t += (tmpinf[j]);
				t += (newb+tagb);
				t += ("</td>");
				tmpinf[j] = t;
			}
		}
		
		if (mode == "list")
		{
			var cola = ("<td class='colmin'><input type='checkbox' name='chkmail' id='"+mail[i][5]+"' /></td>");
			var coln = ("<td class='colmin'><span>&nbsp;</span></td>");
			var colb = ("<td class='colmin'><span>&nbsp;</span></td>");
			var colc = ("<td class='colmin'><span>&nbsp;</span></td>");
			var colt = mail[i][6].split(" ");
			if (newa != "") { coln = ("<td class='colmin'><span class='glyphicon glyphicon-certificate txtgreen' style='top: 2px;'></span></td>"); }
			if (colt.length > 1) { colb = ("<td class='colmin'><span class='glyphicon glyphicon-file' style='top: 2px; color: #555555;'></span></td>"); }
			if (seclist.length > 2) { colc = ("<td class='colmin'><img src='"+webp+"/img/lock.png' /></td>"); }
			
			var cold = tmpinf[2];
			if (view == "o") { cold = tmpinf[3]; }
			
			rdecdata += ("<tr>"+cola+coln+colb+colc+tmpinf[1]+cold+tmpinf[4]+"</tr>\n");
			if (i == 0) { jQuery('#mail').html("<tr><td colspan='7'><center>Loading... <img src='"+webp+"/img/load.gif' /></center></td></tr>"); }
		}
		
		if (mode == "view")
		{
			var prea = "<span>", preb = "</span>";
			var head = (" style='width: 0px; white-space: nowrap; text-align: right; border: 0px;' ");
			var valu = (" style='width: 100%; text-align: left; border: 0px;' ");
			
			jQuery('#date').text(tmpinf[1]);
			jQuery('#from').html("<a href='"+webp+"/make/?e="+tmpinf[2]+"'>"+tmpinf[2]+"</a>");
			jQuery('#dest').text(tmpinf[3]);
			
			var rlist = mail[i][6].split(" "), flist = fnames.split(" ");
			for (var j in rlist)
			{
				if (j > 0)
				{
					jQuery('#attach').append("<a href='"+webp+"/file/?i="+tmpivr.strTOhex()+"&k="+tmpkey.strTOhex()+"&e="+mail[i][5]+"&f="+rlist[j]+"'><span class='glyphicon glyphicon-file'></span> <span id='attach"+j+"'></span></a> &nbsp; &nbsp; ");
					jQuery('#attach'+j).text(window.atob(flist[j]));
				}
			}
			
			jQuery('#subj').text(tmpinf[4]);
			
			tmpmsg = window.atob(mail[i][3]);
			tmpmsg = aesdec(tmpivr, tmpkey, tmpmsg);
			if (seclist.length > 2)
			{
				tmpmsg = window.atob(tmpmsg.rstrTrim());
				tmpmsg = aesdec(smpivr, smpkey, tmpmsg);
			}
			
			jQuery('#mesg').text(tmpmsg);
			jQuery('#mesg').html(jQuery('#mesg').html().replace(/\n/g, "<br />"));
		}
		
		rdecindx += 1;
		setTimeout(function() { msgdec(mode); }, 0);
	}
	
	else
	{
		if (mode == "list")
		{
			if (rdecdata != "") { jQuery('#mail').html(rdecdata); }
		}
	}
}

function keypre()
{
	var deckey = localStorage.getItem("encr");
	var tmpivr = "", tmpkey = deckey.hexTOstr(), tmpmsg = enckey.hexTOstr();
	
	tmpivr = tmpmsg.substr(0, 16);
	tmpmsg = tmpmsg.substr(16);
	
	prikey = aesdec(tmpivr, tmpkey, tmpmsg);
	prikey = new BigInteger(prikey, 10);
}

function keygen()
{
	if (keyobj == null)
	{
		var l = prime.toString(10).length, r = "", s = "";
		for (var x = 0; x < l; ++x)
		{
			r += Math.floor(Math.random() * 10).toString();
			s += Math.floor(Math.random() * 10).toString();
		}
		prikey = new BigInteger(r, 10);
		keyobj = new BigInteger(s, 10);
		
		prikey = prikey.mod(prime);
		while (prikey.compareTo(three) < 0)
		{
			prikey = prikey.add(BigInteger.ONE);
		}
	}
	
	keyobj = keyobj.mod(prime);
	while (keyobj.compareTo(three) < 0)
	{
		keyobj = keyobj.add(BigInteger.ONE);
	}
	
	var pnt = [keyobj, curve_25519(keyobj, cnst, prime)];
	//console.log("point:"+pnt[0].toString(10)+","+pnt[1].toString(10));
	if (pnt[1] != -1)
	{
		var x = pnt[0].modPow(three, prime).add(cnst.multiply(pnt[0].modPow(two, prime))).add(pnt[0]).mod(prime);
		var y = pnt[1].modPow(two, prime);
		//console.log("check:"+x.toString(10)+"?="+y.toString(10));
		if (x.equals(y))
		{
			var aG = point_mul(prikey, pnt, prime);
			
			jQuery('#prikey').html(prikey.toString(10));
			jQuery('#pubkey').html(pnt[0].toString(10)+"\n"+pnt[1].toString(10)+"\n\n"+aG[0].toString(10)+"\n"+aG[1].toString(10));
			
			var iv = "";
			for (var y = 0; y < 16; ++y) { iv += String.fromCharCode(Math.floor(Math.random() * 256)); }
			
			var encr = ("" + CryptoJS.SHA256(jQuery('#pass').val()) + "");
			jQuery('#enckey').val(encr);
			
			var sign = ("" + CryptoJS.SHA256(encr) + "");
			jQuery('#intkey').val(sign);
			
			var auth = ("" + CryptoJS.SHA256(sign) + "");
			jQuery('#idnkey').val(auth);
			
			var key = encr.hexTOstr();
			var outp = aesenc(iv, key, prikey.toString(10));
			jQuery('#prienc').val(outp);
			
			return 0;
		}
	}
	keyobj = keyobj.add(BigInteger.ONE);
	
	setTimeout("keygen();", 0);
}
