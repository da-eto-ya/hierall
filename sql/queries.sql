------------------------------------------------------------------------------
--  Select root nodes
--
EXPLAIN ANALYZE SELECT *
                FROM catalogues
                WHERE parent_id IS NULL;
-- Index Scan using catalogues_parent_id_index on catalogues  (cost=0.42..4.46 rows=1 width=22) (actual time=0.041..0.062 rows=9 loops=1)
--   Index Cond: (parent_id IS NULL)
-- Total runtime: 0.083 ms
SELECT *
FROM catalogues
WHERE parent_id IS NULL;
-- 4ms (execution: 3ms, fetching: 1ms)

------------------------------------------------------------------------------
-- Select some children nodes
--
EXPLAIN ANALYZE SELECT *
                FROM catalogues
                WHERE parent_id = 1760731;
-- Index Scan using catalogues_parent_id_index on catalogues  (cost=0.42..10.17 rows=49 width=22) (actual time=0.025..0.262 rows=370 loops=1)
--   Index Cond: (parent_id = 1760731)
-- Total runtime: 0.289 ms
SELECT *
FROM catalogues
WHERE parent_id = 1760731;
-- 6ms (execution: 3ms, fetching: 3ms)

------------------------------------------------------------------------------
-- Select names like given
--
EXPLAIN ANALYZE SELECT *
                FROM catalogues
                WHERE name ILIKE '%lin%';
-- Bitmap Heap Scan on catalogues  (cost=113.57..7268.92 rows=9493 width=22) (actual time=2.195..14.130 rows=11359 loops=1)
--   Recheck Cond: ((name)::text ~~* '%lin%'::text)
--   ->  Bitmap Index Scan on catalogues_name_trgm_gin  (cost=0.00..111.20 rows=9493 width=0) (actual time=1.699..1.699 rows=11359 loops=1)
--         Index Cond: ((name)::text ~~* '%lin%'::text)
-- Total runtime: 14.452 ms
SELECT *
FROM catalogues
WHERE name ILIKE '%lin%'
LIMIT ALL;
-- 21ms (execution: 5ms, fetching: 16ms)

------------------------------------------------------------------------------
-- Select parent
--
EXPLAIN ANALYZE SELECT p.*
                FROM catalogues AS c JOIN catalogues AS p ON (c.parent_id = p.id)
                WHERE c.id = 1763638
                LIMIT 1;
-- Limit  (cost=0.85..16.90 rows=1 width=22) (actual time=0.008..0.008 rows=1 loops=1)
--   ->  Nested Loop  (cost=0.85..16.90 rows=1 width=22) (actual time=0.008..0.008 rows=1 loops=1)
--         ->  Index Scan using catalogues_pkey on catalogues c  (cost=0.42..8.44 rows=1 width=4) (actual time=0.006..0.006 rows=1 loops=1)
--               Index Cond: (id = 1763638)
--         ->  Index Scan using catalogues_pkey on catalogues p  (cost=0.42..8.45 rows=1 width=22) (actual time=0.002..0.002 rows=1 loops=1)
--               Index Cond: (id = c.parent_id)
-- Total runtime: 0.023 ms
SELECT p.*
FROM catalogues AS c JOIN catalogues AS p ON (c.parent_id = p.id)
WHERE c.id = 1763638
LIMIT 1;
-- 5ms (execution: 3ms, fetching: 2ms)

------------------------------------------------------------------------------
-- Select all subtree
--
EXPLAIN ANALYZE WITH RECURSIVE bfs AS (
  SELECT
    cat.id,
    cat.name,
    cat.parent_id,
    0 AS level
  FROM catalogues AS cat
  WHERE cat.id = 2693993

  UNION ALL
  SELECT
    c.id,
    c.name,
    p.id        AS parent_id,
    p.level + 1 AS level
  FROM catalogues AS c JOIN bfs AS p ON (c.parent_id = p.id)
)
SELECT *
FROM bfs;
-- CTE Scan on bfs  (cost=1286.77..1411.19 rows=6221 width=528) (actual time=0.016..47.010 rows=23430 loops=1)
--   CTE bfs
--     ->  Recursive Union  (cost=0.42..1286.77 rows=6221 width=26) (actual time=0.014..39.862 rows=23430 loops=1)
--           ->  Index Scan using catalogues_pkey on catalogues cat  (cost=0.42..8.44 rows=1 width=22) (actual time=0.013..0.014 rows=1 loops=1)
--                 Index Cond: (id = 2693993)
--           ->  Nested Loop  (cost=0.42..115.39 rows=622 width=26) (actual time=0.017..3.260 rows=2130 loops=11)
--                 ->  WorkTable Scan on bfs p  (cost=0.00..0.20 rows=10 width=8) (actual time=0.000..0.139 rows=2130 loops=11)
--                 ->  Index Scan using catalogues_parent_id_index on catalogues c  (cost=0.42..10.74 rows=62 width=22) (actual time=0.001..0.001 rows=1 loops=23430)
--                       Index Cond: (parent_id = p.id)
-- Total runtime: 48.203 ms
WITH RECURSIVE bfs AS (
  SELECT
    cat.id,
    cat.name,
    cat.parent_id,
    0 AS level
  FROM catalogues AS cat
  WHERE cat.id = 2693993

  UNION ALL
  SELECT
    c.id,
    c.name,
    p.id        AS parent_id,
    p.level + 1 AS level
  FROM catalogues AS c JOIN bfs AS p ON (c.parent_id = p.id)
)
SELECT *
FROM bfs;
-- 9ms (execution: 3ms, fetching: 6ms)

------------------------------------------------------------------------------
-- Select subtree with given depth
--
EXPLAIN ANALYZE WITH RECURSIVE bfs AS (
  SELECT
    cat.id,
    cat.name,
    cat.parent_id,
    0 AS level
  FROM catalogues AS cat
  WHERE cat.id = 2693993

  UNION ALL
  SELECT
    c.id,
    c.name,
    p.id        AS parent_id,
    p.level + 1 AS level
  FROM catalogues AS c JOIN bfs AS p ON c.parent_id = p.id
  WHERE p.level < 5
)
SELECT *
FROM bfs;
-- CTE Scan on bfs  (cost=394.60..431.82 rows=1861 width=528) (actual time=0.011..29.813 rows=19901 loops=1)
--   CTE bfs
--     ->  Recursive Union  (cost=0.42..394.60 rows=1861 width=26) (actual time=0.010..24.453 rows=19901 loops=1)
--           ->  Index Scan using catalogues_pkey on catalogues cat  (cost=0.42..8.44 rows=1 width=22) (actual time=0.009..0.009 rows=1 loops=1)
--                 Index Cond: (id = 2693993)
--     ->  Nested Loop  (cost=0.42..34.89 rows=186 width=26) (actual time=0.095..3.617 rows=3317 loops=6)
--           ->  WorkTable Scan on bfs p  (cost=0.00..0.22 rows=3 width=8) (actual time=0.080..0.319 rows=2161 loops=6)
--                 Filter: (level < 5)
--                 Rows Removed by Filter: 1156
--           ->  Index Scan using catalogues_parent_id_index on catalogues c  (cost=0.42..10.78 rows=62 width=22) (actual time=0.001..0.001 rows=2 loops=12965)
--                 Index Cond: (parent_id = p.id)
-- Total runtime: 30.664 ms
------------------------------------------------------------------------------
-- Select subtree with given depth
--
WITH RECURSIVE bfs AS (
  SELECT
    cat.id,
    cat.name,
    cat.parent_id,
    0 AS level
  FROM catalogues AS cat
  WHERE cat.id = 2693993

  UNION ALL
  SELECT
    c.id,
    c.name,
    p.id        AS parent_id,
    p.level + 1 AS level
  FROM catalogues AS c JOIN bfs AS p ON c.parent_id = p.id
  WHERE p.level < 5
)
SELECT *
FROM bfs;
-- 10ms (execution: 4ms, fetching: 6ms)

