<?php
/*------------------------------------------------------------------------------
  SMPL Content Management System Language File
  Language:       American (US) English
  Language Code:  en-US
  Last Updated:   July 18, 2013
  Translation by: Gowon Patterson
--------------------------------------------------------------------------------
  Type: language
  Subtag: en
  Description: English
  Added: 2005-10-16
  Suppress-Script: Latn

  Type: region
  Subtag: US
  Description: United States
  Added: 2005-10-16
--------------------------------------------------------------------------------
  More information can be found at:
  http://tools.ietf.org/html/rfc5646
  http://www.iana.org/assignments/language-subtag-registry/language-subtag-registry
------------------------------------------------------------------------------*/

$SMPL_LANG_CODE = 'en-US';
$SMPL_LANG_DESC = 'US English';

$SMPL_LANG_PHRASES = array(
  // SMPL-generated URL phrases
  "api" => "api",
  "feed" => "feed",
  "articles" => "articles",
  //"categories" => "categories",
  
  // Control Panel - General Elements
  "controlPanel" => "Control Panel",
  "welcome" => "Welcome, ",
  "systemSettings" => "System Settings",
  "apiSettings" => "API Settings",
  "users" => "Users",
  "categories" => "Categories",
  "content" => "Content",
  "spaces" => "Spaces",
  "blocks" => "Blocks",
  "createNew" => "Create New",
  "purge" => "Purge",
  
  // Control Panel - Confirm Delete
  "confirmDelete" => "Confirm Deletion",
  "deleteNotice" => "Are you sure you want to delete this?",
  "delete" => "Delete",
  
  // Control Panel - Confirm Purge
  "confirmPurge" => "Purge Content",
  "purgeNotice" => "Delete all content with a <strong>publish date before</strong> selected date:",
  "onlyArticles" => "Only delete Articles",
  
  // Control Panel - Logout Elements
  "logout" => "Logout",
  "logoutNotice" => 'You have successfully logged out. You are being redirected to <a href="login.html">login.html</a>.<br/>If you are not redirected after a few seconds, please click on the link above.',
  
  // Control Panel - Login Elements
  "login" => "Login",
  "loginMsg" => "You are not authorized to access this page. Please log in to continue.",
  "username" => "Username",
  "password" => "Password",
  
  // Control Panel - General Form Elements
  "date" => "Date",
  "time" => "Time",
  "edit" => "Edit",
  "previous" => "Prev",
  "next" => "Next",
  "options" => "Options",
  "title" => "Title",
  "status" => "Status",
  "cancel" => "Cancel",
  "reset" => "Reset",
  "submit" => "Submit",
 
  // Control Panel - API
  "api" => "API",
  "api-token-field" => "API Token",
  "api-description-field" => "Description",
  "api-cnonce-field" => "CNONCE Token",
  "permissions-access_database-checkbox" => "Access to database",
  "permissions-access_system-checkbox" => "Access to system settings",
  "permissions-access_users-checkbox" => "Access to users",
  "permissions-access_content-checkbox" => "Access to content",
  "permissions-access_blocks-checkbox" => "Access to blocks",
  
  // Control Panel - Users
  "user" => "User",
  "account-user_name-hash" => "Username",
  "account-password-hash" => "Password",
  "account-name-field" => "Name",
  "account-email-field" => "Email Address",

  // Control Panel - Categories
  // Control Panel - Spaces
  "category" => "Category",
  "space" => "Space",
  "title-field" => "Title",
 	"title_mung-field" => "Search Engine Friendly Title",
 	"publish_flag-checkbox" => "Published",
  
  // Control Panel - Content
  "content-title-field" => "Title",
  "content-title_mung-field" => "Search Engine Friendly Title",
  "content-static_page_flag-checkbox" => "Content is Static Page",
  "content-default_page_flag-checkbox" => "Content is Default Page",
  "content-category-dropdown" => "Category",
  "content-author-dropdown" => "Author",
  "content-date-date" => "Content Date",
  "content-body-textarea" => "Body",
  "content-tags-field" => "Tags",
  "publish-publish_flag-dropdown" => "Publish Status",
  "publish-publish_date-date" => "Publish Date",            
  "publish-unpublish_flag-checkbox" => "Set Unpublish Date",
  "publish-unpublish_date-date" => "Unpublish Date",
  
  // Control Panel - Blocks
  "block" => "Block",
  "content-space-dropdown" => "Space",
  "content-priority-dropdown" => "Block Priority",
  "content-redirect_flag-checkbox" => "Redirect to External File",                    
  "content-redirect_location-field" => "External File Location (in /smpl-includes/)",    
     
  // Control Panel - Info Pane
  "cms" => "Content Management System",
  "infoHtml" => 'Bug reports, suggestions: <a href="https://github.com/gowondesigns/smpl/issues" target="_blank">Please open a new issue.</a><br/>
  Check to see if there are any <a href="http://smply.it/" target="_blank">new releases</a> of SMPL available.<br/>
  <a href="http://smply.it/" target="_blank">SMPL</a> is licensed under the <a href="http://www.opensource.org/licenses/osl-3.0.php" target="_blank">Open Software License 3.0</a>.',
  
  // Control Panel - Statistics Pane
  "statistics" => "Statistics",
  "lastLogin" => "Last Login: ",
  "totalUsers" => "Total Users: ",
  "totalPages" => "Total Pages: ",
  "totalArticles" => "Total Articles: ",
  "pendingPublishes" => "Pending Publishes: "
);

?>