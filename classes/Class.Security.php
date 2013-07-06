<?php
/* SMPL Security Classes
//  A collection of static utility functions and constants 
//
//*/


static class Security
{
    $currentSessionValid = null;

    function GeneratePassword($length = 13, $useDashes = false, $useLowercase = true, $useUppercase = true, $useDigits = true, $useSymbols = true)
    {
    // Generates a strong password of N length containing at least one lower case letter,
    // one uppercase letter, one digit, and one special character. The remaining characters
    // in the password are chosen at random from those four sets.
    //
    // The available characters in each set are user friendly - there are no ambiguous
    // characters such as i, l, 1, o, 0, etc. This, coupled with the $add_dashes option,
    // makes it much easier for users to manually type or speak their passwords.
    //
    // Note: the $add_dashes option will increase the length of the password by
    // floor(sqrt(N)) characters.
    
        srand((float) microtime()); // Seed using time of execution 

        $sets = array();
        if ($useLowercase === true)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        
        if($useUppercase === true)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
    
        if($useDigits === true)
		        $sets[] = '23456789';
    
        if($useSymbols === true)
		        $sets[] = '!@#$%&*?';

    
        $all = '';
        $password = '';
    
        foreach($sets as $set)
        {
		        $password .= $set[array_rand(str_split($set))];
    		    $all .= $set;
        }

        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
		        $password .= $all[array_rand($all)];
    
        $password = str_shuffle($password);

        if(!$add_dashes)
		        return $password;

        $dash_len = floor(sqrt($length));
        $dash_str = '';
    
        while(strlen($password) > $dash_len)
        {
		        $dash_str .= substr($password, 0, $dash_len) . '-';
    		    $password = substr($password, $dash_len);
        }
    
        $dash_str .= $password;
        return $dash_str;
    }
    
    public static function GeneratePassphrase($useSpaces = true)
    {
        // An array of uncommon terms
        $bank = array(); // [MUSTCHANGE]
        $phrase = Security::GeneratePassword().' '.Security::GeneratePassword().' '.Security::GeneratePassword();
        return $phrase;
    }

    // Checks if the site should be using SSL. If it is, then force reload to HTTPS.
    public static function EnforceHttps()
    {
        //
        if (empty($_SERVER['HTTPS']) || $_SERVER['SERVER_PORT'] !== 443)
        {
            if (Configuration::SslCertificate())
            {
                header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit;            
            }
        }
    }
    
    // XSS Filter method, based on http://kohanaframework.org/3.0/guide/api/Security#xss_clean
    public static function FilterXss($data)
    {
        // * Handle arrays recursively 
        if (is_array($data) OR is_object($data))
        {
            foreach ($data as $key => $value)
            {
                $data[$key] = Security::FilterXss($value);
            }
            
            return $data;
        }
        
        $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        $data = htmlentities($data, ENT_QUOTES, 'UTF-8');

        // Remove all NULL bytes
        $data = str_replace("\0", '', $data); 
        // Fix &entity\n; 
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data); 
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data); 
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data); 
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8'); 
        // Remove any attribute starting with "on" or xmlns 
        $data = preg_replace('#(?:on[a-z]+|xmlns)\s*=\s*[\'"\x00-\x20]?[^\'>"]*[\'"\x00-\x20]?\s?#iu', '', $data); 
        // Remove javascript: and vbscript: protocols 
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data); 
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data); 
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data); 
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span> 
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#is', '$1>', $data); 
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#is', '$1>', $data); 
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#ius', '$1>', $data); 
        // Remove namespaced elements (we do not need them) 
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    
        do
        {
            // Remove really unwanted tags
            $old = $data; 
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data); 
        } while ($old !== $data);
    
        return $data;
    }
    
    /// AUTHENTICATION METHODS, VERIFYING WEB ACCESS

    // Check if a valid session is present.    
    public static function Authenticate()
    {
        if (null === $this->currentSessionValid)
        {
                // has a session already been started?
                if (session_status() !== PHP_SESSION_ACTIVE)
                    session_start();
                
                // User authentication period is still active, then update 
                if (isset($_SESSION[Configuration::Site().'_AUTH_PERIOD']) && $_SESSION[Configuration::Site().'_AUTH_PERIOD'] > Date::Flatten(Date::Create()))
                {
                    $_SESSION[Configuration::Site().'_AUTH_PERIOD'] = (int) Date::Flatten(Date::Create()) + 10000;
                    $this->currentSessionValid = true;
                }
                else
                { 
                    $this->currentSessionValid = false;
                    Security::DestroySession();
                }


        }
        
        return $this->currentSessionValid;
    }

    //Check if user has permissions to access content
    public static function HasAccessTo($level)
    {
        if(!Security::Authenticate())
            return false;
        
        switch ($level) {
            case 'system':
                return $_SESSION[Configuration::Site().'-AUTH-ACCESS_SYSTEM'];
                break;
            case 'users':
                return $_SESSION[Configuration::Site().'-AUTH-ACCESS_USERS'];
                break;
            case 'content':
                return $_SESSION[Configuration::Site().'-AUTH-ACCESS_CONTENT'];
                break;
            case 'blocks':
                return $_SESSION[Configuration::Site().'-AUTH-ACCESS_BLOCKS'];
                break;
            default:
                return false;
                break;
        }
       
    }    
    
    // Validate login information, and create an administrative session success
    public static function Login($username, $password)
    {
        $username = md5(Security::FilterXSS($username) );
        $password = md5(Security::FilterXSS($password) );

        $database = Database::Connect();
        $result = $database->Retrieve('users', '*',  "account-user_name-hash = '{$username}' AND account-password-hash = '{$username}'");
    
        if($value = $result->fetch_array(MYSQLI_ASSOC))
        {
            Security::CreateSession($value);  
        }
        else
        {
            Security::DestroySession();   
        }
    }

    public static function Logout()
    {
        Security::DestroySession();
    }

    private static function CreateSession($sessionData)
    {
        session_start();
        $_SESSION[Configuration::Site().'-AUTH-ID'] = $sessionData['id'];
        $_SESSION[Configuration::Site().'-AUTH-USERNAME'] = $sessionData['account-user_name-hash'];
        // Validate sessions for an hour
        $_SESSION[Configuration::Site().'-AUTH-PERIOD'] = (int) Date::Flatten(Date::Create()) + 10000;
        
        $_SESSION[Configuration::Site().'-AUTH-ACCESS_SYSTEM'] = $sessionData['permissions-access_system-checkbox'];
        $_SESSION[Configuration::Site().'-AUTH-ACCESS_USERS'] = $sessionData['permissions-access_users-checkbox'];
        $_SESSION[Configuration::Site().'-AUTH-ACCESS_CONTENT'] = $sessionData['permissions-access_content-checkbox'];
        $_SESSION[Configuration::Site().'-AUTH-ACCESS_BLOCKS'] = $sessionData['permissions-access_blocks-checkbox'];
        
        // Possibly use Cookies instead of Sessions (less server overhead, more security concerns)
        /*        
        $expire = time() + 3600;
        setcookie(Configuration::Site().'_AUTH_ID', $id, $expire);
        setcookie(Configuration::Site().'_AUTH_USERNAME', $username, $expire);
        //*/
    }
    
    private static function DestroySession()
    {
        unset($_SESSION[Configuration::Site().'_AUTH_ID']);
        unset($_SESSION[Configuration::Site().'_AUTH_USERNAME']);
        unset($_SESSION[Configuration::Site().'_AUTH_PERIOD']);
        unset($_SESSION[Configuration::Site().'-AUTH-ACCESS_SYSTEM']);
        unset($_SESSION[Configuration::Site().'-AUTH-ACCESS_USERS']);
        unset($_SESSION[Configuration::Site().'-AUTH-ACCESS_CONTENT');
        unset($_SESSION[Configuration::Site().'-AUTH-ACCESS_BLOCKS']);
        session_destroy();
        
        // Possibly use Cookies instead of Sessions (less server overhead, more security concerns)
        /*
        setcookie(Configuration::Site().'_AUTH_ID', '', time() - 3600);
        setcookie(Configuration::Site().'_AUTH_USERNAME', '', time() - 3600);
        //*/
    } 

}

?>
