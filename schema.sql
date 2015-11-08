CREATE TABLE catalogues (
  id        INTEGER      NOT NULL DEFAULT nextval('catalogues_id_seq' :: REGCLASS),
  name      VARCHAR(255) NOT NULL DEFAULT '' :: CHARACTER VARYING,
  parent_id INTEGER,
  PRIMARY KEY (id),
  FOREIGN KEY (parent_id) REFERENCES catalogues (id)
);
CREATE UNIQUE INDEX catalogues_pkey ON catalogues (id);
