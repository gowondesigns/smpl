--    Database fields should be in the following format:
--    id -OR-
--    item_name-item_type -OR-
--    item_group-item_name-item_type


DROP TABLE IF EXISTS settings;
-- ---- Create settings table:
CREATE TABLE `settings` (
  `id` TINYINT PRIMARY KEY AUTO_INCREMENT,
  `name-hidden` VARCHAR(30) UNIQUE NOT NULL,
  `title-label` VARCHAR(255) NOT NULL,
  `value-field` LONGTEXT NOT NULL
);


DROP TABLE IF EXISTS api;
-- ---- Create api table:
CREATE TABLE `api` (
  `id` TINYINT PRIMARY KEY AUTO_INCREMENT,
  `api-token-field` VARCHAR(30) UNIQUE NOT NULL,
  `api-description-field` VARCHAR(255) DEFAULT NULL,
  `api-cnonce-field` VARCHAR(255) NOT NULL,
  `permissions-access_database-checkbox` BOOL NOT NULL DEFAULT FALSE,  -- Give client ability to make custom SQL queries, effectively supercedes all other permissions
  `permissions-access_system-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_users-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_content-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_blocks-checkbox` BOOL NOT NULL DEFAULT FALSE
);


DROP TABLE IF EXISTS users;
-- ---- Create users table:
CREATE TABLE `users` (
  `id` TINYINT PRIMARY KEY AUTO_INCREMENT,
  `account-user_name-hash` CHAR(32) UNIQUE NOT NULL,
  `account-password-hash` CHAR(32) NOT NULL,
  `account-name-field` VARCHAR(30) NOT NULL,
  `account-email-field` VARCHAR(255) NOT NULL,
  `account-last_login-hidden` BIGINT UNSIGNED DEFAULT NULL,
  `permissions-access_system-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_users-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_content-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `permissions-access_blocks-checkbox` BOOL NOT NULL DEFAULT FALSE
);


DROP TABLE IF EXISTS categories;
-- ---- Create categories table:
CREATE TABLE `categories` (
	`id` TINYINT PRIMARY KEY AUTO_INCREMENT,
 	`title-field` VARCHAR(100) NOT NULL,
 	`title_mung-field` VARCHAR(25) UNIQUE NOT NULL,
 	`publish_flag-checkbox` BOOL NOT NULL DEFAULT TRUE
);


DROP TABLE IF EXISTS content;
-- ---- Create content table:
-- IN CATEGORY FLAG affects visibility and URI behavior
-- Articles: If false, Article will not be listed in category index
-- Pages: If true, Page URI default will be without category
CREATE TABLE `content` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `content-title-field` VARCHAR(100) NOT NULL,
  `content-title_mung-field` VARCHAR(50) UNIQUE NOT NULL,
  `content-body-textarea` LONGTEXT DEFAULT NULL,
  `content-tags-field` VARCHAR(255) DEFAULT NULL,
  `meta-author-dropdown` INT NOT NULL DEFAULT 1,
  `meta-category-dropdown` INT NOT NULL DEFAULT 1,
  `meta-date-date` BIGINT(14) UNSIGNED UNIQUE NOT NULL,
  `meta-static_page_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `meta-default_page_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `meta-indexed_flag-checkbox` BOOL NOT NULL DEFAULT TRUE,
  `publish-publish_flag-dropdown` ENUM('NOTPUBLISHED', 'PUBLISHED', 'TOPUBLISH') NOT NULL DEFAULT 'PUBLISHED',
  `publish-publish_date-date` BIGINT UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT UNSIGNED NOT NULL
  -- FULLTEXT (`content-title-field`, `content-body-textarea`, `content-tags-field`)  -- #1214 - The used table type doesn't support FULLTEXT indexes 
);


DROP TABLE IF EXISTS spaces;
-- ---- Create spaces table:
CREATE TABLE `spaces` (
	`id` TINYINT PRIMARY KEY AUTO_INCREMENT,
 	`title-field` VARCHAR(100) UNIQUE NOT NULL,
 	`title_mung-field` VARCHAR(25) UNIQUE NOT NULL,
 	`publish_flag-checkbox` BOOL NOT NULL DEFAULT TRUE
);


DROP TABLE IF EXISTS blocks;
-- ---- Create blocks table:
CREATE TABLE `blocks` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
 	`content-title-field` VARCHAR(100) NOT NULL,
 	`content-title_mung-field` VARCHAR(25) UNIQUE NOT NULL,
  `content-body-textarea` LONGTEXT DEFAULT NULL,
  `meta-space-dropdown` INT NOT NULL DEFAULT 1,
  `meta-priority-dropdown` ENUM('LOW', 'MED', 'HIGH') NOT NULL DEFAULT 'LOW',
  `meta-redirect_flag-checkbox` BOOL NOT NULL DEFAULT TRUE,
  `meta-redirect_location-field` VARCHAR(255) DEFAULT NULL,
  `publish-publish_flag-dropdown` ENUM('NOTPUBLISHED', 'PUBLISHED', 'TOPUBLISH') NOT NULL DEFAULT 'PUBLISHED',
  `publish-publish_date-date` BIGINT(14) UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT(14) UNSIGNED NOT NULL
);
