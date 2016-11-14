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
import M2Crypto

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
	mailhost = "quickchatr.com"
	
	mailinfo = {"from":"", "to":[], "body":"", "subject":"", "emsg":[]}
	dataflag = 0; datamark = ""; datastat = 0
	
	prestime = int(time.time())
	
	for lineread in sys.stdin.readlines():
		templine = lineread.strip()
		
		if (dataflag == 0):
			regxobjc = re.match("^(from|to): [ ]*(.+)$", templine, re.I)
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
			
			regxobjc = re.match("^subject: [ ]*(.+)$", templine, re.I)
			if (regxobjc):
				mailinfo["subject"] = str(regxobjc.group(1))
			
			regxobjc = re.match("^content-type: [ ]*.*boundary=([^ ;]+).*$", templine, re.I)
			if (regxobjc):
				datamark = str(regxobjc.group(1))
			
			regxobjc = re.match("^zsmsg-[^:]+: [ ]*(.+)$", templine, re.I)
			if (regxobjc):
				mailinfo["emsg"].append(str(regxobjc.group(1)))
			
			if (templine == ""):
				dataflag = 1
		
		else:
			if (datamark == ""):
				mailinfo["body"] += lineread
			
			else:
				if (templine == ("--" + datamark)):
					if (datastat == 0):
						datastat = 1
					else:
						datastat = 0
				
				elif (datastat == 1):
					regxobjc = re.match("^content-type: [ ]*.*plain.*$", templine, re.I)
					if (regxobjc):
						datastat = 2
				
				elif (datastat == 2):
					mailinfo["body"] += lineread
	
	mailinfo["date"] = str(prestime)
	mailinfo["subject"] = base64.b64encode(mailinfo["subject"].strip())
	mailinfo["body"] = base64.b64encode(mailinfo["body"].strip())
	
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
					os.system("/usr/bin/sudo /var/www/s-mail/sys/pperm.py www-data nogroup 6770 %s" % (filepath))
					
					tempobjc = open("%s/%s/%s.auth" % (mailpath, mailinfo["name"], mailinfo["name"]), "r")
					userlist = tempobjc.read().split("\n")
					tempobjc.close()
					
					if (len(userlist) > 3):
						encrflag = "plain"
						for ekeyitem in mailinfo["emsg"]:
							ekeylist = ekeyitem.split(" ")
							if ((len(ekeylist) > 2) and (ekeylist[0] == mailinfo["name"])):
								encrflag = ("secure %s %s" % (ekeylist[1], ekeylist[2]))
						
						encrsivr = Crypto.Random.new().read(16)
						encrskey = Crypto.Random.new().read(32)
						
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
						encrskey = base64.b64encode(encrskey)
						
						rsakbioo = M2Crypto.BIO.MemoryBuffer(base64.b64decode(userlist[2]))
						rsakobjc = M2Crypto.RSA.load_pub_key_bio(rsakbioo)
						rsactext = rsakobjc.public_encrypt(encrskey, M2Crypto.RSA.pkcs1_padding)
						rsacenco = rsactext.encode("base64")
						for c in ["\0", "\t", "\r", "\n", " "]:
							rsacenco = rsacenco.replace(c, "")
						
						fileobjc.write(encrsivr + "\n" + rsacenco + "\n" + "\n".join(encrlist) + "\n" + encrflag + "\n")
					
					fileobjc.close()
		
		if (len(sys.argv) > 1):
			if (sys.argv[1] == "sent"):
				sys.exit(0)

if (__name__ == "__main__"):
	main()
