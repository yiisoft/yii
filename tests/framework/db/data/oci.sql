-- Remove all existing foreign keys in 'profiles' table.
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'profiles';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'ALTER TABLE "profiles" DROP CONSTRAINT "fk_user_id"';
	END IF;
END;
--SEPARATOR--
-- ---------------------
-- Create 'users' table.
-- ---------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'users';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "users"';
	END IF;
END;
--SEPARATOR--
CREATE TABLE "users" (
	"id" INTEGER NOT NULL,
	"username" VARCHAR2(128 CHAR) DEFAULT '' NOT NULL,
	"password" VARCHAR2(128 CHAR) DEFAULT '' NOT NULL,
	"email" VARCHAR2(128 CHAR) DEFAULT '' NOT NULL
) LOGGING NOCOMPRESS NOCACHE
--SEPARATOR--
COMMENT ON COLUMN "users"."id" IS 'User''s entry primary key'
--SEPARATOR--
COMMENT ON COLUMN "users"."username" IS 'Имя пользователя'
--SEPARATOR--
COMMENT ON COLUMN "users"."password" IS '用户的密码'
--SEPARATOR--
COMMENT ON COLUMN "users"."email" IS 'דוא"ל של המשתמש'
--SEPARATOR--
ALTER TABLE "users" ADD CHECK ("id" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "users" ADD CHECK ("username" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "users" ADD CHECK ("password" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "users" ADD CHECK ("email" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "users" ADD PRIMARY KEY ("id")
--SEPARATOR--
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_sequences WHERE sequence_name = 'users_id_sequence';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP SEQUENCE "users_id_sequence"';
	END IF;
END;
--SEPARATOR--
CREATE SEQUENCE "users_id_sequence"
START WITH 1
INCREMENT BY 1
NOMAXVALUE
--SEPARATOR--
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_triggers WHERE trigger_name = 'users_id_trigger';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TRIGGER "users_id_trigger"';
	END IF;
END;
--SEPARATOR--
CREATE TRIGGER "users_id_trigger"
BEFORE INSERT ON "users"
FOR EACH ROW BEGIN
	SELECT "users_id_sequence".nextval INTO :new."id" FROM dual;
END;
--SEPARATOR--
INSERT INTO "users" ("username", "password", "email") VALUES ('user1', 'pass1', 'email1')
--SEPARATOR--
INSERT INTO "users" ("username", "password", "email") VALUES ('user2', 'pass2', 'email2')
--SEPARATOR--
INSERT INTO "users" ("username", "password", "email") VALUES ('user3', 'pass3', 'email3')
--SEPARATOR--
INSERT INTO "users" ("username", "password", "email") VALUES ('пользователь4', '密码4', 'דוא"ל4')
--SEPARATOR--
-- ------------------------
-- Create 'profiles' table.
-- ------------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'profiles';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "profiles"';
	END IF;
END;
--SEPARATOR--
CREATE TABLE "profiles" (
	"id" NUMBER NOT NULL,
	"first_name" VARCHAR2(128 CHAR) DEFAULT '' NOT NULL,
	"last_name" VARCHAR2(128 CHAR) DEFAULT '' NOT NULL,
	"user_id" NUMBER NOT NULL
) LOGGING NOCOMPRESS NOCACHE
--SEPARATOR--
ALTER TABLE "profiles" ADD CHECK ("id" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "profiles" ADD CHECK ("first_name" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "profiles" ADD CHECK ("last_name" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "profiles" ADD CHECK ("user_id" IS NOT NULL)
--SEPARATOR--
ALTER TABLE "profiles" ADD PRIMARY KEY ("id")
--SEPARATOR--
ALTER TABLE "profiles" ADD CONSTRAINT "fk_user_id" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON DELETE CASCADE
--SEPARATOR--
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_sequences WHERE sequence_name = 'profiles_id_sequence';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP SEQUENCE "profiles_id_sequence"';
	END IF;
END;
--SEPARATOR--
CREATE SEQUENCE "profiles_id_sequence"
START WITH 1
INCREMENT BY 1
NOMAXVALUE
--SEPARATOR--
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_triggers WHERE trigger_name = 'profiles_id_trigger';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TRIGGER "profiles_id_trigger"';
	END IF;
END;
--SEPARATOR--
CREATE TRIGGER "profiles_id_trigger"
BEFORE INSERT ON "profiles"
FOR EACH ROW BEGIN
	SELECT "profiles_id_sequence".nextval INTO :new."id" FROM dual;
END;
--SEPARATOR--
INSERT INTO "profiles" ("first_name", "last_name", "user_id") VALUES ('first 1', 'last 1', 1)
--SEPARATOR--
INSERT INTO "profiles" ("first_name", "last_name", "user_id") VALUES ('first 2', 'last 2', 2)
--SEPARATOR--
-- ---------------------
-- Create 'posts' table.
-- ---------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'posts';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "posts"';
	END IF;
END;
--SEPARATOR--
-- ------------------------
-- Create 'comments' table.
-- ------------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'comments';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "comments"';
	END IF;
END;
--SEPARATOR--
-- --------------------------
-- Create 'categories' table.
-- --------------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'categories';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "categories"';
	END IF;
END;
--SEPARATOR--
-- -----------------------------
-- Create 'post_category' table.
-- -----------------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'post_category';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "post_category"';
	END IF;
END;
--SEPARATOR--
-- ----------------------
-- Create 'orders' table.
-- ----------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'orders';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "orders"';
	END IF;
END;
--SEPARATOR--
-- ---------------------
-- Create 'items' table.
-- ---------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'items';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "items"';
	END IF;
END;
--SEPARATOR--
-- ---------------------
-- Create 'types' table.
-- ---------------------
DECLARE
	c INT;
BEGIN
	SELECT COUNT(*) INTO c FROM user_tables WHERE table_name = 'types';
	IF c = 1 THEN
		EXECUTE IMMEDIATE 'DROP TABLE "types"';
	END IF;
END;
