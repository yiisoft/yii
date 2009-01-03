CREATE TABLE User
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL,
	profile TEXT
);

CREATE TABLE Post
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	contentDisplay TEXT,
	status INTEGER NOT NULL,
	createTime INTEGER,
	updateTime INTEGER,
	commentCount INTEGER DEFAULT 0,
	authorId INTEGER NOT NULL,
	CONSTRAINT FK_post_author FOREIGN KEY (authorId)
		REFERENCES User (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE Comment
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	content TEXT NOT NULL,
	contentDisplay TEXT,
	status INTEGER NOT NULL,
	createTime INTEGER,
	author VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL,
	url VARCHAR(128),
	postId INTEGER NOT NULL,
	CONSTRAINT FK_comment_post FOREIGN KEY (postId)
		REFERENCES Post (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE TABLE Tag
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128) NOT NULL
);

CREATE TABLE PostTag
(
	postId INTEGER NOT NULL,
	tagId INTEGER NOT NULL,
	PRIMARY KEY (postId, tagId),
	CONSTRAINT FK_post FOREIGN KEY (postId)
		REFERENCES Post (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_tag FOREIGN KEY (tagId)
		REFERENCES Tag (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO User (username, password, email) VALUES ('demo','fe01ce2a7fbac8fafaed7c982a04e229','webmaster@example.com');
INSERT INTO Post (title, content, contentDisplay, status, createTime, updateTime, authorId) VALUES ('Welcome to Yii Blog','This blog system is developed using Yii. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.

Feel free to try this system by writing new posts and posting comments.','<p>This blog system is developed using Yii. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.</p>

<p>Feel free to try this system by writing new posts and posting comments.</p>',1,1230952187,1230952187,1);


INSERT INTO Tag (name) VALUES ('yii');
INSERT INTO Tag (name) VALUES ('blog');

INSERT INTO PostTag (postId, tagId) VALUES (1,1);
INSERT INTO PostTag (postId, tagId) VALUES (1,2);