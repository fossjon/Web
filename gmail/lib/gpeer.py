import gdata.contacts.client

class gpeer:
	def __init__(self, user, upwd):
		self.i = "fossjon-QuickChatr-1"
		self.g = gdata.contacts.client.ContactsClient(source=self.i)
		self.g.ClientLogin(user, upwd, self.g.source)
	
	def lusr(self):
		peers = []
		feed = self.g.GetContacts()
		
		while (1):
			if ((not feed) or (not feed.entry)):
				break
			
			for i, entry in enumerate(feed.entry):
				peerflag = 0
				
				peername = ""
				if (entry.name):
					if (entry.name.full_name):
						peername = entry.name.full_name.text
					
					else:
						if (entry.name.given_name):
							peername += entry.name.given_name.text
						
						if (entry.name.family_name):
							if (peername != ""):
								peername += " "
							peername += entry.name.family_name.text
				
				else:
					peername = entry.title.text
				
				peermail = []
				for emailobj in entry.email:
					peermail.append(emailobj.address)
				
				peerdata = ""
				if (entry.content):
					peerdata = entry.content.text
				
				peers.append([peername, peermail, peerdata, entry])
			
			next = feed.GetNextLink()
			if (next):
				feed = self.g.GetContacts(uri=next.href)
			else:
				feed = None
		
		return peers
	
	def lmod(self, peer, data):
		print("Updating [%s] with [%s]..." % (peer[1], data))
		try:
			peer[3].content.text = data
			updated = self.g.Update(peer[3])
		except:
			print("\tError!")
