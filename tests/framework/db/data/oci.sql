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
	"id" NUMBER NOT NULL,
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
