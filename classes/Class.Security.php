<?php
/* SMPL Security Classes
//  A collection of static utility functions and constants 
//
//*/


static class Security
{
      
     public static function PassphraseGenerator($useSpaces = true)
     {
        // An array of uncommon terms
        $bank = array(); // [MUSTCHANGE]
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

}

?>
