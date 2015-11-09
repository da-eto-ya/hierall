DROP TABLE IF EXISTS catalogues;
DROP SEQUENCE IF EXISTS catalogues_id_seq;
CREATE SEQUENCE catalogues_id_seq;
CREATE TABLE catalogues (
  id        INTEGER NOT NULL DEFAULT nextval('catalogues_id_seq' :: REGCLASS),
  name      VARCHAR(255)     DEFAULT '' :: CHARACTER VARYING,
  parent_id INTEGER,
  PRIMARY KEY (id),
  FOREIGN KEY (parent_id) REFERENCES catalogues (id) ON DELETE CASCADE
);
