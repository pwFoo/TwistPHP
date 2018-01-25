-- ------------------------------------------------------
--
-- All SQL updates that have been made to the default tables
-- since the last version should be placed in here. All queries
-- are run in the order that they appear in this file.
--
-- Changes to tables      add an ALTER query
-- New tables             add a CREATE query
-- Removal of tables      add a DROP query
-- Additional records     add an INSERT query
-- Removal of records     add a DELETE query
-- Updating of records    add a UPDATE query
--
-- Any changes that are made by queries in this file
-- must also be made to the corresponding install.sql
--
-- To Add the Twist table prefix you must use the following
-- syntax /*TWIST_DATABASE_TABLE_PREFIX*/`table_name`
--
-- You can use a single line comment above each query using
-- the following syntax "-- @comment This is my query comment"
--
-- ------------------------------------------------------

-- @comment Store the GDPR data locations
CREATE TABLE IF NOT EXISTS /*TWIST_DATABASE_TABLE_PREFIX*/`gdpr_locations` (
  `table` char(128) NOT NULL,
  `usage` text COLLATE utf8_unicode_ci,
  `portable` int(11) NOT NULL,
  `locked` int(11) NOT NULL,
  `autodetected` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  `fields` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `table` (`table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;