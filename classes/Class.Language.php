<?php
/* SMPL Language Classes
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


class LanguageFactory
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


class Language
{
    private $language = "US English";
    private $languageCode = "en-US";
    
    // This default array represents the official set of phrases that must be guaranteed by translations
    private $languagePhrases = array(
        // SMPL-generated URL phrases
        "api" => "api",
        "feed" => "feed",
        "admin" => "admin",
        "articles" => "articles",
        "categories" => "categories",
  
        // SMPL Admin Panel phrases
        "Administration" => "Administration",
        "Logout" => "Logout",
        "EditContent" => "Edit Content",
        "Author" => "Author",
        "Comment" => "Comment",
        "Page" => "Page",
        "Date" => "Date"
        );

    
    public function __construct($languageCode)
    {
        // If the language code is 
        if (isset($languageCode))
        {
            include("smpl-languages/lang.".$languageCode.".php");
            if(!isset($SMPL_LANG_DESC) || !isset($SMPL_LANG_CODE) || !isset($SMPL_LANG_PHRASES))
            {
                die('Invalid Language File'); // [MUSTCHANGE]
            }
            
            $this->language = $SMPL_LANG_DESC;
            $this->languageCode = $SMPL_LANG_CODE;
            foreach ($SMPL_LANG_PHRASES as $key => $value)
            {
                $this->Update($key, $value);
            }
            
        }
    }

    // Get the information on the current language    
    public function Info()
    {
        return array(               
            "language" => $this->language,
            "code" => $this->languageCode);
    }

    // Use language phrase    
    public function Phrase($key)
    {
        if (isset($this->languagePhrases[$key]))
        {
            return $this->languagePhrases[$key];
        }
        else
        {
            return false; // REPLACE with Exception: Phrase Does not exist in <Language>-<Language Code>
        }
    }
    
    // Add/Update/Remove the content of a particular phrase, the changes are global    
    public function Update($key, $value)
    {
        // If the value is set to NULL, then the key will be removed from the phrase list
        if(null === $value)
        {
            if(isset($this->languagePhrases[$key]))
            {
                unset($this->languagePhrases[$key]);
            }
        }
        // The default behavior is to replace the value an entry to the phrase list, or add a new phrase if it doesn't already exist 
        else
        {
            $this->languagePhrases[$key] = $value;
        }
    }  

}

?>
