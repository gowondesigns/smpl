<?php
/* SMPL Language Classes
// 
// Example Use:
// $l = LanguageFactory::Create("en-US");
// echo $l->Phrase("Author");
//
//*/


static class LanguageFactory
{
    private static $LangInstance = null;
    
    
    public static function Create($languageCode = null)
    {
        if (null === $LangInstance) {
            $LangInstance = new Language($languageCode);
        }

        return $LangInstance;
    }
    
    public static function Reset($languageCode = null)
    {
        $LangInstance = new Language($languageCode);
        return $LangInstance;
    }
}


class Language
{
    private $language;
    private $languageCode;
    private $languagePhrases = array();

    
    public function __construct($languageCode)
    {
        // This default array represents the official set of phrases that must be guaranteed by translations
        $this->languagePhrases = array(
            "Author" => "Author",
            "Comment" => "Comment",
            "Page" => "Page",
            "Date" => "Date");

        // Language files are included from the implied location (languages/) folder
        if (isset($languageCode))
        {
            include("languages/lang.".$languageCode.".php");
            /* The language file is just a container including these three variables
            // $SMPL_LANG_DESC
            // $SMPL_LANG_CODE
            // $SMPL_LANG_PHRASES
            //*/
            
            $this->language = $SMPL_LANG_DESC;
            $this->languageCode = $SMPL_LANG_CODE;
            foreach ($SMPL_LANG_PHRASES as $key => $value)
            {
                $this->Update($key, $value);
            }
            
        }
        // If no language is set, default to US-English
        else
        {
            $this->language = "US English";
            $this->languageCode = "en-US";
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
            die(); // REPLACE with Exception: Phrase Does not exist in <Language>-<Language Code>
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
