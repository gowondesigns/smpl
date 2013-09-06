<?php
define("DEBUG_MODE", true);
define("DEBUG_STRICT", false);
define("DEBUG_VERBOSE", true);
define("DEBUG_LOGGING", false);


error_reporting(-1);
set_error_handler(array('Debug', 'ErrorHandler'));
register_shutdown_function(array('Debug', 'EndOfExecution'));
Debug::Set(DEBUG_MODE, DEBUG_STRICT, DEBUG_VERBOSE, DEBUG_LOGGING);

function __autoload($class_name)
{
    require_once('classes/Class.'.$class_name.'.php');
}

IncludeFromFolder("classes/");

$database = Database::Connect();
$query = $database::NewQuery()
    ->Select()
    ->Using("users")
    ->Item("id")->Item("account-name-field","name")
    ->Match("permissions-access_content-checkbox", true)
    ->OrWhere()->Match("permissions-access_blocks-checkbox", true)
    ->AndWhere()->Match("permissions-access_system-checkbox", true)
    ->Limit(3)
    ->OrderBy("name");
var_dump($query);
echo $query;    

$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SMPL Test Data Tool</title>
</head>

<body id="home">

    <h1>SMPL Test Data Tool</h1>
    <form action="?" method="post">
          Create Tables
          <button type="submit" name="create">Click Here</button><br/>
          Generate New Data & Fill Database
          <button type="submit" name="fill">Click Here</button><br/>
          Clear Database
          <button type="submit" name="clear">Click Here</button>
    </form>';

$statusMessage = null;
if (isset($_POST['fill'])) $statusMessage = GenerateNewData();
if (isset($_POST['clear'])) $statusMessage = ClearAllData();

$html .= "<h2>$statusMessage</h2>
</body>
</html>";

echo $html;
/*
$database = Database::Connect();
$database->Queries();
var_dump(get_class_methods($database));
*/

function GenerateNewData()
{
    ClearAllData();
    $database = Database::Connect();
    $errors = array();

    
    /* Generate Users */
    $users = array(
        'account-user_name-hash' => null,
        'account-password-hash' => md5('password'),
        'account-name-field' => null,
        'account-email-field' => 'fake_email@domain.com',
        'permissions-access_system-checkbox' => null,
        'permissions-access_users-checkbox' => null,
        'permissions-access_content-checkbox' => null,
        'permissions-access_blocks-checkbox' => null
    );

    for($i = 0; $i < 16; $i++)
    {
        $bin = decbin(15 - $i);
        $permissions = substr("0000",0,4 - strlen($bin)) . $bin;
        
        $users['account-user_name-hash'] = md5('user'.$i);
        $users['account-name-field'] = "User {$i}";
        $users['permissions-access_system-checkbox'] = $permissions[0];
        $users['permissions-access_users-checkbox'] = $permissions[1];
        $users['permissions-access_content-checkbox'] = $permissions[2];
        $users['permissions-access_blocks-checkbox'] = $permissions[3];
        
        $errors[] = $database->Create('users', $users);    
    }


    /* Generate Categories */
    $categories = array(
        'Uncategorized' => 1,
        'Articles' => 1,
        'Disabled' => 0,
        'Misc Stuff' => 1 
    );
    
    foreach ($categories as $key => $value)
    {
        $data = array(
            'title-field' => $key,
            'title_mung-field' => Utils::Munge($key),
            'publish_flag-checkbox' => $value
        );
        
        $errors[] = $database->Create('categories', $data);
    }
    
    
    /* Generate Spaces */
    $spaces = array(
        'Side Content' => 1,
        'Ads' => 1,
        'Disabled' => 0,
        'Footer' => 1 
    );
    
    foreach ($spaces as $key => $value)
    {
        $data = array(
            'title-field' => $key,
            'title_mung-field' => Utils::Munge($key),
            'publish_flag-checkbox' => $value
        );
        
        $errors[] = $database->Create('spaces', $data);
    }
    
    /* Generate Content */    
/*
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
  `publish-publish_flag-dropdown` ENUM('NOTPUBLISHED', 'PUBLISHED', 'TOPUBLISH') NOT NULL DEFAULT 'PUBLISHED',
  `publish-publish_date-date` BIGINT UNSIGNED NOT NULL,
  `publish-unpublish_flag-checkbox` BOOL NOT NULL DEFAULT FALSE,
  `publish-unpublish_date-date` BIGINT UNSIGNED NOT NULL
//*/    
    $titleWords = array('The','as','is','a','Orange','Blue','Man','Woman','Cat','Dog', 2, '&hearts;');
    shuffle($titleWords);
    
    $title = implode(' ', $titleWords);
        
    $data = array(
        'content-title-field' => $title,
        'content-title_mung-field' => Utils::Munge($title),
        'content-static_page_flag-checkbox' => true,
        'content-in_category_flag-checkbox' => true,
        'content-default_page_flag-checkbox' => true,
        'content-category-dropdown' => 1,
        'content-author-dropdown' => 1,
        'content-date-date' => Date::Now()->ToInt(),
        'content-body-textarea' => $database->real_escape_string(lipsum(4,20)),
        'content-tags-field' => null,
        'publish-publish_flag-dropdown' => 'PUBLISHED',
        'publish-publish_date-date' => Date::Now()->ToInt(),
        'publish-unpublish_flag-checkbox' => true,
        'publish-unpublish_date-date' => Date::Now()->AddTime(3600)->ToInt()
    );
    
    $errors[] = $database->Create('content', $data);
    
    
    /* Generate Settings */
    $setting = array('name-hidden' => 'siteURL', 'title-label' => 'Site URL', 'value-field' => 'http://localhost/smpl/');
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'title', 'title-label' => 'Site Title', 'value-field' => 'My SMPL Site');
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'description', 'title-label' => 'Site Description', 'value-field' => 'My SMPL Site Description');
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'listMaxNum', 'title-label' => 'Max # of items per page listed in categorical view', 'value-field' => 10);
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'feedDefaultType', 'title-label' => 'Default feed format', 'value-field' => 'Atom');
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'feedItemLimit', 'title-label' => 'Max # items in feed', 'value-field' => 5);
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'permalinkSalt', 'title-label' => 'Salt integer for unique permalinks', 'value-field' => rand(0,62));
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'languageDefault', 'title-label' => 'Default language', 'value-field' => 'en-US');
    $errors[] = $database->Create('settings', $setting);
    $setting = array('name-hidden' => 'dateOffset', 'title-label' => 'Date Timezone Offset', 'value-field' => rand(-12,14));
    $errors[] = $database->Create('settings', $setting);
    
    /* Pass along any errors*/
    $msg = null;
    $errMsg = null;
    
    foreach ($errors as $key => $value)
    {
        if($value == false)
            $errMsg .= "{$key}, ";
    }
    
    if (null == $errMsg)
        $msg = "All Data Successfully Generated";
    else
        $msg = "The following queries failed: ".$errMsg;
    
    return $msg;
}

function ClearAllData()
{
    $configurations = Configuration::Database();
    $database = Database::Connect();
    
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}api");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}blocks");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}categories");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}content");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}settings");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}spaces");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}users");
    
    return "All Data Clear";
}

function lipsum($numParagraphs = 1, $wordsPerParagragh = 10, $maxWordLength = 20)
{
    $bank = array_merge(range('a', 'z'), range('A', 'Z'));
    $paragraphs = array();
    
    for ($i = 0; $i < $numParagraphs; $i++)
    {
        $words = array();
        
        for ($j = 0; $j < $wordsPerParagragh; $j++)
        {
            shuffle($bank);
            $word = implode($bank);
            $length = rand(3, $maxWordLength);
            $words[] = substr($word, 0, $length);
        }
        
        $paragraphs[] = implode(' ', $words);
    }
    
    return '<p>'.implode(".</p>\n\n<p>", $paragraphs).'</p>';
}

function IncludeFromFolder($folder)
{
    foreach (glob("{$folder}*.php") as $filename)
    {
        //print('Including '.$filename.'<br>');
        require_once($filename);
    }
}

?>
