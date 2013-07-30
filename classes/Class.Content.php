<?php
/* SMPL Content Class
// 
//
//*/


static class Content
{
    private static $uri = null;
    
    private static $spaces = array(
        'head' => null,
        'main' => null
    );
    
    private static $hooks = array(
        'pre' => array(
            '*' => array(
            ),
            'sitemap' => 'Sitemap::RenderXML',
            'feed' => 'Feed::Render',
            'api' => 'Content::Stub',
        ),
        'head' => array(
            '*' => array(
            'Content::HtmlHeader'
            ),
            'articles' => 'Content::TagsKeywords',
            'pages' => 'Content::TagsKeywords',
        ),
        'main' => array(
            '*' => array(
            ),
            'tags' => 'Content::ListByTags',
            'categories' => 'Content::ListByCategory',
            'articles' => 'Content::RenderArticle',
        )
    );
    
    // [MUSTCHANGE]
    // Hooks define the behavior the CMS will take when a trigger is defined
    // Custom hooks are inserted in smpl-includes/, filnames 'hook.<hook_name>.php'
    // 'StaticClassName::StaticMethodName'


    //Do-nothing function
    public static function Stub($uri)
    {
        return null;
    }
    
    // Method to initiate all automatic actions
    public static function Update()
    {
        self::$uri = explode('/', $_SERVER['QUERY_STRING']);
        
        
        /* Make modifications to CMS, including various files if they're present on the server
        smpl-includes/	(for Redirect Blocks)
        class.*.php -> Additional Classes
        hook.*.php -> Additional Hooks
        smpl-includes/hooks/	(recommended organization to manage customized hooks)
        smpl-uploads/ (Where uploaded content where be stored and managed)

        */
        foreach (glob("smpl-includes/class.*.php") as $filename)
        {
        require_once($filename);
        }
        
        // Is not POST signature is present, any existing validation data should be unset
        $key = md5(Configuration::Get('siteURL'));
        if (!isset($_POST[$key]))
            unset($_SESSION[$key]['validate']);
        
        $database = Database::Connect();
        $database->Update(array('content', 'blocks'), array('publish-publish_flag-dropdown' => 1), "publish-publish_flag-dropdown = 2 AND publish-publish_date-date <= ".Date::CreateFlat() );
        $database->Update(array('content', 'blocks'), array('publish-publish_flag-dropdown' => 0, 'publish-unpublish_flag-checkbox' => 0), "publish-unpublish_flag-checkbox = 1 AND publish-unpublish_date-date <= ".Date::CreateFlat() );
    }

    // Change the behavior of the system based on any hooks defined in the URI [MUSTCHANGE]
    public static function Hook()
    {
        // Run all wildcard '*' hooks
        foreach (self::$hooks['pre']['*'] as $action)
            $action(self::$uri); 

        // Run any hooks triggerd by the URI
        foreach (self::$uri as $key)
        {
            if(array_key_exists($key, self::$hooks['pre'])))
                self::$hooks['pre'][$key](self::$uri); 
        }
    }
    
        
    public static function Status()
    {
        print_r(self, true);
        exit;        
    }

/*
First check is Space is MAIN (reserved) space.
- Check to see if query is calling for default page.
-- Load 'main' override space if it is present and active
-- Otherwise load the default page
-Otherwise load the content being called
- If no content is suitable, look for '404' space
-- If no '404' space is designated, call default '404' block
Otherwise, look for the space being called
- If found but not active, alert with comment <!-- The space 'spaceName' is not visible. -->
- If found, load all relevant blocks
-- If no blocks are found, alert with comment <!-- The space 'spaceName' is empty. --> 
- If not found, alert with comment <!-- The space 'spaceName' does not exist. -->

[MUSTCHANGE]
//*/       
    public static function Space($spaceName)
    {
        if (!isset($this->spaces[$spaceName]) || null === $this->spaces[$spaceName])
        {
            $this->spaces[$spaceName] = new Space($spaceName);
        }
        else if (!isset($this->spaces[$spaceName]) || null === $this->spaces[$spaceName])
        {
        }
        //return $this->spaces[$spaceName];
    }
    
    public static function HtmlHeader()
    {
        $html = '<title>'.Configuration::Get('title')."</title>\n";
        $html .= '<meta content="text/html; charset=UTF-8" http-equiv="content-type">'."\n";
        $html .= '<meta name="robots" content="index,follow">'."\n";
        $html .= '<link rel="alternate" type="application/atom+xml" title="ATOM 1.0" href="'.Utils::GenerateUri('feed/').'">'."\n";
        //$html .= '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="'.Utils::GenerateUri('feed/rss/').'">'."\n";
          
        echo $html;
    }
    
    
    public static function AdminBlock()
    {
        if (Security::Authenticate()) // If the user is logged in
        {
            $site = Configuration::Site();
            $html = null;
            $l = LanguageFactory::Create(); // Grab language data

            $html .= '<div class="block_admin"><ul>';
            $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin')).'" title="'.$l->Phrase('Admnistration').'">'.$l->Phrase('Admnistration').'</a></li>';
            if (true) // If the user has permissions to edit the current content piece
            {
                $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin'),'CONTENT-TYPE','edit','CONTENT-ID').'" title="'.$l->Phrase('Admnistration').'">'.$l->Phrase('EditContent').'</a></li>';
            }
            $html .= '<li><a href="'.Utils::GenerateUri($l->Phrase('admin')).'" title="'.$l->Phrase('Logout').'">'.$l->Phrase('Logout').'</a></li>';
            $html .= '</ul></div>';
            
            echo $html;
        }
    }

    public static function Breadcrumbs($seperator = "&bull;")
    {
        $l = LanguageFactory::Create(); // Grab language data
        $crumbs = array();
        $crumbs[] = '<li><a href="'.Utils::GenerateUri().'" title="'.$l->Phrase('Home').'">'.$l->Phrase('Home')."</a></li>\n";
        
        $html = "<ul class=\"breadcrumbs\">\n";
        
        if (Security::Authenticate())
        {
            $crumbs[] = '<li><a href="'.Utils::GenerateUri($l->Phrase('admin')).'" title="'.$l->Phrase('Admnistration').'">'.$l->Phrase('Admnistration')."</a></li>\n";
            $crumbs[] = '<li><a href="'.Utils::GenerateUri($l->Phrase('admin'),'logout').'" title="'.$l->Phrase('Logout').'">'.$l->Phrase('Logout')."</a></li>\n";
        }
        
        $html .= implode($crumbs);
        $html .= "</ul>\n";
        echo $html;
        // Grab and explode URI Query data
        // List assets in heirarchical order, with links to various paths
        // [MUSTCHANGE]
    }
    
    
}


// Structure for all content elements
abstract class aContentObject
{
    protected $titleMung;
    protected $body;
    
    protected function __construct($titleMung, $body)
    {
        $this->titleMung = $titleMung;
        $this->body = $body;
    }

    abstract public function Display();
        
}


class Page extends aContentObject
{
    protected $title;
    protected $permalink;
    protected $category;
    protected $date;
    protected $tags = null;
    
    public function __construct($id, MySQLi_Result $data = null)
    {
        if (null === $data)
        {
            $database = Database::Connect();
            $data = $database->Retrieve('content', '*',  "id = '{$id}'");
        }
        
        $page = $data->fetch_array(MYSQLI_ASSOC);
        $this->title = $page['content-title-field'];
        $this->permalink = $page['content-permalink-hidden'];
        $this->date = Date::Create($page['content-date-date']);
        
        $this->tags = explode(',', $page['content-tags-field']);
        foreach ($this->tags as $key => $value)
        {
            $this->tags[$key] = trim($value);
        }
        
        $result = $database->Retrieve('categories', 'title-field',  "id = '{$page['content-category-dropdown']}'");
        $category = $result->fetch_array(MYSQLI_ASSOC);
        $this->category = $category['title-field'];
        
        
        parent::__construct($page['content-title_mung-field'], $page['content-body-textarea']);
    }
    
    public function Display()
    {
        return null; // [MUSTCHANGE]
    }
    
    public function Get($data)
    {
        if($data == 'tags')
            return implode(', ', $this->tags);
            
        return $this->$data;
    }
}

 // Articles are pages with extra formatting considerations
class Article extends Page
{
    protected $author;
    
    
    public function __construct($id, MySQLi_Result $data = null)
    {
        if (null === $data)
        {
            $database = Database::Connect();
            $data = $database->Retrieve('content', '*',  "id = '{$id}'");
        }
        
        $article = $data->fetch_array(MYSQLI_ASSOC);
        $result = $database->Retrieve('users', 'account-name-field',  "id = '{$article['content-category-dropdown']}'");
        $author = $result->fetch_array(MYSQLI_ASSOC);
        $this->author = $author['account-name-field'];        
        
        parent::__construct($id, $data);
    }
    
    public function Summary() // [MUSTCHANGE]
    {
    }
}


class Block extends aContentObject
{
    private $redirectLocation = null;
    
    public function __construct($id, MySQLi_Result $data = null)
    {
        if (null === $result)
        {
            $database = Database::Connect();
            $data = $database->Retrieve('blocks', '*',  "id = '{$id}'");
        }
        
        $block = $data->fetch_array(MYSQLI_ASSOC);
        $this->redirectLocation = $block['redirect_location-field'];
        
        
        parent::__construct($block['content-title_mung-field'], $block['content-body-textarea']);
    }
}

?>
