if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[FK_Comment_Post]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [dbo].[Comment] DROP CONSTRAINT FK_Comment_Post
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[FK_PostTag_Post]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [dbo].[PostTag] DROP CONSTRAINT FK_PostTag_Post
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[FK_PostTag_Tag]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [dbo].[PostTag] DROP CONSTRAINT FK_PostTag_Tag
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[FK_Post_User]') and OBJECTPROPERTY(id, N'IsForeignKey') = 1)
ALTER TABLE [dbo].[Post] DROP CONSTRAINT FK_Post_User
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Comment]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[Comment]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Post]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[Post]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[PostTag]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[PostTag]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[Tag]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[Tag]
GO

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[User]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[User]
GO

CREATE TABLE [dbo].[Comment] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[content] [text] COLLATE French_CI_AS NOT NULL ,
	[contentDisplay] [text] COLLATE French_CI_AS NULL ,
	[status] [int] NOT NULL ,
	[createTime] [int] NULL ,
	[author] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[email] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[url] [varchar] (128) COLLATE French_CI_AS NULL ,
	[postId] [int] NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[Post] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[title] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[content] [text] COLLATE French_CI_AS NOT NULL ,
	[contentDisplay] [text] COLLATE French_CI_AS NULL ,
	[tags] [text] COLLATE French_CI_AS NULL ,
	[status] [int] NOT NULL ,
	[createTime] [int] NULL ,
	[updateTime] [int] NULL ,
	[commentCount] [int] NULL ,
	[authorId] [int] NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE TABLE [dbo].[PostTag] (
	[postId] [int] NOT NULL ,
	[tagId] [int] NOT NULL
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[Tag] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[name] [varchar] (128) COLLATE French_CI_AS NOT NULL
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[User] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[username] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[password] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[email] [varchar] (128) COLLATE French_CI_AS NOT NULL ,
	[profile] [text] COLLATE French_CI_AS NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

ALTER TABLE [dbo].[Comment] WITH NOCHECK ADD
	CONSTRAINT [PK_Comment] PRIMARY KEY  CLUSTERED
	(
		[id]
	)  ON [PRIMARY]
GO

ALTER TABLE [dbo].[Post] WITH NOCHECK ADD
	CONSTRAINT [PK_Post] PRIMARY KEY  CLUSTERED
	(
		[id]
	)  ON [PRIMARY]
GO

ALTER TABLE [dbo].[PostTag] WITH NOCHECK ADD
	CONSTRAINT [PK_PostTag] PRIMARY KEY  CLUSTERED
	(
		[postId],
		[tagId]
	)  ON [PRIMARY]
GO

ALTER TABLE [dbo].[Tag] WITH NOCHECK ADD
	CONSTRAINT [PK_Tag] PRIMARY KEY  CLUSTERED
	(
		[id]
	)  ON [PRIMARY]
GO

ALTER TABLE [dbo].[User] WITH NOCHECK ADD
	CONSTRAINT [PK_User] PRIMARY KEY  CLUSTERED
	(
		[id]
	)  ON [PRIMARY]
GO

ALTER TABLE [dbo].[Comment] ADD
	CONSTRAINT [FK_Comment_Post] FOREIGN KEY
	(
		[postId]
	) REFERENCES [dbo].[Post] (
		[id]
	) ON DELETE CASCADE
GO

ALTER TABLE [dbo].[Post] ADD
	CONSTRAINT [FK_Post_User] FOREIGN KEY
	(
		[authorId]
	) REFERENCES [dbo].[User] (
		[id]
	) ON DELETE CASCADE
GO

ALTER TABLE [dbo].[PostTag] ADD
	CONSTRAINT [FK_PostTag_Post] FOREIGN KEY
	(
		[postId]
	) REFERENCES [dbo].[Post] (
		[id]
	) ON DELETE CASCADE ,
	CONSTRAINT [FK_PostTag_Tag] FOREIGN KEY
	(
		[tagId]
	) REFERENCES [dbo].[Tag] (
		[id]
	) ON DELETE CASCADE
GO

INSERT INTO [dbo].[User] (username, password, email) VALUES ('demo','fe01ce2a7fbac8fafaed7c982a04e229','webmaster@example.com');
GO

INSERT INTO [dbo].[Post] (title, content, contentDisplay, status, createTime, updateTime, authorId, tags) VALUES ('Welcome to Yii Blog','This blog system is developed using Yii. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.

Feel free to try this system by writing new posts and posting comments.','<p>This blog system is developed using Yii. It is meant to demonstrate how to use Yii to build a complete real-world application. Complete source code may be found in the Yii releases.</p>

<p>Feel free to try this system by writing new posts and posting comments.</p>',1,1230952187,1230952187,1,'yii, blog');

GO


INSERT INTO [dbo].[Tag] (name) VALUES ('yii');
GO

INSERT INTO [dbo].[Tag] (name) VALUES ('blog');
GO

INSERT INTO [dbo].[PostTag] (postId, tagId) VALUES (1,1);
GO
INSERT INTO [dbo].[PostTag] (postId, tagId) VALUES (1,2);
GO
