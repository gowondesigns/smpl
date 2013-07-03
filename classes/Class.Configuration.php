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


    public static function Site()                         // [MUSTCHANGE]
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['SCRIPT_NAME']);
        $website = ($directory == '/') ? $host.'/': $host.$directory.'/';
        return $website;
    }
    
    public static function DatabaseInfo($item = null)
    {
        if (null === $this->langInstance)
        {
            return return $this->database;
        }
        else
        {
            return $this->database[$item];
        }
    }
    
    public static CheckSetting($settingName)
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