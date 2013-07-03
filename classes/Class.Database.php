<?php
/* SMPL Database Classes
// 
//
//*/

    
static class Database
{
    private static $mainDatabaseInstance = null;
    
    // Database factory method, establish database connection and then pass it
    public static function Connect($databaseType = null)
    {
        $configurations = Configuration::DatabaseInfo();
        
        // If no database type is specified, assume that the main database object is being used
        if (null === $databaseType)
        {
            $databaseType = $configurations['type'].'Database';
            if (null === $this->mainDatabaseInstance)
            {
                $this->mainDatabaseInstance = new $databaseType($configurations['host'], $configurations['name'], $configurations['username'], $configurations['password'], $configurations['prefix']);
            }
            
            return $this->mainDatabaseInstance;
        }
        else // Otherwise assume a unique database instance
        {
            $databaseType .= 'Database';
            $optionalAssets = array_slice(func_get_args(), 1);
            
            if (isset($optionalAssets))
            {
                $configurations['host'] = $optionalAssets[0];
                $configurations['name'] = $optionalAssets[1];
                $configurations['username'] = $optionalAssets[2];
                $configurations['password'] = $optionalAssets[3];
                $configurations['prefix'] = $optionalAssets[4];
            }
            
            return new $databaseType($configurations['host'], $configurations['name'], $configurations['username'], $configurations['password'], $configurations['prefix']);
        }
    }

}


interface iDatabase
{
    public function Query($queryString);    // Custom Queries that cant be handled by the basic handlers
    public function Create($insertToTable, $insertItems);   // Create (Insert) Queries
    public function Retrieve($selectFromTable, $selectItems = null,  $selectWhereClause = null, $selectExtra = null); // Retrieve (Select) Queries
    public function Update($updateToTable, $updateItems, $updateWhereClause, $updateExtra = null);   // Update Queries
    public function Delete($deleteFromTable, $deleteWhereClause, $selectExtra = null);   // Delete Queries
}

abstract class aDatabase
{
    protected $databaseStatus = null;
    protected $databaseHost;
    protected $databaseName;
    protected $databaseUsername;
    protected $databasePassword;
    protected $databasePrefix;
    
    public function __construct($host, $name, $username, $password, $prefix)
    {
        $this->databaseHost = $host;
        $this->databaseName = $name;
        $this->databaseUsername = $username;
        $this->databasePassword = $password;
        $this->databasePrefix = $prefix;
    }
    
    
    public function GetStatus()
    {
        return $this->databaseStatus;
    }
}

class MySqlDatabase extends aDatabase implements iDatabase
{

    public function __construct($host, $name, $username, $password, $prefix)
    {
        parent::__construct($host, $name, $username, $password, $prefix);
        
        $this->databaseStatus = mysql_connect($host, $username, $password) or die('MySQL Connection Error'); // Establish Connection, save results      // [MUSTCHANGE]
    }

    public function Query($queryString)
    {
        $data = mysql_fetch_array( mysql_query($queryString));
        // restructure elements
        return $data;
    }
    
    public function Create($insertToTable, $insertItems) // $insertItems must be an array
    {
        $insertString = array();
        $insertToTable = Configuration::DatabaseInfo('prefix').$insertToTable;
        
        for ($i = 0; $i < count($insertItems); $i++)
        {
            if (is_numeric($value))
            {
                $insertString[] = $insertItems[$i];
            }
            else
            {
                $insertString[] = "'{$insertItems[$i]}'";
            }
        }
        
        $insertString = implode(', ', $insertItems);
            
        mysql_query('INSERT INTO '.$insertToTable.' VALUES (SET '.$insertString.')');
    }
    
    // Retrieve specific item (not meant for queries that return multiple results)
    public function Retrieve($selectFromTable, $selectItems = null,  $selectWhereClause = null, $selectExtra = null)
    {
        $selectFromTable = Configuration::DatabaseInfo('prefix').$selectFromTable;
        
        if (null === $selectItems)
        {
            $selectItems = '*';
        }
        
        if (isset($selectWhereClause))
        {
            $selectWhereClause = ' WHERE '.$selectWhereClause;
        }
        
        $data = mysql_fetch_array( mysql_query('SELECT '.$selectItems.' FROM '.$selectFromTable.$selectWhereClause.' '.$selectExtra));
        // restructure elements
        return $data;
    }
    
    public function Update($updateToTable, $updateItems, $updateWhereClause, $updateExtra = null)    // $updateItems must be an array
    {
        $i = 0;
        $updateString = array();
        $updateToTable = Configuration::DatabaseInfo('prefix').$updateToTable;
        
        foreach ($updateItems as $key => $value)
        {
            $updateString[$i] = $key.' = ';
            if (is_numeric($value))
            {
                $updateString[$i++] .= $value;
            }
            else
            {
                $updateString[$i++] .= "'{$value}'";
            }
        }
        
        $updateString = implode(', ', $updateString);
            
        mysql_query('UPDATE '.$updateToTable.' SET '.$updateString.' WHERE '.$updateWhereClause.' '.$selectExtra);
    }
    
    public function Delete($deleteFromTable, $deleteWhereClause, $selectExtra = null)
    {
        $deleteFromTable = Configuration::DatabaseInfo('prefix').$deleteFromTable;
        mysql_query('DELETE FROM '.$deleteFromTable.' WHERE '.$selectWhereClause.' '.$selectExtra);
    }

}

?>
