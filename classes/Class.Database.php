<?php
/* SMPL Database Classes
// 
//
//*/

    
class Database
{
    private static $mainDatabaseInstance = null;
    
    // Database factory method, establish database connection and then pass it
    public static function Connect($databaseType = null, $host = null, $name = null, $username = null, $password = null, $prefix = null)
    {
        $configurations = Configuration::Database();
        
        // If no database type is specified, assume that the main database object is being used
        if (null === $databaseType)
        {
            $databaseType = $configurations['type'].'Database';
            if (null === self::$mainDatabaseInstance)
            {
                self::$mainDatabaseInstance = new $databaseType($configurations['host'], $configurations['name'], $configurations['username'], $configurations['password'], $configurations['prefix']);
            }
            
            return self::$mainDatabaseInstance;
        }
        else // Otherwise assume a unique database instance
        {
            $databaseType .= 'Database';
            $optionalAssets = array_slice(func_get_args(), 1);
            
            if($host)
                $configurations['host'] = $host;
            if($name)
                $configurations['name'] = $name;
            if($username)
                $configurations['username'] = $username;
            if($password)
                $configurations['password'] = $password;
            if($prefix)
                $configurations['prefix'] = $prefix;
            
            
            return new $databaseType($configurations['host'], $configurations['name'], $configurations['username'], $configurations['password'], $configurations['prefix']);
        }
    }
}


interface iDatabase
{
    //[MUSTCHANGE] MUST CREATE A CONNECT METHOD
    public static function NewQuery();    // Instatiate new query object
    public function Query($query);
    public function Create($insertToTables, $insertItems, $insertExtra = null);   // Create (Insert) Queries
    public function Retrieve($selectFromTables, $selectItems = null,  $selectWhereClause = null, $selectExtra = null); // Retrieve (Select) Queries
    public function Update($updateToTables, $updateItems, $updateWhereClause, $updateExtra = null);   // Update Queries
    public function Delete($deleteFromTables, $deleteWhereClause, $delectExtra = null);   // Delete Queries
}

class MySqlDatabase extends MySQLi implements iDatabase
{
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
        
        parent::__construct($host, $username, $password, $name);
    }

    public static function NewQuery()
    {
        return new MySqlDatabaseQuery();
    }
    
    public function Exec(iQuery $query)
    {
        if(!($query instanceof MySqlDatabaseQuery))
            throw new StrictException("Query object must be of type MySqlDatabaseQuery");
        return;
    }

    public function Query($query)
    {
        if(!($query instanceof MySqlDatabaseQuery))
            throw new StrictException("Query object must be of type MySqlDatabaseQuery");
        
        Debug::Message("MySqlDatabase\Query: ".$query->ToString());
        //$this->queries[] = $query;
        $this->real_query($query->ToString());
        return new MySqlDatabaseResult($this);
    }
        
    public function CustomQuery($query)
    {
        Debug::Message("MySqlDatabase\Query: ".$query);
        $this->queries[] = $query;
        $this->real_query($query);
        return new MySqlDatabaseResult($this);
    }
    // $insertToTables can be a single string, or an array of strings for the tables
    // $insertItems must be an array
    public function Create($insertToTables, $insertItems, $insertExtra = null) 
    {
        $cells = array();
        $values = array();
        if (is_array($insertToTables))
        {
            foreach ($insertToTables as $key => $value)
                $insertToTables[$key] = $this->prefix.$value;
        
            $tables = implode(', ', $insertToTables);  
        }
        else
            $tables = $this->prefix.$insertToTables;

        // Seperate and format all of the values
        foreach ($insertItems as $key => $value)
        {
            $cells[] = "`{$key}`";
            if (is_numeric($value))
                $values[] = $value;
            else
                $values[] = "'{$value}'";
        }
        
        $insertCells = implode(',', $cells);
        $insertValues = implode(',', $values);
            
        // Method will return TRUE on success, FALSE on failure
        return $this->CustomQuery("INSERT INTO {$tables} ({$insertCells}) VALUES ({$insertValues}) ".$insertExtra);

    }
    
    // Retrieve specific item (not meant for queries that return multiple results)
    public function Retrieve($selectFromTables, $selectItems = null,  $selectWhereClause = null, $selectExtra = null)
    {
        if (is_array($selectFromTables))
        {
            foreach ($selectFromTables as $key => $value)
                $selectFromTables[$key] = $this->prefix.$value;
        
            $tableString = '`'.implode('`,`', $selectFromTables).'`';  
        }
        else
            $tableString = '`'.$this->prefix.$selectFromTables.'`';
        
        if (null === $selectItems)
        {
            $selectItems = '*';
        }
        else if (is_array($selectItems))
        {
            foreach ($selectItems as $key => $value)
                $selectItems[$key] = $this->prefix.$value;
        
            $selectItems = '`'.implode('`,`', $selectItems).'`';  
        }
        else
            $selectItems = '`'.$this->prefix.$selectItems.'`';
        
        if (isset($selectWhereClause))
        {
            $selectWhereClause = ' WHERE '.$selectWhereClause;
        }

        return $this->CustomQuery('SELECT '.$selectItems.' FROM '.$tableString.$selectWhereClause.' '.$selectExtra);
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
        return $this->CustomQuery('UPDATE '.$tableString.' SET '.$updateString.' WHERE '.$updateWhereClause.' '.$updateExtra);
    }
    
    public function Delete($deleteFromTables, $deleteWhereClause, $deleteExtra = null)
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
        return $this->CustomQuery('DELETE FROM '.$tableString.' WHERE '.$deleteWhereClause.' '.$deleteExtra);
    }

}

// Other database objects should also implement SeekableIterator for use in loop operators
interface IDatabaseResult
{
    public function Fetch();
    public function FetchAll();
    public function Count();
    
    /* Inhereted Methods (from SeekableIterator) //
    abstract public void seek ( int $position )
    abstract public mixed current ( void )
    abstract public scalar key ( void )
    abstract public void next ( void )
    abstract public void rewind ( void )
    abstract public boolean valid ( void )

    //*/
}

// MySQLi_Result implements Traversible, does it need to implement Iterator?
class MySqlDatabaseResult extends MySQLi_Result implements IDatabaseResult
{
    /* Inhereted Properties
    int $current_field ;
    int $field_count;
    array $lengths;
    int $num_rows;
    $type;

    //*/
    
    public function Fetch()
    {
        return $this->fetch_assoc();
    }

    public function FetchAll()
    {
        $rows = array();
        while($row = $this->Fetch())
        {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function Count()
    {
        return $this->num_rows;
    }

    /* Inhereted Methods //    
    __construct
    close
    free_result
    
    bool data_seek ( int $offset )
    mixed fetch_all ([ int $resulttype = MYSQLI_NUM ] )
    mixed fetch_array ([ int $resulttype = MYSQLI_BOTH ] )
    array fetch_assoc ( void )
    object fetch_field_direct ( int $fieldnr )
    object fetch_field ( void )
    array fetch_fields ( void )
    object fetch_object ([ string $class_name [, array $params ]] )
    mixed fetch_row ( void )
    bool field_seek ( int $fieldnr )
    void free ( void )

    //*/
}

/*  Database Query Fluent Interface 
    Abstracts away the syntax in creating simple DB queries
*/
interface iQuery
{
    public function Execute(iDatabase $database); // possible name, ToDatabase()?
    public function ToString();
    
    /* Action Methods */
    public function Select();
    public function Create();
    public function Update();
    public function Delete();
    public function Custom($query);
    
    /* Selection Methods*/
    public function UsingTable($table, $tableAlias);
    public function Item($item, $itemAlias);
    
    /* Create/Update Methods*/
    public function SetValue($value);

    /* Where Clause Methods*/
    public function OrWhere();
    public function AndWhere(); // Default behavior is to AND clauses, may not be necessary
    public function Match($item, $condition);
    public function NotMatch($item, $condition);
    public function LessThan($item, $condition);   
    public function GreaterThan($item, $condition);
    public function LessThanOrEq($item, $condition);
    public function GreaterThanOrEq($item, $condition);
    public function FindIn($items, $text);
    
    /* Query Optimization Methods */
    public function orderBy($item, $ascending);
    public function Limit($count);
    public function Offset($amount);
}

class MySqlDatabaseQuery implements iQuery
{
    protected $action = null;   // Query Action: Select, Create (Insert), Update, Delete
    protected $tables = array();  // Tables to be accessed in the query (Using)
    protected $items = array();
    protected $itemValues = array();
    protected $whereClauses = array();
    protected $whereClausesLogic = array();
    protected $orderByLogic = array();
    protected $resultLimit = null;
    protected $resultOffset = null;
    protected $custom = null;

    public function __construct()
    {
        //return $this;
    }
    
    public function __toString()
    {
        return $this->ToString();
    }
    
    public function Execute(iDatabase $database)
    {
        return $database->Query($this);
    }
    
    public function ToString()
    {
        if(isset($this->custom))
            return $this->custom;
        else if(empty($this->action))
            throw new StrictException("No query action was set.");
        
        switch ($this->action)
        {
            case "SELECT":
                $sql = "SELECT";
                
                /* Select Items */
                if(empty($this->items))
                    $sql .= " *";
                else
                {
                    $items = array();
                    foreach($this->items as $key => $value)
                    {
                        $expanded = explode('.', $value);
                        $expanded = implode('`.`', $expanded);
                        if($key == $value)
                            $items[] .= "`{$expanded}`";
                        else
                            $items[] .= "`{$expanded}` AS `{$key}`";
                    }
                    
                    $sql .= ' '.implode(', ', $items);
                }
                
                /* Select Tables*/
                $tables = array();
                foreach($this->tables as $key => $value)
                {
                    if($key == $value)
                        $tables[] .= "`{$key}`";
                    else
                        $tables[] .= "`{$value}` AS `{$key}`";
                }
                
                $sql .= ' FROM '.implode(', ', $tables);
                
                /* Where Clauses*/
                if(!empty($this->whereClauses))
                {
                    $sql .= ' WHERE';
                    foreach($this->whereClauses as $key => $clause)
                    {
                        $sql .= " {$clause}";
                        $sql .= (isset($this->whereClausesLogic[$key])) ? " {$this->whereClausesLogic[$key]}": null;
                    }
                }
                
                /* Select Optimizers */
                if(!empty($this->orderByLogic))
                    $sql .= ' ORDER BY '.implode(', ', $this->orderByLogic);                
                
                if(isset($this->resultLimit))
                    $sql .= ' LIMIT '.$this->resultLimit;
                
                if(isset($this->resultOffset))
                    $sql .= ' OFFSET '.$this->resultOffset;
                                    
                return $sql.PHP_EOL;
                break;
                
            case "INSERT":
                $sql = "INSERT INTO `".array_values($this->tables)[0]."`";
                $sql .= " (`".implode('`,`', $this->items)."`)";
                $sql .= " VALUES('".implode("','", $this->itemValues)."')";
                
                return $sql.PHP_EOL;
                break;
            case "UPDATE":
                $sql = "UPDATE `".implode("`,`", $this->tables)."`";

                $items = array();
                foreach(array_values($this->items) as $key => $column)
                {
                    $items[] = " `{$column}` = '".$this->itemValues[$key]."'";
                }
                $sql .= ' SET'.implode(",", $items);
                                
                /* Where Clauses */
                if(!empty($this->whereClauses))
                {
                    $sql .= ' WHERE';
                    foreach($this->whereClauses as $key => $clause)
                    {
                        $sql .= " {$clause}";
                        $sql .= (isset($this->whereClausesLogic[$key])) ? " {$this->whereClausesLogic[$key]}": null;
                    }
                }
                
                /* Select Optimizers */
                if(!empty($this->orderByLogic))
                    $sql .= ' ORDER BY '.implode(', ', $this->orderByLogic);                
                
                if(isset($this->resultLimit))
                    $sql .= ' LIMIT '.$this->resultLimit;
                
                return $sql.PHP_EOL;
                break;
            case "DELETE":
                $sql = "DELETE FROM `".array_values($this->tables)[0]."`";

                /* Where Clauses */
                if(!empty($this->whereClauses))
                {
                    $sql .= ' WHERE';
                    foreach($this->whereClauses as $key => $clause)
                    {
                        $sql .= " {$clause}";
                        $sql .= (isset($this->whereClausesLogic[$key])) ? " {$this->whereClausesLogic[$key]}": null;
                    }
                }
                
                /* Select Optimizers */
                if(!empty($this->orderByLogic))
                    $sql .= ' ORDER BY '.implode(', ', $this->orderByLogic);                
                
                if(isset($this->resultLimit))
                    $sql .= ' LIMIT '.$this->resultLimit;
                
                return $sql.PHP_EOL;
                break;
            default:
                return null;
        }
    }
    
    /* Action Methods */
    public function Select()
    {
        $this->action = "SELECT";
        return $this;
    }
    
    public function Create()
    {
        $this->action = "INSERT";
        return $this;
    }
    
    public function Update()
    {
        $this->action = "UPDATE";
        return $this;
    }
    
    public function Delete()
    {
        $this->action = "DELETE";
        return $this;
    }

    public function Custom($query)
    {
        $this->custom = $query;
        return $this;
    }
    
    /* Selection Methods*/
    public function UsingTable($table, $tableAlias = null)
    {
        if(isset($tableAlias))
            $this->tables[$tableAlias] = $table;
        else
            $this->tables[$table] = $table;
        return $this;
    }

    public function Item($item, $itemAlias = null)
    {
        if(isset($itemAlias))
            $this->items[$itemAlias] = $item;
        else
            $this->items[$item] = $item;
            
        $this->itemValues[] = null;
        return $this;
    }    
    
    /* Create/Update Methods */
    public function SetValue($value)
    {
        $count = count($this->items);
        if($count < 1)
            return $this;
            
        $this->itemValues[($count - 1)] = $value;
                          
        return $this;
    }
    
    /* Where Clause Methods*/
    public function OrWhere()
    {
        $count = count($this->whereClausesLogic);
        
        if($count < 1)
            return $this;
        
        $this->whereClausesLogic[($count - 1)] = "OR";
                          
        return $this;
    }
    
    public function AndWhere()
    {
        $count = count($this->whereClausesLogic);
        
        if($count < 1)
            return $this;
        
        $this->whereClausesLogic[($count - 1)] = "AND";
                          
        return $this;
    }
    
    public function Match($item, $condition)
    {
        if(is_string($condition))
            $condition = "'{$condition}'";
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` = ".$condition;
        
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                          
        return $this;
    }

    public function NotMatch($item, $condition)
    {
        if(is_string($condition))
            $condition = "'{$condition}'";
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` != ".$condition;
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                
        return $this;
    }
        
    public function LessThan($item, $condition)
    {
        if(!is_numeric($condition))
            throw new StrictException("Condition must be numeric");
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` < ".$condition;
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
        
        return $this;
    }
    
    public function GreaterThan($item, $condition)
    {
        if(!is_numeric($condition))
            throw new StrictException("Condition must be numeric");
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` > ".$condition;
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                
        return $this;
    }

    public function LessThanOrEq($item, $condition)
    {
        if(!is_numeric($condition))
            throw new StrictException("Condition must be numeric");
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` <= ".$condition;
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                
        return $this;
    }
    
    public function GreaterThanOrEq($item, $condition)
    {
        if(!is_numeric($condition))
            throw new StrictException("Condition must be numeric");
        
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $where = "`{$item}` >= ".$condition;
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                
        return $this;
    }
    
    public function FindIn($items, $text)
    {
        if(is_array($items))
        {
            foreach($items as $key => $item)
            {
                $expanded = explode('.', $item);
                $items[$key] = implode('`.`', $expanded);            
            }
            $match = implode(', ', $items);
        }
        else
        {
            $expanded = explode('.', $items);
            $match = implode('`.`', $expanded);
        }

        $where = "MATCH({$match}) AGAINST('{$text}' IN BOOLEAN MODE)";
        $this->whereClauses[] = $where;
        $this->whereClausesLogic[] = null;
        
        // Default to logical AND to join WHERE clauses        
        $count = count($this->whereClausesLogic);
        if($count > 1)
            if(!isset($this->whereClausesLogic[($count - 2)]))
                $this->whereClausesLogic[($count - 2)] = "AND";
                
        return $this;
    }
    
    /* Query Optimization Methods */
    public function orderBy($item, $ascending = true)
    {
        $direction = ($ascending) ? 'ASC': 'DESC';
        $expanded = explode('.', $item);
        $item = implode('`.`', $expanded);
        $order = "`{$item}` ".$direction;
        $this->orderByLogic[] = $order;
        
        return $this;
    }
    
    public function Limit($count)
    {
        $this->resultLimit = $count;
        return $this;
    }
    
    public function Offset($amount)
    {
        $this->resultOffset = $amount;
        return $this;
    }

}



?>
