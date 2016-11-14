#!/usr/bin/python

import os
import re
import sys

def main():
	name = "[0-9A-Za-z]+[0-9A-Za-z.]*"
	if (len(sys.argv) == 6):
		if (re.match("^/opt/(mail|data|temp)/%s(|/%s)$" % (name, name), sys.argv[4], re.I)):
			if (re.match("^/opt/(mail|data|temp)/%s(|/%s)$" % (name, name), sys.argv[5], re.I)):
				os.link(sys.argv[4], sys.argv[5])
				sys.argv[4] = sys.argv[5]
	if ((len(sys.argv) == 5) or (len(sys.argv) == 6)):
		if (re.match("^/opt/(mail|data|temp)/%s(|/%s)$" % (name, name), sys.argv[4], re.I)):
			os.system("chown -R %s:%s %s" % (sys.argv[1], sys.argv[2], sys.argv[4]))
			os.system("chmod -R %s %s" % (sys.argv[3], sys.argv[4]))
	if (len(sys.argv) == 7):
		if ((sys.argv[4] == "unlink") and (sys.argv[5] == "file")):
			if (re.match("^/opt/(mail|data|temp)/%s(|/%s)$" % (name, name), sys.argv[6], re.I)):
				os.unlink(sys.argv[6])

if (__name__ == "__main__"):
	main()
