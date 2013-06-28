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
        
        // Remove integers from mungek
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

        // Truncate string to the length defined
        preg_match('/^.{0,' . $length. '}(?:.*?)\b/iu', $mung, $matches);
        $mung = $matches[0];
        
        // Lower all cases
        $mung = strtolower($mung);
        return $mung;
    }

}
?>