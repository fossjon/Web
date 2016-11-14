#!/usr/bin/python

import os
import re
import sys

def main():
	name = "[0-9A-Za-z]+[0-9A-Za-z.]*"
	if (re.match("^/opt/(mail|data)/(%s|%s/%s)$" % (name, name, name), sys.argv[4], re.I)):
		os.system("chown -R %s:%s %s" % (sys.argv[1], sys.argv[2], sys.argv[4]))
		os.system("chmod -R %s %s" % (sys.argv[3], sys.argv[4]))

if (__name__ == "__main__"):
	main()
