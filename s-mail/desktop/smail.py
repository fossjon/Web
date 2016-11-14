#!/usr/bin/python

import os
import random
import re
import string
import sys
import time

import PyQt4
import PyQt4.QtGui
import PyQt4.QtCore
from PyQt4.QtCore import Qt

hostname = "quickchatr.com"

class draw(PyQt4.QtGui.QWidget):
	def __init__(self, root, which="home"):
		super(draw, self).__init__()
		
		self.o = PyQt4.QtGui
		self.oo = PyQt4.QtCore
		self.p = os.path.dirname(sys.argv[0])
		self.root = root
		
		self.spacer = (" " * 4)
		self.homehead = None; self.homespcr = None; self.homebody = None
		self.joinhead = None; self.joinspcr = None; self.joinbody = None
		
		if (which == "join"):
			self.setLayout(self.drawjoin())
		else:
			self.setLayout(self.drawhome())
	
	def resize(self):
		# Window resize
		
		for o in [self.widg, self.back]:
			o.setFixedWidth(self.root.w)
		
		# Home page sizing
		
		if (self.homehead):
			self.homehead.setFixedWidth(self.root.w * 0.90)
			self.homehead.setFixedHeight(32)
		
		if (self.homespcr):
			self.homespcr.setFixedWidth(((self.root.w * 0.90) - (self.root.w * 0.88)) / 2)
		
		if (self.homebody):
			self.homebody.setFixedWidth(self.root.w * 0.88)
		
		# Join page sizing
		
		if (self.joinhead):
			self.joinhead.setFixedWidth(self.root.w * 0.90)
			self.joinhead.setFixedHeight(32)
		
		if (self.joinspcr):
			self.joinspcr.setFixedWidth(((self.root.w * 0.90) - (self.root.w * 0.88)) / 2)
		
		if (self.joinbody):
			for o in [self.joinbody, self.joinback]:
				o.setFixedWidth(self.root.w * 0.88)
	
	def addspacer(self, vobj):
		# Spacer grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(10)
		rown = 1; coln = 0
		
		stxt = self.o.QLabel(" ")
		grid.addWidget(stxt, rown, coln); coln += 1
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vobj.addLayout(hlay)
		
		return (vobj, grid)
	
	def drawhead(self):
		high = 0
		
		# Main vertical layout object
		
		self.widg = self.o.QWidget()
		
		self.back = self.o.QLabel(self.widg)
		self.back.setStyleSheet("background-image: url("+self.p+"/img/back.png);")
		
		vlay = self.o.QVBoxLayout(self.widg)
		
		# Header grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(10)
		rown = 1; coln = 0
		
		limg = self.o.QPixmap(self.p + "/img/logo.png")
		labl = self.o.QLabel()
		labl.setPixmap(limg)
		grid.addWidget(labl, rown, coln); coln += 1
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vlay.addLayout(hlay)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Menu grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(10)
		rown = 1; coln = 0
		
		mbtn = self.o.QPushButton(" Home")
		mbtn.setIcon(self.o.QIcon(self.p + "/img/home.png"))
		mbtn.setIconSize(self.oo.QSize(16, 16))
		mbtn.clicked.connect(self.root.viewhome)
		grid.addWidget(mbtn, rown, coln); coln += 1
		
		mbtn = self.o.QPushButton(" Join")
		mbtn.setIcon(self.o.QIcon(self.p + "/img/join.png"))
		mbtn.setIconSize(self.oo.QSize(16, 16))
		mbtn.clicked.connect(self.root.viewjoin)
		grid.addWidget(mbtn, rown, coln); coln += 1
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vlay.addLayout(hlay)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Add some buffer space
		
		(vlay, grid) = self.addspacer(vlay)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Create scroll view
		
		stmp = self.o.QVBoxLayout()
		scrl = self.o.QScrollArea()
		stmp.setContentsMargins(0, 0, 0, 0)
		stmp.setSpacing(0)
		stmp.addWidget(scrl)
		scrl.setWidget(self.widg)
		
		for o in [self.widg, self.back]:
			o.setFixedHeight(high)
		
		return (stmp, vlay)
	
	def drawfinl(self, vobj):
		vobj.addStretch(1)
		return vobj
	
	def drawhome(self):
		high = 0
		(scrl, vobj) = self.drawhead()
		
		# Login grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(10)
		rown = 1; coln = 0
		
		self.uinp = self.o.QLineEdit()
		self.uinp.setFixedWidth(224)
		self.uinp.setPlaceholderText("username [@%s]" % (hostname))
		grid.addWidget(self.uinp, rown, coln); coln += 1
		
		self.pinp = self.o.QLineEdit()
		self.pinp.setFixedWidth(224)
		self.pinp.setPlaceholderText("Long Passphrase")
		self.pinp.setEchoMode(self.o.QLineEdit.Password)
		grid.addWidget(self.pinp, rown, coln); coln += 1
		
		logi = self.o.QPushButton(" Sign In")
		logi.setIcon(self.o.QIcon(self.p + "/img/lock.png"))
		logi.clicked.connect(self.root.sendauth)
		grid.addWidget(logi, rown, coln); coln += 1
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vobj.addLayout(hlay)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Add some buffer space
		
		(vobj, grid) = self.addspacer(vobj)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Home grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(0)
		rown = 1; coln = 0
		
		self.homehead = self.o.QLabel(self.spacer+"Home Page News!")
		self.homehead.setStyleSheet("color: white; font: bold 16px; border-radius: 2px; background: qlineargradient(x1: 0, y1: 0, x2: 0, y2: 1, stop: 0 #f9d835, stop: 0.5 #f3961c, stop: 1.0 #f3961c);")
		grid.addWidget(self.homehead, rown, coln, 1, 3); rown += 1; coln = 0
		
		self.homespcr = self.o.QLabel(" ")
		grid.addWidget(self.homespcr, rown, coln); coln += 1
		
		self.homebody = self.o.QLabel(self.spacer+"\nNew stuff\n".replace("\n","\n"+self.spacer))
		self.homebody.setAlignment(Qt.AlignTop)
		self.homebody.setStyleSheet("color: black; font: 12px; background: white;")
		grid.addWidget(self.homebody, rown, coln); coln += 1
		
		stxt = self.o.QLabel(" ")
		grid.addWidget(stxt, rown, coln); coln += 1
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vobj.addLayout(hlay)
		
		high += (grid.totalSizeHint().height() + 16)
		
		# Add stretch space
		
		vobj = self.drawfinl(vobj)
		
		for o in [self.widg, self.back]:
			high = max(o.geometry().height() + high, self.root.h)
			o.setFixedHeight(high)
		
		return scrl
	
	def drawjoin(self):
		high = 0
		(scrl, vobj) = self.drawhead()
		
		# Join grid
		
		grid = self.o.QGridLayout()
		grid.setSpacing(0)
		rown = 1; coln = 0
		
		self.joinhead = self.o.QLabel(self.spacer+"Join For Free!")
		self.joinhead.setStyleSheet("color: white; font: bold 16px; border-radius: 2px; background: qlineargradient(x1: 0, y1: 0, x2: 0, y2: 1, stop: 0 #2e88c4, stop: 0.5 #075698, stop: 1.0 #075698);")
		grid.addWidget(self.joinhead, rown, coln, 1, 3); rown += 1; coln = 0
		
		# beg: Inner join form layout
		
		self.joinspcr = self.o.QLabel(" ")
		grid.addWidget(self.joinspcr, rown, coln); coln += 1
		
		self.joinbody = self.o.QWidget()
		
		self.joinback = self.o.QLabel(self.joinbody)
		self.joinback.setStyleSheet("color: black; font: 12px; background: white;")
		
		vlay = self.o.QVBoxLayout(self.joinbody)
		
		innr = self.o.QGridLayout()
		innr.setSpacing(10)
		rowm = 1; colm = 0
		
		utxt = self.o.QLabel("Username")
		utxt.setStyleSheet("color: black;")
		utxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(utxt, rowm, colm); colm += 1
		
		uinp = self.o.QLineEdit()
		uinp.setFixedWidth(256)
		uinp.setPlaceholderText("username [@%s]" % (hostname))
		innr.addWidget(uinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Password")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		self.pinp = self.o.QLineEdit()
		self.pinp.setFixedWidth(256)
		self.pinp.setPlaceholderText("Long Passphrase")
		self.pinp.setEchoMode(self.o.QLineEdit.Password)
		innr.addWidget(self.pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Confirm")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		self.pcnf = self.o.QLineEdit()
		self.pcnf.setFixedWidth(256)
		self.pcnf.setPlaceholderText("Confirm Passphrase")
		self.pcnf.setEchoMode(self.o.QLineEdit.Password)
		innr.addWidget(self.pcnf, rowm, colm); colm += 1
		
		join = self.o.QPushButton(" Sign Up")
		join.setIcon(self.o.QIcon(self.p + "/img/join.png"))
		join.clicked.connect(self.root.sendjoin)
		innr.addWidget(join, rowm, colm); rowm += 1; colm = 0
		
		# Join form spacer
		
		utxt = self.o.QLabel("[captcha]")
		utxt.setStyleSheet("color: red;")
		utxt.setAlignment(Qt.AlignCenter | Qt.AlignVCenter)
		innr.addWidget(utxt, rowm, colm, 1, 3); rowm += 1; colm = 0
		
		utxt = self.o.QLabel("[You can ignore these fields below]")
		utxt.setStyleSheet("color: black;")
		utxt.setAlignment(Qt.AlignCenter | Qt.AlignVCenter)
		innr.addWidget(utxt, rowm, colm, 1, 3); rowm += 1; colm = 0
		
		utxt = self.o.QLabel("(Public) (Private)")
		utxt.setStyleSheet("color: blue;")
		utxt.setAlignment(Qt.AlignCenter | Qt.AlignVCenter)
		innr.addWidget(utxt, rowm, colm, 1, 3); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Private-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignTop)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QTextEdit()
		pinp.setFixedWidth(256)
		pinp.setFixedHeight(128)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Public-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignTop)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QTextEdit()
		pinp.setFixedWidth(256)
		pinp.setFixedHeight(128)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Cipher-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QLineEdit()
		pinp.setFixedWidth(256)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Sign-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QLineEdit()
		pinp.setFixedWidth(256)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Auth-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignVCenter)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QLineEdit()
		pinp.setFixedWidth(256)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		ptxt = self.o.QLabel("Secured-Key")
		ptxt.setStyleSheet("color: black;")
		ptxt.setAlignment(Qt.AlignRight | Qt.AlignTop)
		innr.addWidget(ptxt, rowm, colm); colm += 1
		
		pinp = self.o.QTextEdit()
		pinp.setFixedWidth(256)
		pinp.setFixedHeight(128)
		innr.addWidget(pinp, rowm, colm); rowm += 1; colm = 0
		
		hlay = self.o.QHBoxLayout()
		hlay.addLayout(innr)
		hlay.addStretch(1)
		
		vlay.addLayout(hlay)
		
		vlay = self.drawfinl(vlay)
		
		grid.addWidget(self.joinbody, rown, coln); coln += 1
		
		stxt = self.o.QLabel(" ")
		grid.addWidget(stxt, rown, coln); coln += 1
		
		# end: Inner join form layout
		
		hlay = self.o.QHBoxLayout()
		hlay.addStretch(1)
		hlay.addLayout(grid)
		hlay.addStretch(1)
		
		vobj.addLayout(hlay)
		
		# Add stretch space
		
		vobj = self.drawfinl(vobj)
		
		high += (grid.totalSizeHint().height() + 16)
		
		for o in [self.widg, self.back]:
			high = max(o.geometry().height() + high, self.root.h)
			o.setFixedHeight(high)
		
		self.joinbody.setFixedHeight(innr.totalSizeHint().height() + 16)
		self.joinback.setFixedHeight(innr.totalSizeHint().height() + 16)
		
		return scrl

class mail(PyQt4.QtGui.QMainWindow):
	def __init__(self):
		super(mail, self).__init__()
		
		self.o = PyQt4.QtGui
		self.oo = PyQt4.QtCore
		self.p = os.path.dirname(sys.argv[0])
		
		# General window setup
		
		self.w = 640; self.h = 640
		
		self.setMinimumWidth(self.w)
		self.setMinimumHeight(self.h)
		self.setGeometry(64, 64, self.w, self.h)
		self.setWindowTitle("Secure Email [S-Mail]")
		self.setWindowIcon(self.o.QIcon(self.p + "/img/icon.png"))
		
		self.homeview = draw(self, "home")
		self.joinview = draw(self, "join")
		
		self.appwidget = self.o.QStackedWidget()
		self.appwidget.addWidget(self.homeview)
		self.appwidget.addWidget(self.joinview)
		
		self.setCentralWidget(self.appwidget)
		self.viewhome()
	
	def resizeEvent(self, evt=None):
		g = self.geometry()
		self.w = g.width(); self.h = g.height()
		
		self.homeview.resize()
		self.joinview.resize()
	
	def viewhome(self):
		self.appwidget.setCurrentWidget(self.homeview)
		self.resizeEvent()
	
	def viewjoin(self):
		self.appwidget.setCurrentWidget(self.joinview)
		self.resizeEvent()
	
	def sendauth(self):
		print("auth")
	
	def sendjoin(self):
		print("join")

def main():
	mapp = PyQt4.QtGui.QApplication(sys.argv)
	mwin = mail()
	mwin.show()
	sys.exit(mapp.exec_())

if (__name__ == "__main__"):
	main()
