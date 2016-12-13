#!/bin/sh

mysql -u root -e 'CREATE SCHEMA `yee` CHARACTER SET utf8 COLLATE utf8_general_ci; GRANT ALL ON `yee`.* TO test@localhost IDENTIFIED BY "test"; FLUSH PRIVILEGES;'
