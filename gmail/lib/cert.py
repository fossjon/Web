import base64
import re

import pyasn1.codec.der.decoder

class cert:
	def __init__(self):
		self.d = ""
	
	def pops(self, objc):
		# Purpose: Remove lists within lists and level the object (recursive)
		# Case: If this isn't even a list then just return the object
		if (type(objc) != list):
			return [[objc]]
		# Note: Loop thru the object and note how many list items are in it
		flag = 0; leng = len(objc)
		for item in objc:
			if (type(item) == list):
				flag += 1
		# Case: If we even have items to examine
		if (leng > 0):
			# Case: If no sub lists were found then just return the object
			if (flag == 0):
				return [objc]
			# Note: Loop thru the object and place any non-list items adjacent to each other in a list together
			retn = []; temp = []
			for item in objc:
				if (type(item) == list):
					if (len(temp) > 0):
						retn.append(temp)
						temp = []
					# Recursion: Call this method to check or clean this list item
					for i in self.pops(item):
						retn.append(i)
				else:
					temp.append(item)
			# Note: Append any last minute non-list items found at the end
			if (len(temp) > 0):
				retn.append(temp)
				temp = []
			objc = retn
		return objc
	
	def deru(self, dero, root=0):
		# Purpose: Recursive method taken mostly from the prettyPrint implementation
		data = []
		try:
			l = len(dero._componentValues)
		except:
			t = str((1,dero)).replace(" ","")
			if (t.startswith("(1,BitString(")):
				bitn = 0; bitl = 0; bits = ""
				for bit in dero:
					bitn = (bitn | (bit << (7 - bitl))); bitl += 1
					if (bitl > 7):
						bits += chr(bitn)
						bitn = 0; bitl = 0
				dero = base64.b64encode(bits)
			else:
				dero = dero.prettyPrint()
			return [dero]
		for idx in range(l):
			if dero._componentValues[idx] is not None:
				componentType = dero.getComponentType()
				items = self.deru(dero._componentValues[idx], root + 1)
				for item in items:
					data.append(item)
		if (root != 0):
			return [data]
		return self.pops(data)
	
	def text(self, ders):
		derobj = pyasn1.codec.der.decoder.decode(ders)
		data = self.deru(derobj[0])
		
		info = {
			"algo":{
				"1.2.840.113549.1.1.1":"RSA-Encryption",
				"1.2.840.113549.1.1.2":"MD2-With-RSA-Encryption",
				"1.2.840.113549.1.1.4":"MD5-With-RSA-Encryption",
				"1.2.840.113549.1.1.5":"SHA1-With-RSA-Encryption",
				"1.2.840.113549.1.1.11":"SHA256-With-RSA-Encryption"
			},
			"data":{
				"0.9.2342.19200300.100.1.25":"DC",
				"1.3.6.1.4.1.311.60.2.1.3":"EV[C]",
				"1.3.6.1.4.1.311.60.2.1.2":"EV[S]",
				"2.5.4.6":"C", "2.5.4.8":"S", "2.5.4.7":"L",
				"2.5.4.16":"PA", "2.5.4.17":"PC", "2.5.4.18":"PO",
				"2.5.4.20":"T#", "2.5.4.5":"S#",
				"2.5.4.9":"ST", "2.5.4.15":"BCat", "2.5.4.11":"OU",
				"2.5.4.10":"ON", "2.5.4.49":"DN", "2.5.4.3":"CN"
			},
			"x509":{
				"2.5.29.19":"BC"
			},
			"month":{
				"01":"Jan", "02":"Feb", "03":"Mar", "04":"Apr",
				"05":"May", "06":"Jun", "07":"Jul", "08":"Aug",
				"09":"Sep", "10":"Oct", "11":"Nov", "12":"Dec"
			}
		}
		
		f = {
			"info":{},
			"issu":{"info":[], "CN":""},
			"time":{"beg":"", "end":""},
			"subj":{"info":[], "CN":""},
			"pubk":{"a":"", "s":"", "m":"", "e":""},
			"x509":{"BC":"FALSE"},
			"prvs":{"a":"", "s":""}
		}
		
		h = "0123456789abcdef"
		
		if (len(data) > 2):
			i = (data.pop(0) + data.pop(0))
			if (len(i) > 2):
				f["info"] = {"v":i[0], "s":i[1], "a":i[2]}
			if (f["info"]["a"] in info["algo"].keys()):
				f["info"]["a"] = info["algo"][f["info"]["a"]]
			while (len(data) > 0):
				i = data.pop(0)
				while (len(i) < 2):
					i.insert(0, "")
				if (f["issu"]["CN"] == ""):
					if (i[0] in info["data"].keys()):
						f["issu"][info["data"][i[0]]] = i[1]
						f["issu"]["info"].append(info["data"][i[0]] + "=" + i[1])
				elif (f["time"]["beg"] == ""):
					if (re.match("^[0-9]{12}Z$", i[0]) and re.match("^[0-9]{12}Z$", i[1])):
						bm = i[0][2:4]; em = i[1][2:4]
						if (bm in info["month"].keys()):
							bm = info["month"][bm]
						if (em in info["month"].keys()):
							em = info["month"][em]
						f["time"]["beg"] = ("%s %s, %s:%s:%s, 20%s GMT" % (bm, i[0][4:6], i[0][6:8], i[0][8:10], i[0][10:12], i[0][0:2]))
						f["time"]["end"] = ("%s %s, %s:%s:%s, 20%s GMT" % (em, i[1][4:6], i[1][6:8], i[1][8:10], i[1][10:12], i[1][0:2]))
				elif (f["subj"]["CN"] == ""):
					if (i[0] in info["data"].keys()):
						f["subj"][info["data"][i[0]]] = i[1]
						f["subj"]["info"].append(info["data"][i[0]] + "=" + i[1])
				elif (f["pubk"]["a"] == ""):
					if (i[0] in info["algo"].keys()):
						f["pubk"]["a"] = info["algo"][i[0]]
				elif (f["pubk"]["m"] == ""):
					f["pubk"]["s"] = "(0 bit)"
					f["pubk"]["e"] = "0"
					f["pubk"]["m"] = "00"
					t = pyasn1.codec.der.decoder.decode(base64.b64decode(i[1]))
					t = self.deru(t[0])
					if (len(t) > 0):
						if (len(t[0]) > 1):
							m = int(t[0][0]); f["pubk"]["m"] = ""
							f["pubk"]["e"] = t[0][1]
							j = 0
							while (m > 0):
								b = (m & 0xf)
								if ((j > 0) and ((j % 2) == 0)):
									f["pubk"]["m"] = (":" + f["pubk"]["m"])
								f["pubk"]["m"] = (h[b] + f["pubk"]["m"])
								m = (m >> 4)
								j = (j + 1)
							if ((j % 2) == 1):
								f["pubk"]["m"] = ("0" + f["pubk"]["m"])
								j = (j + 1)
							l = f["pubk"]["m"].split(":")
							x = 0
							while ((x + 16) < len(l)):
								x += 16
								l.insert(x, "\n")
								x += 1
							f["pubk"]["m"] = ":".join(l).replace(":\n:", "\n" + (" " * 36))
							b = 1
							while (b < (j * 4)):
								b = (b << 1)
							f["pubk"]["s"] = ("(%d bit)" % (b))
				elif (f["x509"]["BC"] == ""):
					if (i[0] in info["x509"].keys()):
						f["x509"][info["x509"][i[0]]] = i[1]
						if (info["x509"][i[0]] == "BC"):
							bc = "FALSE"
							if ("false" in i[1].lower()):
								bc = "TRUE"
							f["x509"]["BC"] = bc
				elif (f["prvs"]["a"] == ""):
					if (i[0] in info["algo"].keys()):
						f["prvs"]["a"] = info["algo"][i[0]]
				elif (f["prvs"]["s"] == ""):
					f["prvs"]["s"] = ""
					j = 0
					for c in base64.b64decode(i[1]):
						d = ord(c)
						if (f["prvs"]["s"] != ""):
							if ((j % 16) == 0):
								f["prvs"]["s"] += ("\n" + (" " * 21))
							else:
								f["prvs"]["s"] += ":"
						f["prvs"]["s"] += (h[(d >> 4) & 0xf])
						f["prvs"]["s"] += (h[(d >> 0) & 0xf])
						j += 1
		
		return ("""
Certificate: 
    Info: 
          Version:   %s
          Serial:    %s
          Algorithm: %s

    Data: 
          Validity: 
                     Not Before:  %s
                     Not After:   %s

          Issuer:    %s
          Subject:   %s

              Public Key: 
                          Type:     %s %s
                          Exponent: %s
                          Modulus: 
                                    %s

    X509: 
          Basic Constraints: %s=%s

    Signature: 
               Type: %s
               Data: 
                     %s
		""" % (
			f["info"]["v"], f["info"]["s"], f["info"]["a"],
			f["time"]["beg"], f["time"]["end"],
			", ".join(f["issu"]["info"]), ", ".join(f["subj"]["info"]),
			f["pubk"]["a"], f["pubk"]["s"], f["pubk"]["e"], f["pubk"]["m"],
			"CA", f["x509"]["BC"],
			f["prvs"]["a"], f["prvs"]["s"]
		)).strip()
