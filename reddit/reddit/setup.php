<?php
	/*
		
		DROP TABLE csrf;
		CREATE TABLE csrf (
			tokeuniq VARCHAR(256) PRIMARY KEY, userid INT, tokemade TIMESTAMP
		);
		
		DROP TABLE logins;
		CREATE TABLE logins (
			userid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(256) NOT NULL,
			hashsalt VARCHAR(256) NOT NULL, hashpass VARCHAR(256) NOT NULL,
			email VARCHAR(256) NOT NULL, usermade TIMESTAMP
		);
		
		DROP TABLE posts;
		CREATE TABLE posts (
			postid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, userid INT, subsid INT,
			postlink VARCHAR(256) NOT NULL, posthead VARCHAR(256) NOT NULL, postbody VARCHAR(4096) NOT NULL,
			postself VARCHAR(256) NOT NULL, postmade TIMESTAMP
		);
		
		DROP TABLE subs;
		CREATE TABLE subs (
			subsid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, subsname VARCHAR(256) NOT NULL, subsmade TIMESTAMP
		);
		
		DROP TABLE votes;
		CREATE TABLE votes (
			userid INT, postid INT, noteid INT, voteval INT, votemade TIMESTAMP
		);
		
		DROP TABLE comments;
		CREATE TABLE comments (
			noteid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, userid INT, postid INT, replyid INT,
			notebody VARCHAR(4096) NOT NULL, chain VARCHAR(8192), notemade TIMESTAMP
		);
		
	*/
?>
