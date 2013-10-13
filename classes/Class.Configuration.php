<?php
/**
 * Class.Config
 *
 * @package SMPL\Config
 */

/**
 * Contains database configuration information and site settings getter method
 * 
 * @package Config
 */
 final class Config
{
    const DB_TYPE = 'MySql';
    const DB_HOST = 'localhost';
    const DB_NAME = 'smpl';
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_PREFIX = '';
    const USE_MODREWRITE = false;
    const USE_SSL = false;

    private static $database = null;

     /**
      * Empty private constructor to enforce "static-ness"
      * @return \Config
      */
    private function __construct() {}
    
    /**
     * Database factory method, establish database connection and then pass it
     * @param string $databaseType
     * @param string $host
     * @param string $name
     * @param string $username
     * @param string $password
     * @param string $prefix                    
     * @throws StrictException
     * @return Database
     */
    public static function Database($databaseType = null, $host = null, $name = null, $username = null, $password = null, $prefix = null)
    {
        // When no parameters are given, assume main database instance
        if (null === $databaseType) {
            $databaseType = Config::DB_TYPE.'Database';
            if (null === self::$database) {
                self::$database = new $databaseType(Config::DB_HOST, Config::DB_NAME, Config::DB_USER, Config::DB_PASS, Config::DB_PREFIX);
            }
            
            return self::$database;
        }
        else {
            $databaseType .= 'Database';
            if (is_a($databaseType, 'Database', true)) {
                throw new StrictException($databaseType . ' does not implement the Database interface.');
            }
            if (!isset($host)) {
                $host = Config::DB_HOST;
            }
            if (!isset($name)) {
                $name = Config::DB_NAME;
            }
            if (!isset($username)) {
                $username = Config::DB_USER;
            }
            if (!isset($password)) {
                $password = Config::DB_PASS;
            }
            if (!isset($prefix)) {
                $prefix = Config::DB_PREFIX;
            }
            return new $databaseType($host, $name, $username, $password, $prefix);
        }
    }

    /**
     * Generate side URI, may need to change
     * @param bool $domainOnly Flag to only send the domain of the site
     * @return string
     */
    public static function Site($domainOnly = false)
    {
        $host = (self::USE_SSL) ? 'https://' : 'http://';
        $host .= $_SERVER['HTTP_HOST'];
        $directory = dirname($_SERVER['SCRIPT_NAME']);
        $website = ($directory == '/') ? $host . '/' : $host . $directory . '/';
        
        if ($domainOnly) {
            return $host . '/';
        }
        else {
            return $website;
        }
    }

     /**
      * Gets system settings by name
      * @param string $name name of the system setting, matching 'name-hidden' in settings database
      * @throws WarningException
      * @return string
      */
    public static function Get($name)
    {
        $database = Config::Database();
        $result = $database->Retrieve()
            ->UsingTable("settings")
            ->Item("value-field")
            ->Match("name-hidden", $name)
            ->Send();
        
        $value = $result->Fetch();
        if (is_null($value)) {
            throw new WarningException("Could not find setting '{$value}'");
        }
        return $value['value-field'];
    }

}

?>