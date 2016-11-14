import re
import ssl
import socket

class gimap:
	def __init__(self):
		self.h = "imap.gmail.com"
		self.p = 993
		self.t = "tag"
		self.e = "\r\n"
		
		s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
		self.s = ssl.wrap_socket(s)
		self.s.connect((self.h, self.p))
		
		# Save the certificate data
		self.c = self.s.getpeercert(True)
		
		# Initial banner read
		imapdata = self.s.read()
	
	def cert(self):
		return [self.h, self.c]
	
	def eadr(self, adrs):
		elst = []
		for schr in "<>[]{}(),;":
			adrs = adrs.replace(schr, " ")
		for item in adrs.split(" "):
			item = item.strip()
			if (re.match("^.*@.*$", item)):
				elst.append(item)
		return elst
	
	def wait(self):
		buff = ""
		while (1):
			temp = self.s.read()
			if (not temp):
				return buff
			buff += temp
			for line in buff.split("\n"):
				line = line.strip()
				llow = line.lower()
				#print("debug","imap",line)
				if (llow.startswith("%s ok " % (self.t))):
					return buff
				if (llow.startswith("%s no " % (self.t)) or llow.startswith("%s bad " % (self.t))):
					raise Exception("imap", "error", line)
		return buff
	
	def auth(self, user, upwd):
		self.s.write("%s LOGIN %s %s%s" % (self.t, user, upwd, self.e))
		imapdata = self.wait()
	
	def boxl(self):
		self.s.write("%s LIST \"\" \"*\"%s" % (self.t, self.e))
		imapdata = self.wait()
		
		boxs = []
		for lineread in imapdata.split("\n"):
			lineread = lineread.strip()
			regxobjc = re.match("^\* list .* \"([^\"]+)\"$", lineread, re.I)
			if (regxobjc):
				mtid = str(regxobjc.group(1))
				boxs.append(mtid)
		
		return boxs
	
	def newm(self, mbox):
		self.s.write("%s STATUS %s (UNSEEN)%s" % (self.t, mbox, self.e))
		imapdata = self.wait()
		
		for lineread in imapdata.split("\n"):
			lineread = lineread.strip()
			regxobjc = re.match("^\* status .*unseen ([0-9]+).*$", lineread, re.I)
			if (regxobjc):
				return int(regxobjc.group(1))
		
		return 0
	
	def lbox(self, mbox, maxn):
		self.s.write("%s SELECT %s%s" % (self.t, mbox, self.e))
		imapdata = self.wait()
		
		mnum = 0
		for lineread in imapdata.split("\n"):
			lineread = lineread.strip()
			regxobjc = re.match("^\* ([0-9]+) exists$", lineread, re.I)
			if (regxobjc):
				mnum = int(regxobjc.group(1))
		
		self.s.write("%s SEARCH UNSEEN%s" % (self.t, self.e))
		imapdata = self.wait()
		
		msgn = []
		for lineread in imapdata.split("\n"):
			lineread = lineread.strip()
			regxobjc = re.match("^\* search ([0-9]+.*)$", lineread, re.I)
			if (regxobjc):
				msgn = str(regxobjc.group(1)).strip().split(" ")
		
		self.s.write("%s FETCH %d:%d (BODY.PEEK[HEADER])%s" % (self.t, mnum - maxn, mnum + maxn, self.e))
		imapdata = self.wait()
		
		info = {}; msgs = []
		for lineread in imapdata.split("\n"):
			lineread = lineread.strip()
			regxobjc = re.match("^\* ([0-9]+) fetch .*$", lineread, re.I)
			if (regxobjc):
				numb = str(regxobjc.group(1))
				if (len(info.keys()) > 0):
					msgs.append(info)
					info = {}
				info["numb"] = numb
			regxobjc = re.match("^(From|To|Subject|Cc|Date):(.*)$", lineread, re.I)
			if (regxobjc):
				mode = str(regxobjc.group(1)).lower()
				vals = str(regxobjc.group(2)).strip()
				if (mode == "cc"):
					mode = "to"
				if (not mode in info.keys()):
					info[mode] = ""
				if (info[mode] != ""):
					info[mode] += " "
				info[mode] += vals
		if (len(info.keys()) > 0):
			msgs.append(info)
			info = {}
		
		return [mnum, msgn, msgs]
	
	def read(self, mesg):
		self.s.write("%s FETCH %s (BODY[TEXT])%s" % (self.t, mesg["numb"], self.e))
		imapdata = self.wait()
		return imapdata
