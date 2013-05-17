-- Double EOL symbol (\n\n) used as separator. This means that query string like "{SQL1}\n\n{SQL2}" would
-- cause two queries to the RDBMS: first "{SQL1}" and second "{SQL2}".

-- Create 'users' table.
CREATE TABLE "users" (
	"id" INTEGER NOT NULL,
	"username" VARCHAR2(128 CHAR) NOT NULL,
	"password" VARCHAR2(128 CHAR) NOT NULL,
	"email" VARCHAR2(128 CHAR) NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

COMMENT ON COLUMN "users"."id" IS 'User''s entry primary key';

COMMENT ON COLUMN "users"."username" IS 'Имя пользователя';

COMMENT ON COLUMN "users"."password" IS '用户的密码';

COMMENT ON COLUMN "users"."email" IS 'דוא"ל של המשתמש';

ALTER TABLE "users" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "users" ADD CHECK ("username" IS NOT NULL);

ALTER TABLE "users" ADD CHECK ("password" IS NOT NULL);

ALTER TABLE "users" ADD CHECK ("email" IS NOT NULL);

ALTER TABLE "users" ADD PRIMARY KEY ("id");

CREATE SEQUENCE "users_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "users_id_trigger"
BEFORE INSERT ON "users"
FOR EACH ROW BEGIN
	SELECT "users_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'profiles' table.
CREATE TABLE "profiles" (
	"id" INTEGER NOT NULL,
	"first_name" VARCHAR2(128 CHAR) NOT NULL,
	"last_name" VARCHAR2(128 CHAR) NOT NULL,
	"user_id" INTEGER NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "profiles" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "profiles" ADD CHECK ("first_name" IS NOT NULL);

ALTER TABLE "profiles" ADD CHECK ("last_name" IS NOT NULL);

ALTER TABLE "profiles" ADD CHECK ("user_id" IS NOT NULL);

ALTER TABLE "profiles" ADD PRIMARY KEY ("id");

ALTER TABLE "profiles" ADD FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE;

CREATE SEQUENCE "profiles_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "profiles_id_trigger"
BEFORE INSERT ON "profiles"
FOR EACH ROW BEGIN
	SELECT "profiles_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'posts' table.
CREATE TABLE "posts" (
	"id" INTEGER NOT NULL,
	"title" VARCHAR2(128 CHAR) NOT NULL,
	"create_time" TIMESTAMP NOT NULL,
	"author_id" INTEGER NOT NULL,
	"content" CLOB
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "posts" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "posts" ADD CHECK ("title" IS NOT NULL);

ALTER TABLE "posts" ADD CHECK ("create_time" IS NOT NULL);

ALTER TABLE "posts" ADD CHECK ("author_id" IS NOT NULL);

ALTER TABLE "posts" ADD PRIMARY KEY ("id");

ALTER TABLE "posts" ADD FOREIGN KEY ("author_id") REFERENCES "users" ("id") ON DELETE CASCADE;

CREATE SEQUENCE "posts_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "posts_id_trigger"
BEFORE INSERT ON "posts"
FOR EACH ROW BEGIN
	SELECT "posts_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'comments' table.
CREATE TABLE "comments" (
	"id" INTEGER NOT NULL,
	"content" CLOB,
	"post_id" INTEGER NOT NULL,
	"author_id" INTEGER NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "comments" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "comments" ADD CHECK ("post_id" IS NOT NULL);

ALTER TABLE "comments" ADD CHECK ("author_id" IS NOT NULL);

ALTER TABLE "comments" ADD PRIMARY KEY ("id");

ALTER TABLE "comments" ADD FOREIGN KEY ("post_id") REFERENCES "posts" ("id") ON DELETE CASCADE;

ALTER TABLE "comments" ADD FOREIGN KEY ("author_id") REFERENCES "users" ("id") ON DELETE CASCADE;

CREATE SEQUENCE "comments_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "comments_id_trigger"
BEFORE INSERT ON "comments"
FOR EACH ROW BEGIN
	SELECT "comments_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'categories' table.
CREATE TABLE "categories" (
	"id" INTEGER NOT NULL,
	"name" VARCHAR2(128 CHAR) NOT NULL,
	"parent_id" INTEGER
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "categories" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "categories" ADD CHECK ("name" IS NOT NULL);

ALTER TABLE "categories" ADD PRIMARY KEY ("id");

ALTER TABLE "categories" ADD FOREIGN KEY ("parent_id") REFERENCES "categories" ("id") ON DELETE CASCADE;

CREATE SEQUENCE "categories_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "categories_id_trigger"
BEFORE INSERT ON "categories"
FOR EACH ROW BEGIN
	SELECT "categories_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'post_category' table.
CREATE TABLE "post_category" (
	"category_id" INTEGER NOT NULL,
	"post_id" INTEGER NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "post_category" ADD CHECK ("category_id" IS NOT NULL);

ALTER TABLE "post_category" ADD CHECK ("post_id" IS NOT NULL);

ALTER TABLE "post_category" ADD PRIMARY KEY ("category_id", "post_id");

ALTER TABLE "post_category" ADD FOREIGN KEY ("category_id") REFERENCES "posts" ("id") ON DELETE CASCADE;

ALTER TABLE "post_category" ADD FOREIGN KEY ("post_id") REFERENCES "categories" ("id") ON DELETE CASCADE;

-- Create 'orders' table.
CREATE TABLE "orders" (
	"key1" INTEGER NOT NULL,
	"key2" INTEGER NOT NULL,
	"name" VARCHAR2(128 CHAR) NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "orders" ADD CHECK ("key1" IS NOT NULL);

ALTER TABLE "orders" ADD CHECK ("key2" IS NOT NULL);

ALTER TABLE "orders" ADD CHECK ("name" IS NOT NULL);

ALTER TABLE "orders" ADD PRIMARY KEY ("key1", "key2");

-- Create 'items' table.
CREATE TABLE "items" (
	"id" INTEGER NOT NULL,
	"name" VARCHAR2(128 CHAR) NOT NULL,
	"col1" INTEGER NOT NULL,
	"col2" INTEGER NOT NULL
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "items" ADD CHECK ("id" IS NOT NULL);

ALTER TABLE "items" ADD CHECK ("name" IS NOT NULL);

ALTER TABLE "items" ADD CHECK ("col1" IS NOT NULL);

ALTER TABLE "items" ADD CHECK ("col2" IS NOT NULL);

ALTER TABLE "items" ADD PRIMARY KEY ("id");

ALTER TABLE "items" ADD FOREIGN KEY ("col1", "col2") REFERENCES "orders" ("key1", "key2") ON DELETE CASCADE;

CREATE SEQUENCE "items_id_sequence" START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "items_id_trigger"
BEFORE INSERT ON "items"
FOR EACH ROW BEGIN
	SELECT "items_id_sequence".nextval INTO :new."id" FROM dual;
END;

-- Create 'types' table.
CREATE TABLE "types" (
	"int_col" INT NOT NULL,
	"int_col2" INTEGER DEFAULT 1,
	"char_col" CHAR(100) NOT NULL,
	"char_col2" VARCHAR2(100 CHAR) DEFAULT 'something',
	"char_col3" CLOB,
	"float_col" NUMBER(4, 3) NOT NULL,
	"float_col2" BINARY_DOUBLE DEFAULT 1.23,
	"blob_col" BLOB,
	"numeric_col" NUMBER(5, 2) DEFAULT 33.22,
	"time" TIMESTAMP DEFAULT TO_TIMESTAMP('2010-01-01 00:00:00','YYYY-MM-DD HH24:MI:SS'),
	"bool_col" NUMBER(1, 0) NOT NULL,
	"bool_col2" NUMBER(1, 0) DEFAULT 1
) LOGGING NOCOMPRESS NOCACHE;

ALTER TABLE "types" ADD CHECK ("int_col" IS NOT NULL);

ALTER TABLE "types" ADD CHECK ("char_col" IS NOT NULL);

ALTER TABLE "types" ADD CHECK ("float_col" IS NOT NULL);

ALTER TABLE "types" ADD CHECK ("bool_col" IS NOT NULL);

-- Data for the 'users' table.
INSERT INTO "users" ("username", "password", "email") VALUES ('user1', 'pass1', 'email1');

INSERT INTO "users" ("username", "password", "email") VALUES ('user2', 'pass2', 'email2');

INSERT INTO "users" ("username", "password", "email") VALUES ('user3', 'pass3', 'email3');

INSERT INTO "users" ("username", "password", "email") VALUES ('пользователь4', '密码4', 'דוא"ל4');

-- Data for the 'profiles' table.
INSERT INTO "profiles" ("first_name", "last_name", "user_id") VALUES ('first 1', 'last 1', 1);

INSERT INTO "profiles" ("first_name", "last_name", "user_id") VALUES ('first 2', 'last 2', 2);

-- Data for the 'posts' table.
INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES ('post 1', TIMESTAMP '2000-01-01 00:00:00', 1, 'content 1');

INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES ('post 2', TIMESTAMP '2000-01-02 00:00:00', 2, 'content 2');

INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES ('post 3', TIMESTAMP '2000-01-03 00:00:00', 2, 'content 3');

INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES ('post 4', TIMESTAMP '2000-01-04 00:00:00', 2, 'content 4');

INSERT INTO "posts" ("title", "create_time", "author_id", "content") VALUES ('post 5', TIMESTAMP '2000-01-05 00:00:00', 3, 'content 5');

-- Data for the 'comments' table.
INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 1', 1, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 2', 1, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 3', 1, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 4', 2, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 5', 2, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 6', 3, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 7', 3, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 8', 3, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 9', 3, 2);

INSERT INTO "comments" ("content", "post_id", "author_id") VALUES ('comment 10', 5, 3);

-- Data for the 'categories' table.
INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 1', NULL);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 2', NULL);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 3', NULL);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 4', 1);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 5', 1);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 6', 5);

INSERT INTO "categories" ("name", "parent_id") VALUES ('cat 7', 5);

-- Data for the 'post_category' table.
INSERT INTO "post_category" ("category_id", "post_id") VALUES (1, 1);

INSERT INTO "post_category" ("category_id", "post_id") VALUES (2, 1);

INSERT INTO "post_category" ("category_id", "post_id") VALUES (3, 1);

INSERT INTO "post_category" ("category_id", "post_id") VALUES (4, 2);

INSERT INTO "post_category" ("category_id", "post_id") VALUES (1, 2);

INSERT INTO "post_category" ("category_id", "post_id") VALUES (1, 3);

-- Data for the 'orders' table.
INSERT INTO "orders" ("key1", "key2", "name") VALUES (1, 2, 'order 12');

INSERT INTO "orders" ("key1", "key2", "name") VALUES (1, 3, 'order 13');

INSERT INTO "orders" ("key1", "key2", "name") VALUES (2, 1, 'order 21');

INSERT INTO "orders" ("key1", "key2", "name") VALUES (2, 2, 'order 22');

-- Data for the 'items' table.
INSERT INTO "items" ("name", "col1", "col2") VALUES ('item 1', 1, 2);

INSERT INTO "items" ("name", "col1", "col2") VALUES ('item 2', 1, 2);

INSERT INTO "items" ("name", "col1", "col2") VALUES ('item 3', 1, 3);

INSERT INTO "items" ("name", "col1", "col2") VALUES ('item 4', 2, 2);

INSERT INTO "items" ("name", "col1", "col2") VALUES ('item 5', 2, 2);
