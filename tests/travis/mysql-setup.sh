#!/bin/sh

mysql -u root -e 'CREATE SCHEMA `yii` CHARACTER SET utf8 COLLATE utf8_general_ci; GRANT ALL ON `yii`.* TO test@localhost IDENTIFIED BY "test"; FLUSH PRIVILEGES;'
