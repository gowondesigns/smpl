<?php
error_reporting(-1);
//set_error_handler(array('Debug', 'ErrorHandler'));
//register_shutdown_function(array('Debug', 'EndOfExecution'));
Debug::Set(Debug::DEBUG_ON, Debug::STRICT_OFF, Debug::VERBOSE_OFF, Debug::LOGGING_OFF);

function __autoload($class_name)
{
    require_once('classes/Class.'.$class_name.'.php');
}

IncludeFromFolder("classes/");

$database = Config::Database();
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
if (isset($_POST['create'])) $statusMessage = CreateDatabase();
if (isset($_POST['fill'])) $statusMessage = GenerateNewData();
if (isset($_POST['clear'])) $statusMessage = ClearAllData();

$html .= "<h2>$statusMessage</h2>
</body>
</html>";

echo $html;



function CreateDatabase()
{
    $database = Config::Database();
    $file = file_get_contents('./data/database.sql', true);
    $database->CustomQuery($file);
    return "Database Created";    
}

function GenerateNewData()
{
    ClearAllData();
    $database = Config::Database();
    $queries = array();
    $errors = array();


    /* Generate Settings */
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('siteURL')
        ->Item('title-label')->SetValue('Site URL')
        ->Item('value-field')->SetValue('http://localhost/smpl/')->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('siteTitle')
        ->Item('title-label')->SetValue('Site Title')
        ->Item('value-field')->SetValue('My SMPL Site')->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('siteDescription')
        ->Item('title-label')->SetValue('Site Description')
        ->Item('value-field')->SetValue('My SMPL Site is a website that hosts pages and articles.')->Send();

    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('siteMungLength')
        ->Item('title-label')->SetValue('SEO URL Max Length')
        ->Item('value-field')->SetValue(50)->Send();

    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('MarkdownActive')
        ->Item('title-label')->SetValue('Parse Articles for Markdown')
        ->Item('value-field')->SetValue(true)->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('listMaxNum')
        ->Item('title-label')->SetValue('Max # of items per page listed in categorical view')
        ->Item('value-field')->SetValue(10)->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('feedDefaultType')
        ->Item('title-label')->SetValue('Default feed format')
        ->Item('value-field')->SetValue('Atom')->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('feedItemLimit')
        ->Item('title-label')->SetValue('Max # items in feed')
        ->Item('value-field')->SetValue(5)->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('permalinkSalt')
        ->Item('title-label')->SetValue('Salt integer for unique permalinks')
        ->Item('value-field')->SetValue(rand(0,62))->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('languageDefault')
        ->Item('title-label')->SetValue('Default language')
        ->Item('value-field')->SetValue('en-US')->Send();
    
    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('dateOffset')
        ->Item('title-label')->SetValue('Date Timezone Offset')
        ->Item('value-field')->SetValue(rand(-12,14))->Send();

    $errors[] = $database->Create()->UsingTable("settings")
        ->Item('name-hidden')->SetValue('articleFormat')
        ->Item('title-label')->SetValue('Article Format')
        ->Item('value-field')->SetValue("<h1>[category]&nbsp;/&nbsp;[title]</h1>\n<p>[body]</p>")->Send();

    
    /* Generate Users */
    for($i = 0; $i < 16; $i++)
    {
        $bin = decbin(15 - $i);
        $permissions = substr("0000",0,4 - strlen($bin)) . $bin;
        
        $errors[] = $database->Create()
            ->UsingTable("users")
            ->Item('account-user_name-hash')->SetValue(md5('user'.$i))
            ->Item('account-password-hash')->SetValue(md5('password'))
            ->Item('account-name-field')->SetValue("User {$i}")
            ->Item('account-email-field')->SetValue('fake_email@domain.com')
            ->Item('permissions-access_system-checkbox')->SetValue($permissions[0])
            ->Item('permissions-access_users-checkbox')->SetValue($permissions[1])
            ->Item('permissions-access_content-checkbox')->SetValue($permissions[2])
            ->Item('permissions-access_blocks-checkbox')->SetValue($permissions[3])
            ->Send();   
    }


    /* Generate Categories */
    $categories = array(
        'Uncategorized' => 1,
        'Tips & Tricks' => 1,
        'Disabled' => 0,
        'Misc Stuff' => 1,
        'A 5th Category' => 0 
    );
    
    foreach ($categories as $key => $value)
    {
        $errors[] = $database->Create()
            ->UsingTable("categories")
            ->Item('title-field')->SetValue($key)
            ->Item('title_mung-field')->SetValue(Utils::Munge($key, Config::Get('siteMungLength')))
            ->Item('publish_flag-checkbox')->SetValue($value)
            ->Send(); 
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
        $errors[] = $database->Create()
            ->UsingTable("spaces")
            ->Item('title-field')->SetValue($key)
            ->Item('title_mung-field')->SetValue(Utils::Munge($key, Config::Get('siteMungLength')))
            ->Item('publish_flag-checkbox')->SetValue($value)
            ->Send(); 
    }
    
    /* Generate Content */    

    for($i = 0; $i < 10; $i++)
    {
        $titleWords = array('The','as','is','a','Orange','Blue','Man','Woman','Cat','Dog', 2, '&hearts;');
        shuffle($titleWords);
        $title = implode(' ', array_slice($titleWords, 0, 7));
        $default = ($i == 0);

        $errors[] = $database->Create()
            ->UsingTable("content")
            ->Item('content-title-field')->SetValue($title)
            ->Item('content-title_mung-field')->SetValue(Utils::Munge($title, Config::Get('siteMungLength')))
            ->Item('meta-static_page_flag-checkbox')->SetValue(rand(0,1))
            ->Item('meta-indexed_flag-checkbox')->SetValue(rand(0,1))
            ->Item('meta-default_page_flag-checkbox')->SetValue($default)
            ->Item('meta-category-dropdown')->SetValue(rand(1,5))
            ->Item('meta-author-dropdown')->SetValue(rand(1,16))
            ->Item('meta-date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('content-body-textarea')->SetValue($database->real_escape_string(gibberish(4,20)))
            ->Item('publish-publish_flag-dropdown')->SetValue(Query::PUB_ACTIVE)
            ->Item('publish-publish_date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('publish-unpublish_flag-checkbox')->SetValue(rand(0,1))
            ->Item('publish-unpublish_date-date')->SetValue(Date::Now()->AddTime(120 + $i)->ToInt())
            ->Send();   
    }
    
        /* Markdown Article Example */
    $article = '
## Parsedown PHP

Parsedown is a parser for Markdown. It parses Markdown text the way people do. First, it divides texts into blocks. Then it looks at how these blocks start and how they relate to each other. Finally, it looks for special characters to identify inline elements. As a result, Parsedown is (super) fast, consistent and clean.

[Explorer (demo)](http://parsedown.org/explorer/)  
[Tests](http://parsedown.org/tests/)

### Installation

Include `Parsedown.php` or install [the composer package](https://packagist.org/packages/erusev/parsedown).

### Example

```php
$text = \'Hello **Parsedown**!\';

$result = Parsedown::instance()->parse($text);

echo $result; # prints: <p>Hello <strong>Parsedown</strong>!</p>
```';

        $errors[] = $database->Update()
            ->UsingTable("content")
            ->Item('meta-static_page_flag-checkbox')->SetValue(0)
            ->Item('content-body-textarea')->SetValue(mysql_escape_string($article))
            ->Item('content-tags-field')->SetValue("test,stuff,blah,foo")
            ->Match("id", 3)
            ->Send();
    

    
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
    $database = Config::Database();
    
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'api');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'blocks');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'categories');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'content');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'settings');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'spaces');
    $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'users');
    
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
    
    return '<p>'.implode(".</p>\n\n<p>", $paragraphs).'.</p>';
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
