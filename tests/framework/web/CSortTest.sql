CREATE TABLE comment (
  id INTEGER PRIMARY KEY,
  post_id INTEGER,
  text text,
  created_at INTEGER
);

CREATE TABLE post (
  id INTEGER PRIMARY KEY,
  created_at INTEGER,
  title VARCHAR(255)
);