<?php
/* SMPL Feed Classes
// 
//
//*/


class Feed
{
    public static function Generate()
    {
        $feed = null;
        $type = Configuration::Get('feedDefaultType').'Feed';
        $category = null;                                                                                       
        $database = Database::Connect();
 
        if(count(Content::Uri()) > 3)
            throw new ErrorException("URI Error.");
              
        // First analyze query string
        // Default feed = /feed/
        // OR /feed/<feed-type>/
        if(Content::Uri()[0] == 'feed')
        {
            if(isset(Content::Uri()[1]))
                $type = ucfirst(Content::Uri()[1]).'Feed';
        }
        // /<category-title>/feed/
        // /<category-title>/feed/<feed-type>/
        else if (Content::Uri()[1] == 'feed')
        {
            if(isset(Content::Uri()[2]))
                $type = ucfirst(Content::Uri()[2]).'Feed';

            $result = $database->Retrieve()
                ->UsingTable("categories")
                ->Item("id")
                ->Match("title_mung-field", Content::Uri()[0])
                ->Execute();
            $category = $result->Fetch();
        }

        Debug::Message("Feed\Render: Creating new feed object of type ".$type);
        if(class_exists($type) && is_subclass_of($type,'IFeed'))
            $feed = new $type;
        else
            throw new ErrorException("{$type} is not a compatible IFeed object or does not exist.");

        // Populate feed with proper articles
        $query = $database->Retrieve()
            ->UsingTable("content")
            ->Item("id")
            ->Match("publish-publish_flag-dropdown", 2)
            ->AndWhere()->Match("content-static_page_flag-checkbox", 0)
            ->OrderBy("publish-publish_date-date", false)
            ->Limit(Configuration::Get('feedItemLimit'));
        
        if(isset($category['id']))
            $query->AndWhere()->Match("content-category-dropdown",$category['id'])
                ->AndWhere()->Match("content-in_category_flag-checkbox", 1);
        
        $list = $query->Execute();
        
        while($item = $list->Fetch())
        {
            $feed->Add(new Article($item['id']));
        }
        
        // Render then die
        $feed->Render();
        exit;
    }
    
    public static function GenerateUuid($key = null, $prefix = null)
    {
		    $key = ($key == null)? uniqid(rand()) : $key;
    		$chars = md5($key);
    		$uuid  = substr($chars,0,8) . '-';
    		$uuid .= substr($chars,8,4) . '-';
    		$uuid .= substr($chars,12,4) . '-';
		    $uuid .= substr($chars,16,4) . '-';
    		$uuid .= substr($chars,20,12);

		    return $prefix . $uuid;
    }
}

// Feed Class Interface
interface IFeed
{
    public static function FeedMimeType();
    public function Add(Article $article);
    public function Render();
}

// Generic Feed Class
abstract class aFeed
{
    protected $title;
    protected $feedUrl;
    protected $lastUpdated;
    protected $feedDescription;
    protected $articles = array();
    
    protected function __construct()
    {
        $this->title = Configuration::Get('title');
        $this->feedDescription = Configuration::Get('description');
    }
}


final class AtomFeed extends aFeed implements IFeed
{
    private $feedUuid;
    
    public static function FeedMimeType()
    {
        return "application/atom+xml";
    }
    
    public function __construct()
    {
        $this->feedUrl = Utils::GenerateUri('feed');
        $this->feedUuid = Feed::GenerateUuid();
        parent::__construct();
    }
    
    public function Add(Article $article)
    {
        $this->articles[] = $article;
    }
    
    public function Render()
    {
        $date = Date::Now();
        $offset = Date::Offset();
        header("Content-Type: application/atom+xml charset=utf-8");
        header("Cache-Control:public");
        header("Expires: ".$date->AddTime(3600)->ToString('D, d M Y H:i:s \G\M\T')); 
        
        $xml = "<\x3F".'xml version="1.0" encoding="utf-8"'."\x3F>\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n\n\t";
        $xml .= "<title>{$this->title}</title>\n\t<subtitle>{$this->feedDescription}</subtitle>\n\t";
        $xml .= '<link href="'.$this->feedUrl.'" rel="self" type="application/atom+xml" />'."\n\t"; 
        $xml .= "<id>urn:uuid:{$this->feedUuid}</id>\n\t";
        $xml .= "<updated>".$date->ToString("Y-m-d\TH:i:s").$offset."</updated>\n\n\n";
      
        foreach ($this->articles as $entry)
        {
            $title =  htmlspecialchars($entry->Get('title'));
            $link = Utils::GenerateUri($entry->Get('categoryMung'),'articles',$entry->Get('titleMung')); // /<category-title>/articles/<article-title>/
            $permalink = Utils::GenerateUri('link',Utils::PermalinkEncode($entry->Get('id')));
            
            $xml .= "\t<entry>\n\t\t";
            $xml .= '<title type="html">'.$title."</title>\n\t\t";
            $xml .= '<link href="'.$link.'" />'."\n\t\t";
            $xml .= '<link href="'.$permalink.'" rel="alternate" type="text/html" />'."\n\t\t"; 
            $xml .= "<id>urn:uuid:".Feed::GenerateUuid($entry->Get('id'))."</id>\n\t\t";
            $xml .= "<updated>".$entry->Get('date')->ToString("Y-m-d\TH:i:s").$offset."</updated>\n\t\t";
            $xml .= "<summary>".$entry->Summary()."</summary>\n\t\t";
            $xml .= "<author>\n\t\t\t<name>".$entry->Get('author')."</name>\n\t\t</author>\n";
            $xml .= "\t</entry>\n\n";
        }
        
        $xml .= "</feed>";
        
        echo $xml;
    }
}

?>