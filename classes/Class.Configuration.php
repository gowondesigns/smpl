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
    private $modRewriteOn = false;


    public static function Site()
    {
        $host = 'http://'.$_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['SCRIPT_NAME']);
        $website = $directory == '/' ? $host.'/' : $host.$directory.'/';
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
        //return the value from the Database query
        
            /* SITE SETTINGS - grab site settings from database
function s($var) {
	global $site_settings;
	if (!$site_settings){
		$query = 'SELECT name,value FROM '._PRE.'settings';
		$result = mysql_query($query);
		while ($r = mysql_fetch_assoc($result)) {
			$site_settings[$r['name']] = $r['value'];
		}
	}
	$value = $site_settings[$var];
	return $value;
}*/ 
    }
  
}

?>