<?php
/* SMPL Configuration Class
// 
//
//*/


static class Configuration
{
    private $database = array(
        'type' => 'MySql',
        'host' => 'localhost',
        'name' => 'smpl',
        'username' => 'root',
        'password' => '',
        'prefix' => '');
        
    private $modRewrite = false;
    private $sslCertificate = false;


    public static function Site($domainOnly = false)                         // [MUSTCHANGE]
    {

        $host = (Configuration::SslCertificate()) ? 'https://': 'http://';
        $host .= $_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['SCRIPT_NAME']);
        $website = ($directory == '/') ? $host.'/': $host.$directory.'/';
        
        if ($domainOnly)
            return $host.'/';
        else
            return $website;
    }
    
    // Return database configuration information
    public static function Database($item = null)
    {
        if (null === $item)
            return $this->database;
        else
            return $this->database[$item];
    }
    
    // Return data from the settings DB table
    public static Get($settingName)
    {
        $database = Database::Connect();
        $result = $database->Retrieve('settings', 'value-field',  "name-hidden = '{$settingName}'");
        $value = $result->fetch_array(MYSQLI_NUM);
        
        return $value[0]
    }
    
    public static ModRewrite()
    {
        return $this->modRewrite;
    }
    
    
    public static SslCertificate()
    {
        return $this->sslCertificate;
    }
      
}

?>