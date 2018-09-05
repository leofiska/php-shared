\set var_database db_fads
\set var_user u_fads

\c postgres

UPDATE pg_database SET datallowconn = 'false' WHERE datname = ':var_database';

SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = ':var_database';

SELECT pg_terminate_backend(pg_stat_activity.pid)
FROM pg_stat_activity
WHERE datname = 'db_fads'
AND pid <> pg_backend_pid();

DROP DATABASE IF EXISTS :var_database;
DROP USER IF EXISTS :var_user;
CREATE USER :var_user WITH PASSWORD 'JAhkjgdY3jhkGHKgkgkhjGTU34536FHGFug';

CREATE DATABASE :var_database
  WITH OWNER :var_user
  ENCODING 'UTF8'
  TABLESPACE = pg_default
  LC_COLLATE = 'en_US.UTF-8'
  LC_CTYPE = 'en_US.UTF-8'
  TEMPLATE template0;

\c :var_database;

