<?php
/* SMPL Feed Classes
// 
//
//*/


class Feed
{
    public static function Render()
    {
        $feed = null;
        $type = Content::Get('feedDefaultType').'Feed';
        $limit = 'ORDER BY publish-publish_date-date DESC LIMIT '. Content::Get('feedItemLimit');
        $category = null;
        $database = Database::Connect();
                
        // First analyze query string
        // Default feed = /feed/
        // OR /feed/<feed-type>/
        if(Content::$uri[0] == 'feed')
        {
            if(isset(Content::$uri[1]))
                $type = ucfirst(Content::$uri[1]).'Feed';
            $feed = (class_exists($type)) ? new $type(): null; 
        
        }
        // /<category-title>/feed/
        // /<category-title>/feed/<feed-type>/
        else if (Content::$uri[1] == 'feed')
        {
            if(isset(Content::$uri[2]))
                $type = ucfirst(Content::$uri[2]).'Feed';
            $feed = (class_exists($type)) ? new $type(): null;
            
            $result = $database->Retrieve('categories', 'id',  "title_mung-field = '.".Content::$uri[0]."'");
            $category = $result->Fetch();
        }

        // Populate feed with proper articles
        $byCategory = (isset($category['id'])) ? "AND content-category-dropdown = {$category['id']} AND content-in_category_flag-checkbox = 1": null;
        $list = $database->Retrieve('content', 'id',  "publish-publish_flag-dropdown = 2 AND content-static_page_flag-checkbox = 0 {$byCategory}", $limit);
        
        while($id = $list->Fetch())
        {
            $item = new FeedItem(new Article($id));
            $feed->AddItem($item);
        }
        
        // Render then die
        $feed->Render();
        exit;
    }
    
    public static function GenerateUuid($key = null, $prefix = '')
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
interface iFeed
{
    public static function FeedMimeType();
    public function AddItem(FeedItem $item);
    public function Render();
}

// Generic Feed Class
abstract class aFeed
{
    protected $title;
    protected $feedUrl;
    protected $lastUpdated;
    protected $feedDescription;
    protected $feedItems = array();
    
    protected function __construct()
    {
        $this->title = Configuration::Get('title');
        $this->lastUpdated = Date::Create();
        $this->feedDescription = Configuration::Get('description');
    }
}


final class AtomFeed extends aFeed implements iFeed
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
    
    public function AddItem(FeedItem $item)
    {
        $this->feedItems[] = $item;
    }
    
    public function Render()
    {
        header("Content-Type: application/atom+xml charset=utf-8");
        
        $xml = "<\x3F".'xml version="1.0" encoding="utf-8'."\x3F>\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n\n\t";
        $xml .= "<title>{$this->title}</title>\n\t<subtitle>{$this->feedDescription}</subtitle>\n\t";
        $xml .= '<link href="'.$this->feedUrl.'" />'."\n\t"; 
        $xml .= "<id>urn:uuid:{$this->feedUuid}</id>\n\t";
        $xml .= "<updated>".Date::CreateFlat($this->lastUpdated, "Y-m-d\x54H:i:sP")."</updated>\n\n\n";
      
        foreach ($this->feedItems as $value)
        {
            $entry = $value->Details();
            $xml .= "\t<entry>\n\t\t";
            $xml .= "<title>{$entry['title']}</title>\n\t\t";
            $xml .= '<link href="'.$entry['url'].'" />'."\n\t\t";
            $xml .= '<link href="'.$entry['permalink'].'" rel="alternate" type="text/html" />'."\n\t\t"; 
            $xml .= "<id>urn:uuid:{$entry['uuid']}</id>\n\t\t";
            $xml .= "<updated>".Date::CreateFlat($entry['date'], "Y-m-d\x54H:i:sP")."</updated>\n\t\t";
            $xml .= "<summary>{$entry['summary']}</summary>\n\t\t";
            $xml .= "<author>\n\t\t\t<name>{$entry['author']}</name>\n\t\t</author>\n";
            $xml .= "\t</entry>\n\n";
        }
        
        $xml .= "</feed>";
        
        echo $xml;
    }
}


class FeedItem
{
    private $title;
    private $url;
    private $permalink;
    private $entryUuid;
    private $date;
    private $summary;
    private $author;
    
    public function __construct(Article $article)
    {
        $this->title = $article->Get('title');
        $this->url = $article->Get('titleMung');
        $this->permalink = Utils::GenerateUri('articles',$article->Get('id'));
        $this->date = $article->Get('date');
        $this->author = $article->Get('author');
        $this->entryUuid = Feed::GenerateUuid();
        $this->summary = $article->Summary();
    } 
    
    
    public function Details()
    {
        return array(
        'title' => $this->title,
        'url' => $this->url,
        'permalink' => $this->permalink,
        'uuid' => $this->entryUuid,
        'date' => $this->date,
        'summary' => $this->summary,
        'author' => $this->author);
    }
}

/*
<\x3F".'xml version="1.0" encoding="utf-8'."\x3F>
<rss version="2.0">
<channel>
 <title>RSS Title</title>
 <description>This is an example of an RSS feed</description>
 <link>http://www.someexamplerssdomain.com/main.html</link>
 <lastBuildDate>Mon, 06 Sep 2010 00:01:00 +0000 </lastBuildDate>
 <pubDate>Mon, 06 Sep 2009 16:20:00 +0000 </pubDate>
 <ttl>1800</ttl>
 
 <item>
  <title>Example entry</title>
  <description>Here is some text containing an interesting description.</description>
  <link>http://www.wikipedia.org/</link>
  <guid>unique string per item</guid>
  <pubDate>Mon, 06 Sep 2009 16:20:00 +0000 </pubDate>
 </item>
 
</channel>
</rss>

//*/


?>