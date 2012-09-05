#!/bin/sh
 
psql -q -c "CREATE ROLE test WITH PASSWORD 'test' LOGIN;" -U postgres
psql -q -c 'CREATE DATABASE yii WITH OWNER = test;' -U postgres
psql -q -c 'GRANT ALL PRIVILEGES ON DATABASE yii TO test;' -U postgres
