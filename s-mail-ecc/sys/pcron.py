#!/usr/bin/python

import os
import re
import sys
import time

def main():
	flag = 0
	path = "/opt/mail"
	
	fobj = open("/etc/aliases", "r")
	mail = fobj.readlines()
	prem = ""
	fobj.close()
	
	adrs = []
	pflg = 0
	for name in mail:
		name = name.strip()
		if (re.match("^# pmail .*$", name, re.I)):
			pflg = 1
		elif (pflg == 0):
			prem += (name + "\n")
		elif (pflg == 1):
			temp = name.split(" ")
			adrs.append(temp[0].rstrip(":").strip())
	
	dirs = []
	temp = os.listdir(path)
	for item in temp:
		if (os.path.isdir("%s/%s" % (path, item))):
			dirs.append(item)
	
	if (flag == 0):
		for dirn in dirs:
			if (not dirn in adrs):
				flag = 1
				break
	
	if (flag == 0):
		for adrn in adrs:
			if (not adrn in dirs):
				flag = 1
				break
	
	if (flag == 1):
		astr = prem
		astr += ("# pmail %s\n" % (time.strftime("%Y-%m-%d %H:%M:%S")))
		for dirn in dirs:
			astr += ("%s: |/var/www/s-mail/sys/pmail.py\n" % (dirn))
		fobj = open("/etc/aliases", "w")
		fobj.write(astr)
		fobj.close()
		os.system("newaliases")
		os.system("postfix reload")

if (__name__ == "__main__"):
	main()
