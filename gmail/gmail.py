#!/usr/bin/python

# Note: API source code can be found here  https://pypi.python.org/pypi/gdata/  &&  http://pyasn1.sourceforge.net/
# Warning: This script modifies contact note data
# Note: This script relies on a OpenSSL binary for key generation as I don't trust myself enough to do it correctly natively
# Info: This script connects to an external service for public key exchanges

# Note: If you're using 2 factor auth then you need an application auth token here  https://accounts.google.com/IssuedAuthSubTokens?hide_authsub=1&hl=en

import base64
import getpass
import hashlib
import os
import re
import random
import string
import subprocess
import sys
import urllib
import urllib2

import Crypto.Cipher.AES
import Crypto.Random

sys.path.append("lib")

import cert
import gimap
import gpeer

def sntc(data):
	path = "./words"
	words = {"adjv":[], "noun":[], "verb":[]}
	order = ["adjv", "noun", "verb"]
	reslt = {"adjv":[], "noun":[], "verb":[]}
	ordri = 0; ordrl = len(order)
	for k in words.keys():
		fobj = open("%s/%s.txt" % (path, k), "r")
		words[k] = fobj.readlines()
		fobj.close()
	x = 0; l = len(data)
	bits = 0; bitl = 10; buff = 0
	while (x < l):
		if (bits < bitl):
			buff = ((buff << 8) | ord(data[x])); bits += 8
			x += 1
			continue
		indx = (buff >> (bits - bitl))
		buff = (buff & (pow(2, bits - bitl) - 1)); bits -= bitl
		reslt[order[ordri]].append(words[order[ordri]][indx].strip())
		ordri = ((ordri + 1) % ordrl)
	indx = 0; jump = (((len(data) * 8) / bitl) / 4)
	s = ""
	indx = ((indx + jump) % len(reslt["adjv"])); s += (reslt["adjv"][indx] + " "); jump += 1
	indx = ((indx + jump) % len(reslt["noun"])); s += (reslt["noun"][indx] + " "); jump += 2
	indx = ((indx + jump) % len(reslt["verb"])); s += (reslt["verb"][indx] + " "); jump += 3
	indx = ((indx + jump) % len(reslt["adjv"])); s += (reslt["adjv"][indx] + " "); jump += 4
	indx = ((indx + jump) % len(reslt["noun"])); s += (reslt["noun"][indx] + " "); jump += 5
	return s.strip()

def genk():
	p1 = subprocess.Popen(["openssl", "genrsa", "2048"], stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	p2 = subprocess.Popen(["openssl", "rsa", "-text", "-noout"], stdin=p1.stdout, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
	p1.stdout.close()
	
	output = p2.communicate()[0]
	
	e = [0, ""]; d = [0, ""]; m = [0, ""]
	for line in output.split("\n"):
		line = line.lower()
		if (line.startswith("publicexponent:")):
			d[0] = 0; m[0] = 0
			e[1] = re.sub("[ ]+", " ", line.strip())
		elif (line.startswith("privateexponent:")):
			d[0] = 1; m[0] = 0
		elif (line.startswith("modulus:")):
			d[0] = 0; m[0] = 1
		elif (not line.startswith(" ")):
			d[0] = 0; m[0] = 0
		elif (d[0] == 1):
			d[1] += re.sub("[^0-9a-f]", "", line.strip())
		elif (m[0] == 1):
			m[1] += re.sub("[^0-9a-f]", "", line.strip())
	
	h = "0123456789abcdef"
	
	e[1] = e[1].split(" ")
	e[1] = int(e[1][1])
	
	t = 0
	for c in d[1]:
		t = ((t << 4) + h.index(c))
	d[1] = t
	
	t = 0
	for c in m[1]:
		t = ((t << 4) + h.index(c))
	m[1] = t
	
	'''aa = pow(5,e[1],m[1]); ab = pow(aa,d[1],m[1])
	ba = pow(5,d[1],m[1]); bb = pow(ba,e[1],m[1])
	print(5,"==",ab,"==",bb)'''
	
	return [e[1], d[1], m[1]]

def enck(keys, mesg):
	minm = 0
	for e in keys:
		if (minm < 3):
			minm = e[2]
		minm = min(minm, e[2])
	
	k = random.randint(3, minm - 3)
	skey = str(k); smin = str(minm)
	while (len(skey) > (len(smin) - 9)):
		skey = skey[1:]
	ciphskey = hashlib.sha256(skey).digest()
	
	ciphinpt = mesg
	while ((len(ciphinpt) % 16) != 0):
		ciphinpt += "\0"
	t = 0
	
	ciphinit = Crypto.Random.new().read(Crypto.Cipher.AES.block_size)
	ciphobjc = Crypto.Cipher.AES.new(ciphskey, Crypto.Cipher.AES.MODE_CBC, ciphinit)
	
	ciphdata = (ciphinit + ciphobjc.encrypt(ciphinpt))
	ciphsign = hashlib.sha256(ciphskey + ciphdata + ciphskey).digest()
	ciphenco = (base64.b64encode(ciphsign + ciphdata) + "\n")
	
	for e in keys:
		npre = ""; npos = ""
		for x in range(0, 4):
			npre += str(random.randint(1, 9))
			npos += str(random.randint(1, 9))
		temp = int(npre + skey + npos)
		p = pow(temp, e[0], e[2])
		ciphenco += (str(e[2]) + " " + str(p) + "\n")
	
	return ciphenco

def gett(site, user, upwd):
	f = open("./cfg/pkey.txt", "r")
	e = int(f.readline().strip())
	d = int(f.readline().strip())
	m = int(f.readline().strip())
	f.close()
	
	emsg = enck([[e, d, m]], user + "\0" + upwd)
	
	parg = {"mode":"auth", "data":emsg}
	data = urllib.urlencode(parg)
	reqs = urllib2.Request(site, data)
	resp = urllib2.urlopen(reqs)
	resd = resp.read()
	
	return resd

def main():
	postpage = "https://quickchatr.com/keyxfr.php"
	emailadr = sys.argv[1]
	password = getpass.getpass("Password: ")
	mailbox = "inbox"
	
	try:
		gmailb = gimap.gimap()
		
		c = gmailb.cert()
		h = hashlib.sha256(c[1]).hexdigest()
		d = hashlib.sha256(c[1]).digest()
		s = sntc(d)
		
		print(c[0],h,s)
		print("")
		
		gmailb.auth(emailadr, password)
		
		print(gett(postpage, emailadr, "*"))
		
		mboxname = ""
		for mbid in gmailb.boxl():
			if (mbid.lower() == mailbox.lower()):
				mboxname = mbid
		
		'''msgn = gmailb.newm(mboxname)
		print(msgn)
		print("")'''
		
		msgs = gmailb.lbox(mboxname, 10)
		print("Messages: [%d] %s" % (msgs[0], msgs[1]))
		for mesg in msgs[2]:
			print("%s %s %s %s" % (mesg["numb"], mesg["date"], gmailb.eadr(mesg["from"]), mesg["subject"]))
		print("")
		
		'''lmsg = (len(msgs[1]) - 1)
		print("Messages: %s" % (msgs[1][lmsg]))
		print("")
		p = raw_input("? ")
		print(gmailb.read(msgs[1][lmsg]))
		print("")'''
		
		'''gpeero = gpeer.gpeer(emailadr, password)
		for peer in gpeero.lusr():
			print(peer)
		print("")'''
		
		'''print(genk())
		print("")'''
	
	except:
		pass

if (__name__ == "__main__"):
	main()
