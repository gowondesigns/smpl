<?php
/* SMPL Utils Classes
//  A collection of static utility functions and constants 
//
//*/


class Utils
{
    // const $codeset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    // readable character set excluded (0,O,1,l)
    // Simple Permalink mask for unique IDs, Base 58
    private static $permalinkBase = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
          
    // Convert strings to SEO-friendly strings, with optional flags to remove integers
    public static function Munge($string, $length = 30, $removeIntegers = false)
    {
        // Remove noise phrases: "the, and, a, an, or, my, our, us" etc.
        
        // Remove everything but standard ASCII characters "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWYXZ1234567890"
        $mung = trim($string);
        
        // Convert accented and international characters  
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y', 'R'=>'R', 'r'=>'r',
        );
   
        $mung = strtr($mung, $table);
        
        // Remove integers from mung string if flagged
        if ($removeIntegers)
        {
            $key = '/[^A-Za-z \-]+/';
        }
        else
        {
            $key = '/[^A-Za-z0-9 \-]+/';
        }
        
        $replace = array($key, '/(\s|\-)+/', '/^-+|-+$/');
        $with = array('', '-', '');
        $mung = preg_replace($replace, $with, $mung);

        // Truncate string to the defined length
        $mung = Utils::Truncate($mung, $length);
        
        // Lower all cases
        $mung = strtolower($mung);
        return $mung;
    }

    /* Discussion: How should truncate behave?
    1) Either truncate by length limit OR truncate by delimiter, NOT BOTH
    2) Truncate by delimiter OR length limit, WHICHEVER COMES FIRST 
    //*/
    public static function Truncate($string, $stringLimit = 30, $breakpointDelimeter = null)
    {
        if(null === $breakpointDelimeter)
        {
            if(strlen($string) <= $stringLimit)
            {
                return $string;
            }
            
            $string = substr($string, 0, $stringLimit);
            if(false !== ($breakpoint = strrpos($string, ' ')))
            {
                $string = substr($string, 0, $breakpoint);
            }
        }
        elseif(false !== ($breakpoint = strrpos($string, $breakpointDelimeter)))
        {
            $string=substr($string, 0, $breakpoint);
        }
                
        return $string;
    }
    
    
    
    // Returns current URI in an array of assets
    public static function ParseUri()
    {
        $assets = array();
        $currentUri = $_SERVER['QUERY_STRING'];
        
        // Need to make sure whether or not '?' is included in the Query String
        // If so, must remove it before exploding the string
        $assets = explode('/', $currentUri);
        
        return $assets;
    }
    
    // System-generated URIs  
    public static function GenerateUri($stub) // $stub isn't used, it's just here to force behavior that at least one argument passed 
    {
        $assets = array_slice(func_get_args());
        $uri = Configuration::Site();
        $uri .= (Configuration::ModRewrite()) ? '': '?';
        $uri .= implode('/', $assets).'/';
        
        return $uri;
    }
    
    // Append items onto the current URI to modify the current assets in some way
    public static function AppendUri($asset)
    {
        $optionalAssets = array_slice(func_get_args(), 1);
        $uri = Configuration::Site();
        
        if (Configuration::ModRewrite())
        {
            $uri .= $asset.'/';
        }
        else
        {
            $uri .= '?'.$asset.'/';
        }
                 
        $uri .= implode('/', $optionalAssets);
        return $uri;
    }

    public static function Pagination($setAmount, $currentPosition, $showPageNumbers = false, $totalPageNumbers = null, $seperator = "&#124;")
    {
        $l = LanguageFactory::Create(); // Grab language data
        $html = '<div class="smpl-pagination"><ul>';
        
        if($currentPosition > $setAmount || $currentPosition < 1)
        {
            die('Index Error');// Replace with DEBUG METHODS [MUSTCHANGE]
        }

        if($currentPosition > 1)
        {
            $html .= '<li id="previous"><a href="'.Utils::GenerateUri($l->Phrase('Previous')).'" title="'.$l->Phrase('Previous').'">'.$l->Phrase('Previous').'</a></li>';
        }        

        if($currentPosition < $setAmount)
        {
            $html .= '<li id="previous"><a href="'.Utils::GenerateUri($l->Phrase('Next')).'" title="'.$l->Phrase('Next').'">'.$l->Phrase('Next').'</a></li>';
        }        
        $html .= '<li id="seperator">'.$seperator.'</li>';
        
        
        
        $html .= '</ul></div>';
        
        return $html;
    }

    public static function PermalinkEncode($id)
    {
        $base = str_split(self::$permalinkBase);
        $baseNum = count($base);
        $salt = intval( Configuration::Get('permalinkSalt'));

        // Shift the order of base by the salt amount. + = Shift Left, - = Shift Right
        $baseOffset = $salt % $baseNum;
      
        if ($salt < 0)
        {
            // Example.
            // base = "ABCDEFGHIJ"
            // 1st = array_slice($base, -3) = "HIJ" 
            // 2nd = array_slice($base, 0, 10-3 = 7) = "ABCDEFG"
            // newbase = 1st.2nd = "HIJABCDEFG"
            if($baseOffset == 0)
                $baseOffset = -1;  
            $first = array_slice($base, $baseOffset);
            $second = array_slice($base, 0, ($baseNum - $baseOffset));
        }
        else
        {
            // Example.
            // base = "ABCDEFGHIJ"
            // 1st = array_slice($base, 3) = "DEFGHIJ" 
            // 2nd = array_slice($base, 0, 3) = "ABC"
            // newbase = 1st.2nd = "DEFGHIJABC"
            if($baseOffset == 0)
                $baseOffset = 1;   
            $first = array_slice($base, $baseOffset);
            $second = array_slice($base, 0, $baseOffset);
        }

        $base = array_merge($first, $second);
        $r = $id % $baseNum ;
        $hash = $base[$r];
        $q = floor($id / $baseNum);
        while ($q)
        {
              $r = $q % $baseNum;
              $q = floor($q / $baseNum);
              $hash = $base[$r].$hash;
        }
        return $hash;
    }

    public static function PermalinkDecode($hash)
    {
        $base = str_split(self::$permalinkBase);
        $baseNum = count($base);
        $salt = intval( Configuration::Get('permalinkSalt'));

        // Shift the order of base by the salt amount. + = Shift Left, - = Shift Right
        $baseOffset = $salt % $baseNum;
      
        if ($salt < 0)
        {
            // Example.
            // base = "ABCDEFGHIJ"
            // 1st = array_slice($base, -3) = "HIJ" 
            // 2nd = array_slice($base, 0, 10-3 = 7) = "ABCDEFG"
            // newbase = 1st.2nd = "HIJABCDEFG"
            if($baseOffset == 0)
                $baseOffset = -1;  
            $first = array_slice($base, $baseOffset);
            $second = array_slice($base, 0, ($baseNum - $baseOffset));
        }
        else
        {
            // Example.
            // base = "ABCDEFGHIJ"
            // 1st = array_slice($base, 3) = "DEFGHIJ" 
            // 2nd = array_slice($base, 0, 3) = "ABC"
            // newbase = 1st.2nd = "DEFGHIJABC"
            if($baseOffset == 0)
                $baseOffset = 1;   
            $first = array_slice($base, $baseOffset);
            $second = array_slice($base, 0, $baseOffset);
        }

        $base = array_merge($first, $second);
        $limit = strlen($hash);
        $id = strpos($base, $hash[0]);
        
        for($i = 1; $i < $limit; $i++)
        {
            $id = $baseNum * $id + strpos($base, $hash[$i]);
        }
        
        return $id;
    }
    
    public static function Strip($data)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si',   // Strip out javascript 
            '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags 
            '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly 
            '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA 
        ); 
        $data = preg_replace($search, ' ', $data);
        $string = trim(preg_replace('/ {2,}/', ' ', $data)); 
        return $data; 
    } 
    
}

?>
