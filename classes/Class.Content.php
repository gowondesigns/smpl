<?php
/* SMPL Content Class
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


static class Content
{
    private static $spaces = array('main' => null);

    
    // Method to initiate all automatic actions
    public static function Update()
    {
     /*   function updateContent() {
$query="UPDATE ".db('prefix')."content SET published='1' WHERE published='2' AND date<='".timeStamp()."'";
mysql_query($query);
$query="UPDATE ".db('prefix')."content SET published='0', unpublish='0' WHERE unpublish='1' AND enddate<='".timeStamp()."'";
mysql_query($query);
}
*/ 
    }
    
    public static function Status()
    {
        return print_r($this->spaces, true);        
    }
    
    public static function Space($spaceName)
    {
        if (!isset($this->spaces[$spaceName]) || null === $this->spaces[$spaceName])
        {
            $this->spaces[$spaceName] = new Space($spaceName);
        }

        return $this->spaces[$spaceName];
    }
    
    public static function HtmlHeader()
    {
        $html = '<title>'.Configuration::GetSetting('title')."</title>\n";
        $html .= '<meta charset="utf-8">'."\n".'<meta name="robots" content="index,follow">'."\n";
        $html .= '<meta name="description" content="'.Configuration::GetSetting('description').'">'."\n";
        //'<meta name="keywords" content="Tags from Content Object if present">'; // [MUSTCHANGE]
          
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
        // Grab and explode URI Query data
        // List assets in heirarchical order, with links to various paths
    }
    
    
}


class Space
{

    public function __construct($spaceName)
    {

            $database = Database::Connect();
            $result = $database->Retrieve('spaces', 'id', "title_mung-field = '{$spaceName}'");
        
            if ($value = $result->fetch_array(MYSQLI_ASSOC))
            {
                $this->spaces[$spaceName] = null;
                $spaceId = $value['id'];
                $result = $database->Retrieve('blocks', '*', "content-space-dropdown = '{$spaceId}'", "ORDER BY content-priority-dropdown DESC");
            }
        
        if(isset($this->spaces[$spaceName]))
        {}
        elseif($spaceName =='main')
        {}
        

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
    protected $tags = array();
    
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
    
    public function Summary()
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
