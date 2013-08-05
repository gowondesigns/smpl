/*
    Database fields should be in the following format:

    id -OR-
    item_name-item_type -OR-
    item_group-item_name-item_type
*/

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

-- ---- Insert default category into Categories table:
INSERT INTO `categories` VALUES (1, 'Uncategorized', 'uncategorized', TRUE);


DROP TABLE IF EXISTS content;
-- ---- Create content table:
CREATE TABLE `content` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `content-title-field` VARCHAR(100) NOT NULL,
  `content-title_mung-field` VARCHAR(25) UNIQUE NOT NULL,
  `content-static_page_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `content-in_category_flag-checkbox` BOOL NOT NULL DEFAULT TRUE,
  `content-default_page_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `content-category-dropdown` INT NOT NULL DEFAULT 1,
  `content-author-dropdown` INT NOT NULL DEFAULT 1,
  `content-date-date` BIGINT(14) UNSIGNED UNIQUE NOT NULL,
  `content-body-textarea` LONGTEXT DEFAULT NULL,
  `content-tags-field` VARCHAR(255) DEFAULT NULL,
  `publish-publish_flag-dropdown` ENUM('NOTPUBLISHED', 'PUBLISHED', 'TOPUBLISH') NOT NULL DEFAULT 2,
  `publish-publish_date-date` BIGINT UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT UNSIGNED NOT NULL,
  FULLTEXT KEY `content` (`content-title-field`,`content-body-textarea`,`meta-description-field`,`meta-keywords-field`)
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
  `content-space-dropdown` INT NOT NULL DEFAULT 1,
  `content-priority-dropdown` ENUM('LOW', 'MED', 'HIGH') NOT NULL DEFAULT 1,
  `content-redirect_flag-checkbox` BOOL NOT NULL DEFAULT TRUE,
  `content-redirect_location-field` VARCHAR(255) DEFAULT NULL,    
  `content-body-textarea` LONGTEXT DEFAULT NULL,
  `publish-publish_flag-dropdown` ENUM('NOTPUBLISHED', 'PUBLISHED', 'TOPUBLISH') NOT NULL DEFAULT 2,
  `publish-publish_date-date` BIGINT(14) UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT(14) UNSIGNED NOT NULL
);
