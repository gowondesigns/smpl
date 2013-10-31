<?php
Debug::Set(Debug::DEBUG_ON, Debug::STRICT_OFF, Debug::VERBOSE_OFF, Debug::LOGGING_OFF, __DIR__);

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
    $settings = array(
        array('site_url', 'Site URL', 'http://localhost/smpl/'),
        array('site_title', 'Site Title', 'My SMPL Site'),
        array('site_description', 'Site Description', 'My SMPL Site is a website that hosts pages and articles.'),
        array('site_mung_length', 'SEO URL Max Length', 50),
        array('markdown_active', 'Parse Articles for Markdown', true),
        array('list_max_num', 'Max # of items per page listed in categorical view', 10),
        array('feed_default_type', 'Default feed format', 'Atom'),
        array('feed_item_limit', 'Max # items in feed', 5),
        array('permalink_salt', 'Salt integer for unique permalinks', rand(0,62)),
        array('language_default', 'Default language', 'en-US'),
        array('date_offset', 'Timezone Offset for Dates and Timestamps', rand(-12,14)),
        array('article_format', 'Article format', '<h1>[category]&nbsp;/&nbsp;[title]</h1>\n<p>[body]</p>')
    );
    
    foreach ($settings as $setting) {
        $errors[] = $database->Execute(Query::Build()->Create()
            ->UseTable('settings')
            ->Set('name-hidden', $setting[0])
            ->Set('title-label', $setting[1])
            ->Set('value-field', $setting[2]));
    }

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
            ->Item('group-access_system-bool')->SetValue($permissions[0])
            ->Item('group-access_users-bool')->SetValue($permissions[1])
            ->Item('group-access_content-bool')->SetValue($permissions[2])
            ->Item('group-access_blocks-bool')->SetValue($permissions[3])
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
            ->Item('publish_flag-bool')->SetValue($value)
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
            ->Item('publish_flag-bool')->SetValue($value)
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
            ->Item('meta-static_page_flag-bool')->SetValue(rand(0,1))
            ->Item('meta-indexed_flag-bool')->SetValue(rand(0,1))
            ->Item('meta-default_page_flag-bool')->SetValue($default)
            ->Item('meta-category-set')->SetValue(rand(1,5))
            ->Item('meta-author-set')->SetValue(rand(1,16))
            ->Item('meta-date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('content-body-text')->SetValue($database->real_escape_string(gibberish(4,20)))
            ->Item('publish-publish_flag-set')->SetValue(Query::PUB_ACTIVE)
            ->Item('publish-publish_date-date')->SetValue(Date::Now()->AddTime($i)->ToInt())
            ->Item('publish-unpublish_flag-bool')->SetValue(rand(0,1))
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
            ->Item('meta-static_page_flag-bool')->SetValue(0)
            ->Item('content-body-text')->SetValue(mysql_escape_string($article))
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
    if (is_a($database, 'MySqlDatabase')) {
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'api');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'blocks');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'categories');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'content');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'settings');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'spaces');
        $database->CustomQuery('TRUNCATE TABLE ' . Config::DB_PREFIX . 'users');
        return "All Data Clear";
    }
    else {
        return 'Could not clear data from the database.';
    }

}

function gibberish($numParagraphs = 1, $wordsPerParagraph = 10, $maxWordLength = 20)
{
    $bank = array_merge(range('a', 'z'), range('A', 'Z'));
    $paragraphs = array();
    
    for ($i = 0; $i < $numParagraphs; $i++)
    {
        $words = array();
        
        for ($j = 0; $j < $wordsPerParagraph; $j++)
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
