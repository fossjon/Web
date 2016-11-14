#!/usr/bin/python

import base64
import hashlib
import os
import random
import re
import stat
import string
import sys
import time

import Crypto
import Crypto.Random
import Crypto.Cipher
import Crypto.Cipher.AES

import ec

def saferstr(inputstr, extrachr=""):
	charlist = (string.digits + string.uppercase + string.lowercase + extrachr)
	outpstri = ""
	for inputchr in inputstr:
		if (inputchr in charlist):
			outpstri += inputchr
	return outpstri

def getdsize(foldpath):
	foldsize = 0
	for dirpath, dirnames, filenames in os.walk(foldpath):
		for f in filenames:
			fp = os.path.join(dirpath, f)
			foldsize += os.path.getsize(fp)
	return foldsize

def main():
	mailpath = "/opt/mail"
	maildata = "/opt/data"
	mailhost = "quickchatr.com"
	mailcomd = "/usr/bin/sudo /var/www/s-mail/sys/pperm.py www-data nogroup 6770"
	
	dataflag = 0
	mailinfo = {"from":"", "to":[], "body":"", "subject":"", "emsg":None}
	
	datamark = []; markflag = 0
	attalist = []; attaobjc = None
	
	prestime = int(time.time())
	encrsivr = Crypto.Random.new().read(16)
	encrskey = Crypto.Random.new().read(32)
	
	for lineread in sys.stdin:
		templine = lineread.strip()
		
		if (dataflag == 0):
			regxobjc = re.match("^(from|to):[ ]*(.+)$", templine, re.I)
			if (regxobjc):
				mailtype = str(regxobjc.group(1)).lower()
				mailaddr = str(regxobjc.group(2)).replace(",", " ").replace(";", " ")
				for mailitem in mailaddr.split(" "):
					if ("@" in mailitem):
						mailitem = saferstr(mailitem, "_-+@.")
						if (mailtype == "from"):
							mailinfo[mailtype] = mailitem
						else:
							mailinfo[mailtype].append(mailitem)
			
			regxobjc = re.match("^subject:[ ]*(.+)$", templine, re.I)
			if (regxobjc):
				mailinfo["subject"] = str(regxobjc.group(1)).strip()
			
			regxobjc = re.match("^content-type:[ ]*.*boundary=([^ ;]+).*$", templine, re.I)
			if (regxobjc):
				datamark.append(str(regxobjc.group(1)).strip())
			
			regxobjc = re.match("^zsflag:[ ]*.*$", templine, re.I)
			if (regxobjc):
				mailinfo["emsg"] = []
			
			if (templine == ""):
				dataflag = 1
		
		else:
			if ((mailinfo["emsg"] != None) and (not None in mailinfo["emsg"])):
				regxobjc = re.match("^zsmsg-[^:]+:[ ]*(.+)$", templine, re.I)
				if (regxobjc):
					mailinfo["emsg"].append(str(regxobjc.group(1)).strip())
				else:
					mailinfo["emsg"].append(None)
			
			elif (len(datamark) < 1):
				mailinfo["body"] += lineread
			
			else:
				for markitem in datamark:
					if (templine.rstrip("-") == ("--" + markitem)):
						markflag = 1
						break
				
				for markitem in datamark:
					if (markflag == 1):
						regxobjc = re.match("^content-type:[ ]*.*boundary=([^ ;]+).*$", templine, re.I)
						if (regxobjc):
							datamark.append(str(regxobjc.group(1)).strip())
							break
						
						argxobjc = re.match("^content-type:[ ]*.*name=['\"]([^'\"]+)['\"].*$", templine, re.I)
						mrgxobjc = re.match("^content-type:[ ]*.*plain.*$", templine, re.I)
						
						if (argxobjc):
							if (attaobjc != None):
								attaobjc.close()
							
							while (1):
								rndstr = ""
								for x in range(0, 16):
									rndstr += str(random.randint(0, 9))
								
								tmprnd = ("attach.%d.%s" % (os.getpid(), rndstr))
								
								if (not os.path.isfile("%s/%s" % (maildata, tmprnd))):
									attaobjc = open("%s/%s" % (maildata, tmprnd), "w")
									os.system("%s %s/%s" % (mailcomd, maildata, tmprnd))
									attaobjc.write(str(argxobjc.group(1)).strip() + "\n")
									attalist.append(tmprnd)
									tempsivr = ("" + encrsivr + "")
									break
							
							markflag = 4
							break
						
						elif (mrgxobjc):
							markflag = 2
							break
				
				for markitem in datamark:
					if ((markflag == 2) or (markflag == 4)):
						if (templine == ""):
							markflag += 1
							break
					
					elif (markflag == 3):
						mailinfo["body"] += lineread
						break
					
					elif (markflag == 5):
						tempdata = ("" + lineread + "")
						while ((len(tempdata) % 16) != 0):
							tempdata += chr(0)
						
						tempskey = ("" + encrskey + "")
						encrsobj = Crypto.Cipher.AES.new(tempskey, Crypto.Cipher.AES.MODE_CBC, tempsivr)
						tempsmsg = encrsobj.encrypt(tempdata)
						tempsivr = ("" + tempsmsg[-16:] + "")
						
						attaobjc.write(tempsmsg)
						break
	
	mailinfo["date"] = str(prestime).strip()
	mailinfo["body"] = mailinfo["body"].strip()
	
	if (mailinfo["emsg"] == None):
		mailinfo["emsg"] = []
	if (None in mailinfo["emsg"]):
		mailinfo["emsg"].remove(None)
	
	if (attaobjc != None):
		attaobjc.close()
	
	for maildest in mailinfo["to"]:
		filepost = ""
		
		if (len(sys.argv) > 1):
			if (sys.argv[1] == "sent"):
				maildest = mailinfo["from"]
				filepost = ".read.sent"
		
		regxobjc = re.match("^([0-9A-Za-z]+)@%s$" % (mailhost), maildest, re.I)
		
		if (regxobjc):
			mailinfo["name"] = str(regxobjc.group(1))
			userfold = ("%s/%s" % (mailpath, mailinfo["name"]))
			
			maildata = ["%s\n%s\n%s\n%s\n%s\n" % (mailinfo["name"], mailinfo["date"], mailinfo["from"], ",".join(mailinfo["to"]), mailinfo["subject"]), "%s\n" % (mailinfo["body"])]
			preshash = hashlib.sha256(maildata[0] + maildata[1]).hexdigest()
			filepath = ("%s/%s.%d.%s%s" % (userfold, mailinfo["name"], prestime - (prestime % 10), preshash[0:16], filepost))
			
			if ((os.path.isdir(userfold)) and (not os.path.isfile(filepath))):
				dirbytes = getdsize(userfold)
				
				if (dirbytes < (100*1000*1000)):
					fileobjc = open(filepath, "w")
					os.system("%s %s" % (mailcomd, filepath))
					
					tempobjc = open("%s/%s/%s.auth" % (mailpath, mailinfo["name"], mailinfo["name"]), "r")
					userlist = tempobjc.read().split("\n")
					tempobjc.close()
					
					if (len(userlist) > 3):
						encrflag = "plain"
						for ekeyitem in mailinfo["emsg"]:
							ekeylist = ekeyitem.split(" ")
							if ((len(ekeylist) > 2) and (ekeylist[0] == mailinfo["name"])):
								encrflag = ("secure %s %s" % (ekeylist[1], ekeylist[2]))
						
						encrlist = []
						for mailitem in maildata:
							tempdata = ("" + mailitem + "")
							while ((len(tempdata) % 16) != 0):
								tempdata += chr(0)
							tempsivr = ("" + encrsivr + ""); tempskey = ("" + encrskey + "")
							encrsobj = Crypto.Cipher.AES.new(tempskey, Crypto.Cipher.AES.MODE_CBC, tempsivr)
							tempsmsg = encrsobj.encrypt(tempdata)
							encrlist.append(base64.b64encode(tempsmsg))
						
						encrsivr = base64.b64encode(encrsivr)
						rsacenco = ""
						
						pkeylist = base64.b64decode(userlist[2]).replace("\r", "").split("\n\n")
						if (len(pkeylist) > 1):
							pkeylist[0] = pkeylist[0].split("\n")
							pkeylist[1] = pkeylist[1].split("\n")
							if ((len(pkeylist[0]) > 1) and (len(pkeylist[1]) > 1)):
								keysplit = [0, 0]; s = (128 - 8)
								for x in range(0, 16):
									keysplit[0] = (keysplit[0] | (ord(encrskey[x]) << s))
									keysplit[1] = (keysplit[1] | (ord(encrskey[x+16]) << s))
									s -= 8
								point = [int(pkeylist[0][0]), int(pkeylist[0][1])]
								pubkey = [int(pkeylist[1][0]), int(pkeylist[1][1])]
								pubkencr = ec.pub_enc(point, pubkey, keysplit)
								rsacenco = base64.b64encode("%s\n%s\n\n%s\n%s\n" % (pubkencr[0][0], pubkencr[0][1], pubkencr[1][0], pubkencr[1][1]))
						
						fileobjc.write(encrsivr + "\n" + rsacenco + "\n" + "\n".join(encrlist) + "\n" + encrflag + "\n" + "file " + " ".join(attalist) + "\n")
					
					fileobjc.close()
		
		if (len(sys.argv) > 1):
			if (sys.argv[1] == "sent"):
				sys.exit(0)

if (__name__ == "__main__"):
	main()
