BEGIN;

CREATE TABLE user (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(255)
);

CREATE TABLE group (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(255)
);

CREATE TABLE user_group (
  user_id INTEGER,
  group_id INTEGER,
  role VARCHAR(255)
);

INSERT INTO user (id, username) VALUES(1, 'Alexander');
INSERT INTO user (id, username) VALUES(2, 'Qiang');

INSERT INTO group (id, name) VALUES(1, Yii);
INSERT INTO group (id, name) VALUES(2, Zii);

INSERT INTO user_group (user_id, group_id, role) VALUES(1, 1, 'dev');
INSERT INTO user_group (user_id, group_id, role) VALUES(1, 2, 'user');
INSERT INTO user_group (user_id, group_id, role) VALUES(2, 1, 'dev');

COMMIT;