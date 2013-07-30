<?php
/* SMPL Feed Classes
// 
//
//*/


static class Feed
{
    public static function Create($type = null)
    {
        $type = (null === $type) ? 'AtomFeed': ucfirst($type).'Feed';
        $feed = (class_exists($type)) ? new $type(): null;
        return $feed;
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
    
    public static function Render()
    {
        $feed = Feed::Create();
        
        exit;
    }
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

    abstract public function AddItem(FeedItem $item)
    {
        $this->feedItems[] = $item;
    }
    
    abstract public function Render();
        
}


final class AtomFeed extends aFeed
{
    private $feedUuid;
    
    public function __construct()
    {
        $this->feedUrl = Utils::GenerateUri('feed');
        $this->feedUuid = Feed::GenerateUuid();
        parent::__construct();
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
        $this->permalink = $article->Get('permalink');
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