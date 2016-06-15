#!/bin/sh
 
psql -q -c "CREATE ROLE test WITH PASSWORD 'test' LOGIN;" -U postgres
psql -q -c 'CREATE DATABASE yee WITH OWNER = test;' -U postgres
psql -q -c 'GRANT ALL PRIVILEGES ON DATABASE yee TO test;' -U postgres
