<?php
/* SMPL Configuration Class
// 
//
//*/


static class Configuration
{
    private $databaseType = 'MySql';
    private $databaseHost = 'localhost';
    private $databaseName = 'smpl';
    private $databaseUsername = 'root';
    private $databasePassword = '';
    private $databasePrefix = '';
    private $modRewriteOn = false;


    public static function Site()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['SCRIPT_NAME']);
        $website = $directory == '/' ? $host.'/' : $host.$directory.'/';
        return $website;
    }
    
    public static function Credentials()
    {
        return array(
            'host' => $this->databaseHost,
            'name' => $this->databaseName,
            'username' => $this->databaseUsername,
            'password' => $this->databasePassword,
            'prefix' => $this->databasePrefix);
    }
    
    public static function UriQuery()
    {
    }
}

?>