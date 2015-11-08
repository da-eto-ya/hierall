-- schema for testing
DROP TABLE IF EXISTS test;
CREATE TABLE test
(
  id   SERIAL PRIMARY KEY,
  name VARCHAR(64) DEFAULT '' NOT NULL
);
