DROP TABLE IF EXISTS system;
-- ---- Create system table:
CREATE TABLE `system` (
  `id` TINYINT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `value` LONGTEXT NOT NULL
);


DROP TABLE IF EXISTS users;
-- ---- Create users table:
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_name-hash` CHAR(32) UNIQUE NOT NULL,
  `password-hash` CHAR(32) NOT NULL,
  `name-field` VARCHAR(30) NOT NULL,
  `email-field` VARCHAR(255) NOT NULL,
  `permissions-set` VARCHAR(255) NOT NULL DEFAULT ''
);


DROP TABLE IF EXISTS categories;
-- ---- Create categories table:
CREATE TABLE `categories` (
	`id` TINYINT PRIMARY KEY AUTO_INCREMENT,
 	`title-field` VARCHAR(100) NOT NULL,
 	`title_mung-field` VARCHAR(25) NOT NULL,
 	`publish_flag-checkbox` BOOL DEFAULT TRUE
);

-- ---- Insert default category into Categories table:
INSERT INTO `categories` VALUES (1, 'Uncategorized', 'uncategorized', TRUE);


DROP TABLE IF EXISTS content;
-- ---- Create content table:
CREATE TABLE `content` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `content-title-field` VARCHAR(100) NOT NULL,
  `content-title_mung-field` VARCHAR(25) NOT NULL,
  `content-static_page_flag-checkbox` BOOL DEFAULT FALSE,
  `content-category-list` INT NOT NULL DEFAULT 1,
  `content-author-list` INT NOT NULL DEFAULT 1,
  `content-date-date` BIGINT(14) UNSIGNED UNIQUE NOT NULL,
  `content-body-textarea` LONGTEXT,
  `meta-description-field` VARCHAR(255) DEFAULT NULL,
  `meta-keywords-field` VARCHAR(255) DEFAULT NULL,
  `publish-publish_flag-checkbox` BOOL DEFAULT TRUE,
  `publish-publish_date-date` BIGINT UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT UNSIGNED NOT NULL,
  FULLTEXT KEY `content` (`content-title-field`,`content-body-textarea`,`meta-description-field`,`meta-keywords-field`)
);


DROP TABLE IF EXISTS views;
-- ---- Create views table:
CREATE TABLE `views` (
	`id` TINYINT PRIMARY KEY AUTO_INCREMENT,
 	`title-field` VARCHAR(100) UNIQUE NOT NULL,
 	`title_mung-field` VARCHAR(25) UNIQUE NOT NULL,
 	`publish_flag-checkbox` BOOL DEFAULT TRUE,
);


DROP TABLE IF EXISTS blocks;
-- ---- Create blocks table:
CREATE TABLE `blocks` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `content-title-field` VARCHAR(100) NOT NULL,
  `content-views-list` INT NOT NULL DEFAULT 1,  
  `content-body-textarea` LONGTEXT,
  `publish-publish_flag-checkbox` BOOL DEFAULT TRUE,
  `publish-publish_date-date` BIGINT(14) UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT(14) UNSIGNED NOT NULL
);

