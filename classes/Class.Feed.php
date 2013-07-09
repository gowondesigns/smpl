<?php
/* SMPL Feed Classes
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


static class Feed
{
    private static $langInstance = null;
    
    
    public static function Create($languageCode = null)
    {
        if (null === $this->langInstance)
        {
            $this->langInstance = new Language($languageCode);
        }

        return $this->langInstance;
    }
    
    public static function Reset($languageCode = null)
    {
        $this->langInstance = new Language($languageCode);
        return $this->langInstance;
    }
}


// Structure for all content elements
abstract class aFeed
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


class AtomFeed extends aFeed
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
    
    
    public function Tags()
    {
        return implode(', ', $this->tags);
    }
}
?>