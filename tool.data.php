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
    ->UsingTable("users")
    ->Item("id")->Item("account-name-field","name")->Item("users.permissions-access_system-checkbox", "sys")
    ->Match("permissions-access_content-checkbox", true)
    ->OrWhere()->Match("permissions-access_blocks-checkbox", true)
    ->AndWhere()->Match("users.permissions-access_system-checkbox", true)
    ->Offset(1)
    ->Limit(3)
    ->OrderBy("name");
//var_dump($query);
//echo $query;

$query = $database::NewQuery()
    ->Create()
    ->UsingTable("settings")
    ->Item("name-hidden")->SetValue("dummySetting")
    ->Item("title-label")->SetValue("This is a dummy setting")
    ->Item("value-field")->SetValue(0);
//var_dump($query);
//echo $query;

$query = $database::NewQuery()
    ->Update()
    ->UsingTable("settings")
    ->Item("value-field")->SetValue(1)
    ->Match("name-hidden", "dummySetting")
    ->OrderBy("name-hidden")
    ->Limit(2);
//var_dump($query);
//echo $query;

$query = $database::NewQuery()
    ->Delete()
    ->UsingTable("settings")
    ->Match("name-hidden", "dummySetting");
//var_dump($query);
//echo $query;

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
    $queries = array();
    $errors = array();

    
    /* Generate Users */
    for($i = 0; $i < 16; $i++)
    {
        $bin = decbin(15 - $i);
        $permissions = substr("0000",0,4 - strlen($bin)) . $bin;
        
        $errors[] = $database::NewQuery()->Create()
            ->UsingTable("users")
            ->Item('account-user_name-hash')->SetValue(md5('user'.$i))
            ->Item('account-password-hash')->SetValue(md5('password'))
            ->Item('account-name-field')->SetValue(md5("User {$i}"))
            ->Item('account-email-field')->SetValue('fake_email@domain.com')
            ->Item('permissions-access_system-checkbox')->SetValue($permissions[0])
            ->Item('permissions-access_users-checkbox')->SetValue($permissions[1])
            ->Item('permissions-access_content-checkbox')->SetValue($permissions[2])
            ->Item('permissions-access_blocks-checkbox')->SetValue($permissions[3])
            ->Execute($database);   
    }


    /* Generate Categories */
    $categories = array(
        'Uncategorized' => 1,
        'Articles' => 1,
        'Disabled' => 0,
        'Misc Stuff' => 1,
        'A 5th Category' => 0 
    );
    
    foreach ($categories as $key => $value)
    {
        $errors[] = $database::NewQuery()->Create()
            ->UsingTable("categories")
            ->Item('title-field')->SetValue($key)
            ->Item('title_mung-field')->SetValue(Utils::Munge($key))
            ->Item('publish_flag-checkbox')->SetValue($value)
            ->Execute($database); 
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
        $errors[] = $database::NewQuery()->Create()
            ->UsingTable("spaces")
            ->Item('title-field')->SetValue($key)
            ->Item('title_mung-field')->SetValue(Utils::Munge($key))
            ->Item('publish_flag-checkbox')->SetValue($value)
            ->Execute($database); 
    }
    
    /* Generate Content */    

    for($i = 0; $i < 10; $i++)
    {
        $titleWords = array('The','as','is','a','Orange','Blue','Man','Woman','Cat','Dog', 2, '&hearts;');
        shuffle($titleWords);
        $title = implode(' ', array_slice($titleWords, 0, 5));
        $default = ($i == 0);

        $errors[] = $database::NewQuery()->Create()
            ->UsingTable("content")
            ->Item('content-title-field')->SetValue($title)
            ->Item('content-title_mung-field')->SetValue(Utils::Munge($title))
            ->Item('content-static_page_flag-checkbox')->SetValue(rand(0,1))
            ->Item('content-in_category_flag-checkbox')->SetValue(rand(0,1))
            ->Item('content-default_page_flag-checkbox')->SetValue($default)
            ->Item('content-category-dropdown')->SetValue(rand(1,5))
            ->Item('content-author-dropdown')->SetValue(rand(1,16))
            ->Item('content-date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('content-body-textarea')->SetValue($database->real_escape_string(gibberish(4,20)))
            ->Item('publish-publish_flag-dropdown')->SetValue(2)
            ->Item('publish-publish_date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('publish-unpublish_flag-checkbox')->SetValue(rand(0,1))
            ->Item('publish-unpublish_date-date')->SetValue(Date::Now()->AddTime(120 + $i)->ToInt())
            ->Execute($database);   
    }
    
    
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
    
    $database::NewQuery()->Custom("TRUNCATE TABLE {$configurations['prefix']}api")->Execute($database);
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}blocks");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}categories");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}content");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}settings");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}spaces");
    $database->CustomQuery("TRUNCATE TABLE {$configurations['prefix']}users");
    
    return "All Data Clear";
}

function gibberish($numParagraphs = 1, $wordsPerParagragh = 10, $maxWordLength = 20)
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
