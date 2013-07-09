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
    public function Create($insertToTables, $insertItems, $insertExtra = null);   // Create (Insert) Queries
    public function Retrieve($selectFromTables, $selectItems = null,  $selectWhereClause = null, $selectExtra = null); // Retrieve (Select) Queries
    public function Update($updateToTables, $updateItems, $updateWhereClause, $updateExtra = null);   // Update Queries
    public function Delete($deleteFromTables, $deleteWhereClause, $selectExtra = null);   // Delete Queries
}

abstract class aDatabase
{
    protected $status = null;
    protected $host;
    protected $name;
    protected $username;
    protected $password;
    protected $prefix;
    
    public function __construct($host, $name, $username, $password, $prefix)
    {
        $this->host = $host;
        $this->name = $name;
        $this->username = $username;
        $this->password = $password;
        $this->prefix = $prefix;
    }
    
    
    public function GetStatus()    // This type of method may not be necessary
    {
        return $this->status;
    }
}

class MySqlDatabase extends aDatabase implements iDatabase
{
    private $mysqli;


    public function __construct($host, $name, $username, $password, $prefix)
    {
        parent::__construct($host, $name, $username, $password, $prefix);
        
        $this->mysqli = new mysqli($host, $username, $password, $host);
        if ($this->mysqli->connect_errno)
        {
            die("Connect failed: %s\n", $mysqli->connect_error); // [MUSTCHANGE]
        }
        
    }

    public function Query($queryString)
    {
        // Return mysqli result object: http://www.php.net/manual/en/class.mysqli-result.php
        return $this->mysqli->query($queryString);
    }
    // $insertToTables can be a single string, or an array of strings for the tables
    // $insertItems must be an array
    public function Create($insertToTables, $insertItems, $insertExtra = null) 
    {
        $insertString = array();
        if (is_array($insertToTables))
        {
            foreach ($insertToTables as $key => $value)
                $insertToTables[$key] = $this->prefix.$value;
        
            $tableString = implode(', ', $insertToTables);  
        }
        else
            $tableString = $this->prefix.$insertToTables;

        
        for ($i = 0; $i < count($insertItems); $i++)
        {
            if (is_numeric($value))
                $insertString[] = $insertItems[$i];
            else
                $insertString[] = "'{$insertItems[$i]}'";
        }
        
        $insertString = implode(', ', $insertItems);
            
        // Method will return TRUE on success, FALSE on failure
        return $this->mysqli->query('INSERT INTO '.$tableString.' VALUES (SET '.$insertString.') '.$insertExtra);

    }
    
    // Retrieve specific item (not meant for queries that return multiple results)
    public function Retrieve($selectFromTables, $selectItems = null,  $selectWhereClause = null, $selectExtra = null)
    {
        if (is_array($selectFromTables))
        {
            foreach ($selectFromTables as $key => $value)
                $selectFromTables[$key] = $this->prefix.$value;
        
            $tableString = implode(', ', $selectFromTables);  
        }
        else
            $tableString = $this->prefix.$selectFromTables;
        
        if (null === $selectItems)
        {
            $selectItems = '*';
        }
        
        if (isset($selectWhereClause))
        {
            $selectWhereClause = ' WHERE '.$selectWhereClause;
        }
        
        // Return mysqli result object: http://www.php.net/manual/en/class.mysqli-result.php
        return $this->mysqli->query('SELECT '.$selectItems.' FROM '.$tableString.$selectWhereClause.' '.$selectExtra);
    }
    
    public function Update($updateToTables, $updateItems, $updateWhereClause, $updateExtra = null)    // $updateItems must be an array
    {
        $i = 0;
        $updateString = array();
        
        if (is_array($updateToTables))
        {
            foreach ($updateToTables as $key => $value)
                $updateToTables[$key] = $this->prefix.$value;
        
            $tableString = implode(', ', $updateToTables);  
        }
        else
            $tableString = $this->prefix.$updateToTables;
        
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
            
        
        // Method will return TRUE on success, FALSE on failure
        return $this->mysqli->query('UPDATE '.$tableString.' SET '.$updateString.' WHERE '.$updateWhereClause.' '.$selectExtra);
    }
    
    public function Delete($deleteFromTables, $deleteWhereClause, $selectExtra = null)
    {
        if (is_array($deleteFromTables))
        {
            foreach ($deleteFromTables as $key => $value)
                $deleteFromTables[$key] = $this->prefix.$value;
        
            $tableString = implode(', ', $deleteFromTables);  
        }
        else
            $tableString = $this->prefix.$deleteFromTables;
        
        // Method will return TRUE on success, FALSE on failure
        return $this->mysqli->query('DELETE FROM '.$tableString.' WHERE '.$selectWhereClause.' '.$selectExtra);
    }

}

?>
