<?php
/* SMPL Utils Classes
//  A collection of static utility functions and constants 
//
//*/


static class Utils
{
      
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
    
    // Simple Hashing function (primarily for generating permalinks)
    public static function Hash($string)
    {
        $dictionary  = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $base  = strlen($dictionary);
        $hash = null;           
        $id = null;
        
        for($i = 0; $i < strlen($string); $i++)
        {
            $id .= ord($string[$i]);
        }

        do 
        {
            $hash = $dict[($id%$base)].$hash;
        } 
        while ($id = floor($id/$base));

        return $hash;
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
    public static function GenerateUri($stub) // $stub isn't used, it's just here to force at least one argument passed 
    {
        $assets = array_slice(func_get_args());
        $uri = Configuration::Site();
        $uri .= (Configuration::CheckSetting('modRewriteFlag')) ? '': '?';
        $uri .= implode('/', $assets).'/';
        
        return $uri;
    }
    
    // Append items onto the current URI to modify the current assets in some way
    public static function AppendUri($asset)
    {
        $optionalAssets = array_slice(func_get_args(), 1);
        $uri = Configuration::Site();
        
        if (Configuration::CheckSetting('modRewriteFlag'))
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
    
    
    // Method to initiate all automatic actions
    public static function Cron()
    {
     /*   function updateContent() {
$query="UPDATE ".db('prefix')."content SET published='1' WHERE published='2' AND date<='".timeStamp()."'";
mysql_query($query);
$query="UPDATE ".db('prefix')."content SET published='0', unpublish='0' WHERE unpublish='1' AND enddate<='".timeStamp()."'";
mysql_query($query);
}
*/ 
    }

}

?>
