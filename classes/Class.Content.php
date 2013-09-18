<?php
/* SMPL Content Class
// 
//
//*/


class Content
{
    private static $uri = null;
    private static $suppressMainExec = false;
    
    private static $spaces = array(
//        'head' => null,
//        'main' => null
    );
    
    private static $hooks = array();
    
    // [MUSTCHANGE]
    // Hooks define the behavior the CMS will take when a trigger is defined
    // Custom hooks are inserted in smpl-includes/, filnames 'hook.<hook_name>.php'
    // 'StaticClassName::StaticMethodName'


    //Do-nothing function
    public static function Stub()
    {
        //echo "\n\nStub\n\n";
        return;
    }
    
    // Getter for URI
    public static function Uri()
    {
        return self::$uri;
    }
        
    // Method to initiate all automatic actions
    public static function Initialize()
    {
        // Set script timezone to UTC for uniform dates regardless of server settings
        // Date objects will handle offset internally
        date_default_timezone_set('UTC');
        
        $l = Language::Create();
        self::$uri = array_filter(explode('/', $_SERVER['QUERY_STRING']), 'strlen'); // Filter out any empty/null/false
        self::$hooks = array(
            'pre' => array(
                '*' => array(
                    'Content::Stub'
                ),
                'tags' => 'Content::UpdateTagsUri',
                'sitemap' => 'Sitemap::RenderXML',
                'link' => 'Content::Permalink',
                'lang' => 'Language::Hook',
                'feed' => 'Feed::Generate',
                'api' => 'Content::Stub',
                'admin' => 'Admin::Render'
            ),
            'head' => array(
                '*' => array(
                    'Content::HtmlHeader'
                ),
                'articles' => 'Content::TagsKeywords',
                'pages' => 'Content::TagsKeywords'
            ),
            'main' => array(
                '*' => array(
                ),
                'tags' => 'Content::ListByTags',
                'categories' => 'Content::ListByCategory',
                'articles' => 'Content::RenderArticle'
            )
        );
        
        
        /* Make modifications to CMS, including various files if they're present on the server
        smpl-includes/	(for Redirect Blocks)
        class.*.php -> Additional Classes
        hook.*.php -> Additional Hooks
        smpl-includes/hooks/	(recommended organization to manage customized hooks)
        smpl-uploads/ (Where uploaded content where be stored and managed)

        */
        foreach (glob("smpl-includes/class.*.php") as $filename)
        {
            Debug::Message("Content\Including additional class found at: ".$filename);
            include_once($filename);
        }
        
        // Is no POST signature is present, any existing validation data should be unset
        $key = md5(Configuration::Get('siteURL'));
        if (!isset($_POST[$key]))
            unset($_SESSION[$key]['validate']);
        
        $database = Database::Connect();
        $database->Update()
            ->UsingTable("content")
            ->UsingTable("blocks")
            ->Item("publish-publish_flag-dropdown")->SetValue(1)
            ->Match("publish-publish_flag-dropdown", 2)
            ->AndWhere()->LessThanOrEq("publish-publish_date-date", Date::Now()->ToInt())
            ->Execute();
        $database->Update()
            ->UsingTable("content")
            ->UsingTable("blocks")
            ->Item("publish-publish_flag-dropdown")->SetValue(0)
            ->Item("publish-unpublish_flag-checkbox")->SetValue(0)
            ->Match("publish-unpublish_flag-checkbox", 1)
            ->AndWhere()->LessThanOrEq("publish-unpublish_date-date", Date::Now()->ToInt())
            ->Execute();
    }


    private static function ParseHook($hooks)
    {
        // Run all wildcard '*' hooks
        foreach($hooks['*'] as $trigger)
        {
            $action = explode("::", $trigger);
            $action[0]::$action[1](self::$uri);
        } 

        // Run any hooks triggerd by the URI
        foreach (self::$uri as $key)
        {
            //[MUSTCHANGE] Need to pass along the index where the hook appears in the URI
            //$index = array_search('lang', Content::Uri());
            if(array_key_exists($key, $hooks))
            {
                if (is_array($hooks[$key]))
                {                        
                    foreach($hooks[$key] as $trigger)
                    {
                        $action = explode("::", $trigger);
                        $action[0]::$action[1](self::$uri);
                    }
                }
                else
                {
                    $action = explode("::", $hooks[$key]);
                    $action[0]::$action[1](self::$uri);
                }
            } 
        }
    }
        
    // Change the behavior of the system based on any hooks defined in the URI [MUSTCHANGE]
    public static function Hook()
    {
        try
        {
            self::ParseHook(self::$hooks['pre']);
        }
        catch (Exception $e)
        {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            die;
        }
    }
    
    // May not be useful    
    public static function Status()
    {
        print_r(self, true);
        exit;        
    }
    
    public static function GetCategoryById($id)
    {
        $database = Database::Connect();
        $result = $database->Retrieve()
            ->UsingTable("categories")
            ->Match("id", $id)
            ->Execute()->Fetch();
            
        return $result['title-field'];
    }

    public static function GetAuthorById($id)
    {
        $database = Database::Connect();
        $result = $database->Retrieve()
            ->UsingTable("users")
            ->Match("id", $id)
            ->Execute()->Fetch();
        
        return $result['account-name-field'];
    }
    
    public static function Permalink()
    {
        // Format: /permalink/<CONTENT_ID>/
        $uri = Utils::GenerateUri();
        
        $database = Database::Connect();
        //$result = $database->Retrieve('content', 'content-title_mung-field, meta-category-dropdown, meta-static_page_flag-checkbox, meta-indexed_flag-checkbox',  "publish-publish_flag-dropdown = 2 AND id = '".self::$uri[1]."'");
        $result = $database->Retrieve()
                ->UsingTable("content")
                ->Item('content-title_mung-field')->Item('meta-category-dropdown')->Item('meta-static_page_flag-checkbox')->Item('meta-indexed_flag-checkbox')
                ->Match("publish-publish_flag-dropdown", 2)
                ->AndWhere()->Match("id", self::$uri[1])
                ->Execute();
        $content = $result->Fetch();
        
        if (isset($content['content-title_mung-field']))
        {
            $category = Content::GetCategoryByID($content['meta-category-dropdown']);
            $url = Utils::GenerateUri($category['title_mung-field'], 'articles', $content['content-title_mung-field']);
            
            //Check if content is a static page
            if($content['meta-static_page_flag-checkbox'] == true)
            {
                if($content['meta-indexed_flag-checkbox'] == true)
                     $url = Utils::GenerateUri($category['title_mung-field'], $content['content-title_mung-field']);
                else
                     $url = Utils::GenerateUri($content['content-title_mung-field']);
            }
                
        }
        
        header('Location: ' . $url, true, 302);
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
        // Check if space exists in database. Overrides any default mechanisms
        if (isset(self::$spaces[$spaceName]))
        {
            self::$spaces[$spaceName]->Render();
            return;
        }
        else
        {
            $database = Database::Connect();
            $data = $database->Retrieve()
                ->UsingTable("spaces")
                ->Item("id")
                ->Match("title_mung-field", $spaceName)
                ->Execute();
            
            $results = $data->Fetch();
            if(isset($results['id']))
            {
                $space = new Space($results['id']); 
                self::$spaces[$spaceName] = $space;
                $space->Render();
                return;
            }
        }
        
        // Default Head Space - execute hooks
        if ($spaceName == 'head')
        {
            self::ParseHook(self::$hooks['head']);
            return;
        }
/*
TAGS Trigger:
/tags/<search-phrase>/
/tags/<search-phrase>/<index-number>/ (Seeking through results)
/tags/<search-phrase>/date/ (sort results by date, most recent first)
/tags/<search-phrase>/date/<index-number>/ (Seeking through results)

CATEGORIES Trigger:
/categories/<category-title>/
/categories/<category-title>/<index-number>/

ARTICLES Trigger:
/articles/ (all active articles)
/articles/<index-number>/

Signature-Based Triggers:
3-parameters, 2nd param = 'articles'
/<category-title>/articles/<article-title>/ (Long-form URL, most helpful, default)

2-parameters, 2nd param = 'articles'
/<category-title>/articles/ (redirect to /categories/<category-title>/)

2-parameters
/<category-title>/<page-title>/ (Long-form URL)

1-parameter
/<page-title>/ (Articles cannot be accessed this way, they must have the "Static Content" flag to be treated like a page)

ELSE

/404/ (If the correct URI does not fetch a result, system redirects to this. Content can be injected by creating "404" block)
//*/
        //Default Main Space behavior
        if ($spaceName == 'main')
        {
            self::ParseHook(self::$hooks['main']);
            
            if (self::$suppressMainExec)
                return;
            
            // Tag Searches
            if(self::$uri[0] == 'tags')
            {
                $tagIndex = count(self::$uri) - 1;
                $searchPhrase = preg_replace('-', ' ', self::$uri[1]);
                
                $l = Language::Create();
                $database = Database::Connect();
                $html = '<h1>'.$l->Phrase("tagSearch")."</h1>\n";
                //$data = $database->Retrieve('content', 'id', "MATCH(content-title-field, content-body-textarea, content-tags-field) AGAINST('{$searchPhrase}' IN BOOLEAN MODE) AND meta-static_page_flag-checkbox = false AND publish-publish_flag-dropdown = 2", $queryExtra);
                $query = $database->Retrieve()
                ->UsingTable("content")
                ->Item("id")
                ->FindIn(array("content-title-field", "content-body-textarea", "content-tags-field"), $searchPhrase)
                ->AndWhere()->Match("meta-static_page_flag-checkbox", 0)
                ->AndWhere()->Match("publish-publish_flag-dropdown", 2);
                
                if (self::$uri[2] == 'date')
                    $query->OrderBy("meta-date-date", false);
                
                if (is_numeric(self::$uri[$tagIndex]))
                    $query->Limit(Configuration::Get('listMaxNum'))
                        ->Offset(( (self::$uri[$tagIndex] - 1) * intval(Configuration::Get('listMaxNum')) ));

                $results = $query->Execute()->FetchAll();

                // Render results
                foreach($results as $id)
                {
                    $article = new Article($id);
                    $html .= "<article>\n<h3>".$article->Get('title')."</h3>\n<p>".$article->Summary()."</p>\n</article>\n\n";
                }
                
                $html .= "<p>Pagination 1 2 3</p>";
                echo $html;
                return;
            }
            
            return;
        }        
    }
    //            Content::HtmlHeader()
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
            $l = Language::Create(); // Grab language data

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
        $l = Language::Create(); // Grab language data
        $database = Database::Connect();
        
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

class Space
{
    private $blocks = array();
    
    public function __construct($id)
    {
        if($data->Count() < 1)
            die();
        
        $page = $data->Fetch();
        $this->id = $id;
        $this->title = $page['content-title-field'];
        $this->date = Date::FromString($page['meta-date-date']);
        
        $this->tags = explode(',', $page['content-tags-field']);
        foreach ($this->tags as $key => $value)
        {
            $this->tags[$key] = trim($value);
        }

        $category = Content::GetCategoryById($page['meta-category-dropdown']);
        $this->categoryMung = $category['title_mung-field']; 

        
        parent::__construct($page['content-title_mung-field'], $page['content-body-textarea']);
    }
}


interface IContentObject
{
    public function Get($item);
    public function Render();
}


class Page implements IContentObject
{
    protected $id;
    protected $title;
    protected $titleMung;
    protected $body;
    protected $categoryMung;
    protected $date;
    protected $tags = null;
    
    public function __construct($data)
    {
        $database = Database::Connect();
        if(is_numeric($data))
        {
            $page = $database->Retrieve()
                ->UsingTable("content")
                ->Match("id", $data)
                ->Execute()->Fetch();
        }
        elseif($data instanceof IDatabaseResult)
        {
            $page = $data->Fetch();
        }
        else
            throw new ErrorException("Passed argument not numeric or of type IDatabaseResult");
            
        $this->id = $page['id'];
        $this->title = $page['content-title-field'];
        $this->date = Date::FromString($page['meta-date-date']);
        
        $this->tags = explode(',', $page['content-tags-field']);
        foreach ($this->tags as $key => $value)
        {
            $this->tags[$key] = trim($value);
        }

        $category = $database->Retrieve()
                ->UsingTable("categories")
                ->Match("id", $page['meta-category-dropdown'])
                ->Execute()->Fetch();
        $this->categoryMung = $category['title_mung-field']; 

        
        //parent::__construct($page['content-title_mung-field'], $page['content-body-textarea']);
    }
    
    public function Get($data)
    {
        if($data == 'tags')
            return implode(', ', $this->tags);
            
        return $this->$data;
    }
    
    public function Render()
    {
        echo $this->body; // [MUSTCHANGE]
    }
}

 // Articles are pages with extra formatting considerations
class Article extends Page
{
    protected $author;
    protected $category;
    
    
    public function __construct($data)
    {
        $database = Database::Connect(); 
        
        if(is_numeric($data))
        { 
            $article = $database->Retrieve()
                ->UsingTable("content")
                ->Match("id", $data)
                ->Execute()->Fetch();
        }
        elseif($data instanceof IDatabaseResult)
        {
            $article = $data->Fetch();
        }
        else
            throw new ErrorException("Passed argument not numeric or of type IDatabaseResult");

        $this->id = $article['id'];
        $this->title = $article['content-title-field'];
        $this->titleMung = $article['content-title_mung-field'];
        $this->body = $article['content-body-textarea'];
        $this->date = Date::FromString($article['meta-date-date']);
        $this->author = Content::GetAuthorById($article['meta-author-dropdown']);
        $this->category = Content::GetCategoryById($article['meta-category-dropdown']); 
        
        
        $this->tags = explode(',', $article['content-tags-field']);
        foreach ($this->tags as $key => $value)
        {
            $this->tags[$key] = trim($value);
        }

        $category = $database->Retrieve()
                ->UsingTable("categories")
                ->Match("id", $article['meta-category-dropdown'])
                ->Execute()->Fetch();
        $this->categoryMung = $category['title_mung-field']; 
    }
    
    public function Summary($size = 160)
    {
        $summary = Utils::Strip($this->body);
        $summary = Utils::Truncate($summary, $size);
        return $summary;
    }

    public function Render() 
    {
        $formatTags = array( 
            "[title]" => $this->title, 
            "[url]" => Utils::GenerateUri($this->categoryMung, 'articles', $this->titleMung), 
            "[body]" => $this->body, 
            "[category]" => $this->category, 
            "[category_url]" => Utils::GenerateUri('categories', $this->categoryMung), 
            "[author]" => $this->author, 
            "[date]" => $this->date->ToString(), 
            "[tags]" => "TAGSLIST" 
        );
         
        $formattedString = str_replace(array_keys($formatTags), array_values($formatTags), Configuration::Get('articleFormat')); 
        echo $formattedString; 
    }
}


class Block implements IContentObject
{
    protected $titleMung;
    protected $body;
    private $redirectLocation = null;
    
    public function __construct($id, MySQLi_Result $data = null)
    {
        if (null === $result)
        {
            $database = Database::Connect();
            $data = $database->Retrieve()
                ->UsingTable("blocks")
                ->Match("id", $id)
                ->Execute();
        }
        
        $block = $data->Fetch();
        $this->redirectLocation = $block['redirect_location-field'];
        
        
        //parent::__construct($block['content-title_mung-field'], $block['content-body-textarea']);
    }
    
    public function Get($data)
    {
        if($data == 'tags')
            return implode(', ', $this->tags);
            
        return $this->$data;
    }
    
    public function Render()
    {
        echo $this->body; // [MUSTCHANGE]
    }
    
}

?>
