BEGIN;

CREATE TABLE tbl_user (
  id INTEGER PRIMARY KEY,
  username VARCHAR(255)
);

CREATE TABLE tbl_group (
  id INTEGER PRIMARY KEY,
  name VARCHAR(255)
);

CREATE TABLE tbl_user_group (
  user_id INTEGER,
  group_id INTEGER,
  role VARCHAR(255)
);

INSERT INTO tbl_user (id, username) VALUES(1, 'Alexander');
INSERT INTO tbl_user (id, username) VALUES(2, 'Qiang');

INSERT INTO tbl_group (id, name) VALUES(1, 'Yii');
INSERT INTO tbl_group (id, name) VALUES(2, 'Zii');

INSERT INTO tbl_user_group (user_id, group_id, role) VALUES(1, 1, 'dev');
INSERT INTO tbl_user_group (user_id, group_id, role) VALUES(1, 2, 'user');
INSERT INTO tbl_user_group (user_id, group_id, role) VALUES(2, 1, 'dev');

COMMIT;