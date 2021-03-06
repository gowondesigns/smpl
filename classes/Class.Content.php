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
    // Custom hooks are inserted in smpl-includes/, filenames 'hook.<hook_name>.php'
    // 'StaticClassName::StaticMethodName'


    //Do-nothing function
    public static function ValidateUri()
    {
        $l = Language::Create();
        $index = array_search($l->Phrase('tags'), self::$uri);
        $uri = implode('/', self::$uri);
        // Check if 'tags' is triggered in the URI at the first location
        // Go into Tag URI Validation
        if($index === 0)
        {
            if(isset($_POST['tagSearch']))
            {
                $query = Utils::Munge($_POST['tagSearch']);
                header('Location: '. Utils::GenerateUri('tags', $query));
                exit;  
            }
            /*  TAGS Trigger:
                /tags/<search-phrase>/
                /tags/<search-phrase>/<index-number>/ (Seeking through results)
                /tags/<search-phrase>/date/ (sort results by date, most recent first)
                /tags/<search-phrase>/date/<index-number>/ (Seeking through results)
            */        
            elseif (preg_match('\/tags\/([A-Za-z0-9\-]+)((\/\d+||(date(\/\d+)*))(\/)*)*', $uri) === 1)
                return; // URI validates, Tags Method will process output
            else
            {
                header('Location: '. Utils::GenerateUri('404'));
                exit;  
            }
        
        }
        /*


        
        CATEGORIES Trigger:
        /categories/<category-title>/
        /categories/<category-title>/<index-number>/
        \/categories\/([A-Za-z0-9\-]+)\/([0-9]*)\/?
        
        ARTICLES Trigger:
        /articles/ (all active articles)
        /articles/<index-number>/
        \/articles\/([0-9]*)\/?
        
        Signature-Based Triggers:
        3-parameters, 2nd param = 'articles'
        /<category-title>/articles/<article-title>/ (Long-form URL, most helpful, default)
        \/([A-Za-z0-9\-]+)\/articles\/([A-Za-z0-9\-]+)\/*
        
        2-parameters, 2nd param = 'articles'
        /<category-title>/articles/ (redirect to /categories/<category-title>/)
        ([A-Za-z0-9\-]+)\/articles\/*
        
        2-parameters
        /<category-title>/<page-title>/ (Long-form URL)
        \/([A-Za-z0-9\-]+)\/([A-Za-z0-9\-]+)\/*
        
        1-parameter
        /<page-title>/ (Articles cannot be accessed this way, they must have the "Static Content" flag to be treated like a page)
        \/([A-Za-z0-9\-]+)\/* 
        
        ELSE
        
        /404/ (If the correct URI does not fetch a result, system redirects to this. Content can be injected by creating "404" block)
        //*/
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
        if (Config::USE_MINIFY) {
            ob_start(array('Content', 'Minify'));
        }

        // Set script timezone to UTC for uniform dates regardless of server settings
        // Date objects will handle offset internally
        date_default_timezone_set('UTC');
        
        //$l = Language::Create();
        self::$uri = array_filter(explode('/', $_SERVER['QUERY_STRING']), 'strlen'); // Filter out any empty/null/false
        self::$hooks = array(
            'pre' => array(
                '*' => array(
                    'Content::ValidateUri'
                ),
//                'tags' => 'Content::TagSearch',
//                'sitemap' => 'Sitemap::Hook',
//                'link' => 'Content::Permalink',
//                'feed' => 'Feed::Hook',
//                'api' => 'Content::Stub',
                'lang' => 'Language::Hook',
//                'admin' => 'Admin::Hook'
            ),
            'head' => array(
                '*' => array(
                    'Content::HtmlHeader'
                ),
                'articles' => 'Content::GenerateMetaKeywords',
                'pages' => 'Content::GenerateMetaKeywords'
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
            Debug::Log("Including additional class found at: ".$filename);
            include_once($filename);
        }
        
        // If no POST signature is present, any existing validation data should be unset
        $key = Security::Key();
        if (!isset($_POST[$key]))
            unset($_SESSION[$key]['validate']);
        
        $database = Config::Database();
        $database->Execute(Query::Build('Content\\Init: Publish pending articles.')
            ->Update()
            ->UseTable('content')
            ->Set('publish-publish_flag-set', Query::PUB_ACTIVE)
            ->Where()->IsEqual('publish-publish_flag-set', Query::PUB_FUTURE)
            ->AndWhere()->IsEqual('publish-publish_date-date', Date::Now()->ToInt()));
        $database->Execute(Query::Build('Content\\Init: Publish pending articles.')
            ->Update()
            ->UseTable('content')
            ->Set('publish-publish_flag-set', Query::PUB_NOT)
            ->Set('publish-unpublish_flag-bool', 0)
            ->Where()->IsEqual('publish-unpublish_flag-bool', 1)
            ->AndWhere()->IsEqual('publish-unpublish_date-date', Date::Now()->ToInt()));
            
    }


    private static function ParseHook($hooks)
    {
        // Run all wildcard '*' hooks
        foreach($hooks['*'] as $trigger)
        {
            $action = explode("::", $trigger);
            $action[0]::$action[1]();
        } 

        // Run any hooks triggerd by the URI
        foreach (self::$uri as $key)
        {
            //pass along the index where the hook appears in the URI
            $index = array_search($key, self::$uri);
            if($index !== false)
            {
                if (is_array($hooks[$key]))
                {                        
                    foreach($hooks[$key] as $trigger)
                    {
                        $action = explode("::", $trigger);
                        $action[0]::$action[1]($index);
                    }
                }
                else
                {
                    $action = explode("::", $hooks[$key]);
                    $action[0]::$action[1]($index);
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
        
    public static function Debug()
    {
        $data = array("hooks" => self::$hooks,
            "spaces" => self::$spaces,
            "suppressMainExec" => self::$suppressMainExec,
            "uri" => self::$uri);
        return print_r($data, true);
    }
    
    public static function GetCategoryById($id)
    {
        $result = Config::Database()->Execute(Query::Build('Retrieve content information.')
            ->Retrieve()
            ->UseTable('categories')
            ->Get('title-field')
            ->Where()->IsEqual('id', $id))->Fetch();            
        return $result['title-field'];
    }

    public static function GetAuthorById($id)
    {
        $result = Config::Database()->Execute(Query::Build('Retrieve content information.')
            ->Retrieve()
            ->UseTable('users')
            ->Get('account-name-field')
            ->Where()->IsEqual('id', $id))->Fetch();
        
        return $result['account-name-field'];
    }
    
    public static function Permalink()
    {
        // Format: /permalink/<CONTENT_ID>/
        $content = Config::Database()->Execute(Query::Build('Retrieve content information.')
            ->Retrieve()
            ->UseTable('content')
            ->Get(array('content-title_mung-field', 'meta-category-set', 'meta-static_page_flag-bool', 'meta-indexed_flag-bool'))
            ->Where()->IsEqual('publish-publish_flag-set', Query::PUB_ACTIVE)
            ->AndWhere()->IsEqual('id', self::$uri[1]))->Fetch();
        
        if (isset($content['content-title_mung-field']))
        {
            $category = Content::GetCategoryByID($content['meta-category-set']);
            $url = Utils::GenerateUri($category['title_mung-field'], 'articles', $content['content-title_mung-field']);
            
            //Check if content is a static page
            if($content['meta-static_page_flag-bool'] == true)
            {
                if($content['meta-indexed_flag-bool'] == true)
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
            $results = Config::Database()->Execute(Query::Build('Retrieve space.')
            ->Retrieve()
            ->UseTable('spaces')
            ->Get('id')
            ->Where()->IsEqual('title_mung-field', $spaceName))->Fetch();
            
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
                //$searchPhrase = preg_replace('-', ' ', self::$uri[1]);
                $searchPhrase = explode('-', self::$uri[1]);
                
                $l = Language::Create();
                $html = '<h1>'.$l->Phrase("tagSearch")."</h1>\n";
                $query = Query::Build('Retrieving article found in URI')
                    ->Retrieve()
                    ->UseTable('content')
                    ->Get('id');
            
                foreach ($searchPhrase as $phrase) {
                    $query->OrWhere()->IsLike(array('content-title-field', 'content-body-text', 'content-tags-field'), '%' . $phrase . '%')
                    ->AndWhere()->IsEqual('meta-static_page_flag-bool', 0)
                    ->AndWhere()->IsEqual('publish-publish_flag-set', Query::PUB_ACTIVE);
                }
                
                if (self::$uri[2] == 'date')
                    $query->OrderBy('meta-date-date', Query::SORT_DESC);
                
                if (is_numeric(self::$uri[$tagIndex]))
                    $query->Limit(Config::Get('listMaxNum'))
                        ->Offset(( (self::$uri[$tagIndex] - 1) * intval(Config::Get('listMaxNum')) ));

                $results = Config::Database()->Execute($query)->FetchAll();

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
        $html = '<title>'.Config::Get('siteTitle')."</title>\n";
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
            $site = Config::Site();
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
        $database = Config::Database();
        
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
    
    public static function GenerateMetaKeywords()
    {
        // [MUSTCHANGE] Should use this for pages and articles.
        $uri = Content::Uri();
        if ($uri[1] != 'articles' || !isset($uri[2])) {
            trigger_error('Could not find article in URI.', E_USER_WARNING);
        }
            $article = Config::Database()->Execute(Query::Build('Grab content tags.')
                ->Retrieve()
                ->UseTable('content')
                ->Get('content-tags-field')
                ->Where()->IsEqual('content-title_mung-field', $uri[2]))->Fetch();

        echo '<meta name="keywords" content="' . $article['content-tags-field'] . '"/>';
    }

    public static function RenderArticle()
    {
        $uri = Content::Uri();
        if ($uri[1] != 'articles' || !isset($uri[2])) {
            trigger_error('Could not render article', E_USER_ERROR);
        }
            $data = Config::Database()->Execute(Query::Build('Retrieving article found in URI')
                ->Retrieve()
                ->UseTable('content')
                ->Where()->IsEqual('content-title_mung-field', $uri[2]));
        
        $article = new Article($data);
        $article->Render();
    }

    /**
     * @param $buffer
     * @param $mode
     * @return mixed|string
     */
    private static function Minify($buffer, $mode) {
        $search = array(
            '/<!--[^\[](.*?)-->/s' => '',
            //'/ +/' => ' ', // Old way to remove extra whitespace, wasn't catching everything
            '/\s{2,}/' => '', // New way catches everything, maybe that's too aggressive?
            //"/<!–\{(.*?)\}–>|<!–(.*?)–>|[\t\r\n]|<!–|–>|\/\/ <!–|\/\/ –>|<!\[CDATA\[|\/\/ \]\]>|\]\]>|\/\/\]\]>|\/\/<!\[CDATA\[/" => ''
            "/[\t\r\n]|<!–|–>|\/\/ <!–|\/\/ –>|<!\[CDATA\[|\/\/ \]\]>|\]\]>|\/\/\]\]>|\/\/<!\[CDATA\[/" => ''
        );
        $buffer = preg_replace(array_keys($search), array_values($search), $buffer);
        /* Only gzip for content with a min size of 860 bytes
         *
         * The reasons 860 bytes is the minimum size for compression is twofold: (1) The overhead of compressing an object
         * under 860 bytes outweighs performance gain. (2) Objects under 860 bytes can be transmitted via a single packet
         * anyway, so there isn't a compelling reason to compress them.
         */
        if (ob_get_length() >= 860) {
            return ob_gzhandler($buffer, $mode);
        }
        return $buffer;
    }
}


class Space
{
    private $blocks = array();
    
    public function __construct($titleMung)
    {
        $database = Config::Database();      
        $space = $database->Execute(Query::Build('Grab space')
            ->Retrieve()
            ->UseTable('spaces')
            ->Get('id')
            ->Where()->IsEqual('title_mung-field', $titleMung))->Fetch();
            
        $blocks = $database->Execute(Query::Build('Grab Blocks')
            ->Retrieve()
            ->UseTable('blocks')
            ->Get('id')
            ->Where()->IsEqual('meta-space-set', $space['id'])
            ->AndWhere()->IsEqual('publish-publish_flag-set', Query::PUB_ACTIVE)
            ->OrderBy('meta-priority-set', Query::SORT_DESC)
            ->OrderBy('item', Query::SORT_ASC));
            
        while ($block = $blocks->Fetch())
        {
            $this->blocks = new Block($block['id']);
        }
        
        $this->Render();
    }
    
    public function Render()
    {
        foreach($this->blocks as $block)
        {
            $block->Render();
        }
    }
}


interface IContent
{
    public function Get($item);
    public function Render();
}


class Page implements IContent
{
    protected $id;
    protected $title;
    protected $titleMung;
    protected $body;
    protected $categoryMung;
    protected $date;
    protected $tags = null;
    
    public function __construct($idOrData)
    {
        $database = Config::Database();
        if (is_numeric($idOrData)) {
            $page = $database->Execute(Query::Build('Grab page using ID')
                ->Retrieve()
                ->UseTable('content')
                ->Where()->IsEqual('id', $idOrData))->Fetch();
        }
        elseif ($idOrData instanceof DatabaseResult) {
            $page = $idOrData->Fetch();
        }
        else {
            trigger_error('Passed argument not numeric or of type DatabaseResult.', E_USER_ERROR);
        }
            
        $this->id = $page['id'];
        $this->title = $page['content-title-field'];
        $this->date = Date::FromString($page['meta-date-date']);
        
        $this->tags = explode(',', $page['content-tags-field']);
        foreach ($this->tags as $key => $value) {
            $this->tags[$key] = trim($value);
        }

        $category = $database->Execute(Query::Build('Grab page category using ID')
            ->Retrieve()
            ->UseTable('categories')
            ->Where()->IsEqual('id', $page['meta-category-set']))->Fetch();
        $this->categoryMung = $category['title_mung-field'];
    }
    
    public function Get($data)
    {
        if ($data == 'tags') {
            return implode(', ', $this->tags);
        }
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
    
    
    public function __construct($idOrData)
    {
        $database = Config::Database(); 
        
        if (is_numeric($idOrData)) { 
            $article = $database->Execute(Query::Build('Grab article using ID')
                ->Retrieve()
                ->UseTable('content')
                ->Where()->IsEqual('id', $idOrData))->Fetch();
        }
        elseif ($idOrData instanceof DatabaseResult) {
            $article = $idOrData->Fetch();
        }
        else {
            trigger_error('Passed argument not numeric or of type DatabaseResult.', E_USER_ERROR);
        }

        $this->id = $article['id'];
        $this->title = $article['content-title-field'];
        $this->titleMung = $article['content-title_mung-field'];
        $this->body = $article['content-body-text'];
        $this->date = Date::FromString($article['meta-date-date']);
        $this->author = Content::GetAuthorById($article['meta-author-set']);
        $this->category = Content::GetCategoryById($article['meta-category-set']); 
        $this->tags = explode(',', $article['content-tags-field']);
        foreach ($this->tags as $key => $value) {
            $this->tags[$key] = trim($value);
        }

        $category = $database->Execute(Query::Build('Grab article category using ID')
            ->Retrieve()
            ->UseTable('categories')
            ->Where()->IsEqual('id', $article['meta-category-set']))->Fetch();
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
        if(Config::Get('MarkdownActive'))
            $body = Parsedown::instance()->parse($this->body);
        else
            $body = $this->body; 
        
        $formatTags = array( 
            "[title]" => $this->title, 
            "[url]" => Utils::GenerateUri($this->categoryMung, 'articles', $this->titleMung), 
            "[body]" => $body, 
            "[category]" => $this->category, 
            "[category_url]" => Utils::GenerateUri('categories', $this->categoryMung), 
            "[author]" => $this->author, 
            "[date]" => $this->date->ToString(), 
            "[tags]" => "TAGSLIST" 
        );
         
        $formattedString = str_replace(array_keys($formatTags), array_values($formatTags), Config::Get('articleFormat')); 
        echo $formattedString; 
    }
}


class Block implements IContent
{
    protected $titleMung;
    protected $body;
    private $redirectLocation = null;
    
    public function __construct($idOrData)
    {
        if (is_numeric($idOrData)) { 
            $block = Config::Database()->Execute(Query::Build('Grab article using ID')
                ->Retrieve()
                ->UseTable('blocks')
                ->Where()->IsEqual('id', $idOrData))->Fetch();
        }
        elseif ($idOrData instanceof DatabaseResult) {
            $block = $idOrData->Fetch();
        }
        else {
            trigger_error('Passed argument not numeric or of type DatabaseResult.', E_USER_ERROR);
        }
        $this->redirectLocation = $block['redirect_location-field'];
    }
    
    public function Get($data)
    {
        return $this->$data;
    }
    
    public function Render()
    {
        echo $this->body; // [MUSTCHANGE]
    }
    
}

?>
