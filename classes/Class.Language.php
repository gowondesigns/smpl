<?php
/**
 * Class.Language
 * @package SMPL\Language
 */

/**
 * A set of tools to provide language sets for the system to use 
 * @package Language
 */
class Language
{
    /**
     * Main instance of the Language set used throughout the system
     * @var LanguageSet $langInstance
     */
    private static $langInstance = null;
    
    /**
     * LanguageSet factory
     * @param string $languageCode Should correspond to to a language file 'smpl-languages/lang.<$languageCode>.php'
     * @return LanguageSet     
     */    
    public static function Create($languageCode = null)
    {
        if (isset($languageCode)) {
            return new LanguageSet($languageCode);
        }
        if (null === self::$langInstance) {
            $languageCode = Config::Get("languageDefault");
            Debug::Message('Initializing system language to: ' . $languageCode);
            self::$langInstance = new LanguageSet($languageCode);
        }
        return self::$langInstance;
    }
    
    /**
     * URI Hook to dynamically change the language     
     */    
    public static function Hook()
    {
        $key = array_search('lang', Content::Uri());
        self::Reset(Content::Uri()[($key + 1)] );
    }
    
    /**
     * Factory to reset or set new main LanguageSet
     * @param string $languageCode Should correspond to to a language file 'smpl-languages/lang.<$languageCode>.php'
     * @return LanguageSet     
     */ 
    public static function Reset($languageCode = null)
    {
        if (null === $languageCode) {
            $languageCode = Config::Get("languageDefault");
        }
        Debug::Message('Resetting system language to: ' . $languageCode);
        self::$langInstance = new LanguageSet($languageCode);
        return self::$langInstance;
    }
}

/**
 * Container of all translations for a given language. Served by Language factory. 
 * @package Language\LanguageSet
 */
class LanguageSet
{
    /**
     * Main instance of the Language set used throughout the system
     * @var string $language
     */
    private $language = null;
   
    /**
     * Main instance of the Language set used throughout the system
     * @var string $languageCode
     */
    private $languageCode = null;
    
    /**
     * list of all the translated phrases in the set.
     * @var array $languagePhrases
     */
    private $languagePhrases = array();

    /**
     * Initialize language phrases to english
     * @param string $languageCode Should correspond to to a language file 'smpl-languages/lang.<$languageCode>.php'
     * @return \LanguageSet     
     */ 
    public function __construct($languageCode)
    {
        // Initialize to US English
        // This default array represents the official set of phrases that must be guaranteed by translations
        $this->language = "US English";
        $this->languageCode = "en-US";
        $this->languagePhrases = array(
            // SMPL-generated URL phrases
            "api" => "api",
            "feed" => "feed",
            "articles" => "articles",
            "pages" => "pages",
            "permalink" => "link",
            //"categories" => "categories",
            
            // Control Panel - General Elements
            "controlPanel" => "Control Panel",
            "welcome" => "Welcome, ",
            "systemSettings" => "System Settings",
            "apiSettings" => "API Settings",
            "users" => "Users",
            "categories" => "Categories",
            "content" => "Content",
            "spaces" => "Spaces",
            "blocks" => "Blocks",
            "createNew" => "Create New",
            "purge" => "Purge",
            
            // Control Panel - Confirm Delete
            "confirmDelete" => "Confirm Deletion",
            "deleteNotice" => "Are you sure you want to delete this?",
            "delete" => "Delete",
            
            // Control Panel - Confirm Purge
            "confirmPurge" => "Purge Content",
            "purgeNotice" => "Delete all content with a <strong>publish date before</strong> selected date:",
            "onlyArticles" => "Only delete Articles",
            
            // Control Panel - Logout Elements
            "logout" => "Logout",
            "logoutNotice" => 'You have successfully logged out. You are being redirected to <a href="?admin/login/">login.html</a>.<br/>If you are not redirected after a few seconds, please click on the link above.',
            
            // Control Panel - Login Elements
            "login" => "Login",
            "loginMsg" => "You are not authorized to access this page. Please log in to continue.",
            "username" => "Username",
            "password" => "Password",
            
            // Control Panel - General Form Elements
            "date" => "Date",
            "time" => "Time",
            "edit" => "Edit",
            "previous" => "Prev",
            "next" => "Next",
            "options" => "Options",
            "title" => "Title",
            "status" => "Status",
            "cancel" => "Cancel",
            "reset" => "Reset",
            "submit" => "Submit",
           
            // Control Panel - API
            //"api" => "API",
            "api-token-field" => "API Token",
            "api-description-field" => "Description",
            "api-cnonce-field" => "CNONCE Token",
            "group-access_database-bool" => "Access to database",
            "group-access_system-bool" => "Access to system settings",
            "group-access_users-bool" => "Access to users",
            "group-access_content-bool" => "Access to content",
            "group-access_blocks-bool" => "Access to blocks",
            
            // Control Panel - Users
            "user" => "User",
            "account-user_name-hash" => "Username",
            "account-password-hash" => "Password",
            "account-name-field" => "Name",
            "account-email-field" => "Email Address",
          
            // Control Panel - Categories
            // Control Panel - Spaces
            "category" => "Category",
            "space" => "Space",
            "title-field" => "Title",
           	"title_mung-field" => "Search Engine Friendly Title",
           	"publish_flag-bool" => "Published",
            
            // Control Panel - Content
            "content-title-field" => "Title",
            "content-title_mung-field" => "Search Engine Friendly Title",
            "meta-static_page_flag-bool" => "Content is Static Page",
            "meta-default_page_flag-bool" => "Content is Default Page",
            "meta-category-set" => "Category",
            "meta-author-set" => "Author",
            "meta-date-date" => "Content Date",
            "content-body-text" => "Body",
            "content-tags-field" => "Tags",
            "publish-publish_flag-set" => "Publish Status",
            "publish-publish_date-date" => "Publish Date",            
            "publish-unpublish_flag-bool" => "Set Unpublish Date",
            "publish-unpublish_date-date" => "Unpublish Date",
            
            // Control Panel - Blocks
            "block" => "Block",
            "meta-space-set" => "Space",
            "meta-priority-set" => "Block Priority",
            "meta-redirect_flag-set" => "Redirect to External File",                    
            "meta-redirect_location-field" => "External File Location (in /smpl-includes/)",    
               
            // Control Panel - Info Pane
            "cms" => "Content Management System",
            "infoHtml" => 'Bug reports, suggestions: <a href="https://github.com/gowondesigns/smpl/issues" target="_blank">Please open a new issue.</a><br/>
            Check to see if there are any <a href="http://smply.it/" target="_blank">new releases</a> of SMPL available.<br/>
            <a href="http://smply.it/" target="_blank">SMPL</a> is licensed under the <a href="http://www.opensource.org/licenses/osl-3.0.php" target="_blank">Open Software License 3.0</a>.',
            
            // Control Panel - Statistics Pane
            "statistics" => "Statistics",
            "lastLogin" => "Last Login: ",
            "totalUsers" => "Total Users: ",
            "totalPages" => "Total Pages: ",
            "totalArticles" => "Total Articles: ",
            "pendingPublishes" => "Pending Publishes: "
        );
        
         
        if (isset($languageCode) && $languageCode != 'en-US') {
            include("smpl-languages/lang." . $languageCode . ".php");
            if (!isset($SMPL_LANG_DESC) || !isset($SMPL_LANG_CODE) || !isset($SMPL_LANG_PHRASES)) {
                trigger_error('Cannot find language file for language code"' . $languageCode . '" in ' . __DIR__ . '/smpl-languages/. Defaulting to en-US.', E_USER_WARNING);
            }
            else {
                $this->language = $SMPL_LANG_DESC;
                $this->languageCode = $SMPL_LANG_CODE;
                foreach ($SMPL_LANG_PHRASES as $key => $value) {
                    $this->Update($key, $value);
                }
            }
        }
    }
 
    /**
     * Get the full name of this LanguageSet
     * @return string     
     */    
    public function Name()
    {
        return $this->language;
    }

    /**
     * Get the language code of this LanguageSet
     * @return string     
     */
    public function Code()
    {
        return $this->languageCode;
    }

    /**
     * Get the translation of the input phrase/key
     * @param string $key      
     * @return string     
     */    
    public function Phrase($key)
    {
        if (isset($this->languagePhrases[$key])) {
            return $this->languagePhrases[$key];
        }
        else {
            trigger_error('Phrase \'' . $key . '\' does not exist in ' . $this->language . '\\' . $this->languageCode, E_USER_WARNING);
            return $key;
        }
    }
    
    /**
     * Update/Add translation of the phrase/key with $value. If null, the phrase/key is removed.
     * Changes only affect LanguageSet ar runtime, they are not committed to the language file.     
     * @param string $key
     * @param string $value                
     */      
    public function Update($key, $value)
    {
        // If the value is set to NULL, then the key will be removed from the phrase list
        if ($value === null) {
            if (isset($this->languagePhrases[$key])) {
                unset($this->languagePhrases[$key]);
            }
            else {
                trigger_error('Phrase \'' . $key . '\' does not exist in ' . $this->language . '-' . $this->languageCode, E_USER_WARNING);
            }
        }
        // The default behavior is to replace the value an entry to the phrase list, or add a new phrase if it doesn't already exist 
        else {
            Debug::Message('Adding phrase ' . $key . ':"' . $value . '" to '. $this->language . '-' . $this->languageCode);
            $this->languagePhrases[$key] = $value;
        }
    }  

}

?>
