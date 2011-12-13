Overall Design
==============

Based on the analysis of the requirements, we decide to use the following database tables to store the persistent data for our blog application:

 * `tbl_user` stores the user information, including username and password.
 * `tbl_post` stores the blog post information. It mainly consists of the following columns:
	 - `title`: required, title of the post;
	 - `content`: required, body content of the post which uses the [Markdown format](http://daringfireball.net/projects/markdown/syntax);
	 - `status`: required, status of the post, which can be one of following values:
		 * 1, meaning the post is in draft and is not visible to public;
		 * 2, meaning the post is published to public;
		 * 3, meaning the post is outdated and is not visible in the post list (still accessible individually, though).
	 - `tags`: optional, a list of comma-separated words categorizing the post.
 * `tbl_comment` stores the post comment information. Each comment is associated with a post and mainly consists of the following columns:
	 - `author`: required, the author name;
	 - `email`: required, the author email;
	 - `url`: optional, the author website URL;
	 - `content`: required, the comment content in plain text format.
	 - `status`: required, status of the comment, which indicates whether the comment is approved (value 2) or not (value 1).
 * `tbl_tag` stores post tag frequency information that is needed to implement the tag cloud feature. The table mainly contains the following columns:
 	 - `name`: required, the unique tag name;
 	 - `frequency`: required, the number of times that the tag appears in posts.
 * `tbl_lookup` stores generic lookup information. It is essentially a map between integer values and text strings. The former is the data representation in our code, while the latter is the corresponding presentation to end users. For example, we use integer 1 to represent the draft post status and string `Draft` to display this status to end users. This table mainly contains the following columns:
 	 - `name`: the textual representation of the data item that is to be displayed to end users;
 	 - `code`: the integer representation of the data item;
 	 - `type`: the type of the data item;
 	 - `position`: the relative display order of the data item among other items of the same type.


The following entity-relation (ER) diagram shows the table structure and relationships about the above tables.

![Entity-Relation Diagram of the Blog Database](schema.png)


Complete SQL statements corresponding to the above ER diagram may be found in [the blog demo](http://www.yiiframework.com/demos/blog/). In our Yii installation, they are in the file `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.



> Info: We name all our table names and column names in lower case. This is because different DBMS often have different case-sensitivity treatment and we want to avoid troubles like this.
>
> We also prefix all our tables with `tbl_`. This serves for two purposes. First, the prefix introduces a namespace to these tables in case when they need to coexist with other tables in the same database, which often happens in a shared hosting environment where a single database is being used by multiple applications. Second, using table prefix reduces the possibility of having some table names that are reserved keywords in DBMS.


We divide the development of our blog application into the following milestones.

 * Milestone 1: creating a prototype of the blog system. It should consist of most of the required functionalities.
 * Milestone 2: completing post management. It includes creating, listing, showing, updating and deleting posts.
 * Milestone 3: completing comment management. It includes creating, listing, approving, updating and deleting post comments.
 * Milestone 4: implementing portlets. It includes user menu, login, tag cloud and recent comments portlets.
 * Milestone 5: final tune-up and deployment.

<div class="revision">$Id$</div>