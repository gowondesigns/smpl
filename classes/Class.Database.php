<?php
/**
 * Class.Database
 *
 * @package SMPL\Database
 */

/**
 * Database interface
 *
 * @package Database\Interface
 */
interface Database
{
    //[MUSTCHANGE] MUST CREATE A CONNECT METHOD
    public static function NewQuery($database = null);    // Instantiate new query object

    /**
     * Generate a Create (Insert) query
     *
     * @return Query
     */
    public function Create();


    /**
     * Generate a Retrieve (Select) query
     *
     * @return Query
     */
    public function Retrieve();

    /**
     * Generate a Update query
     *
     * @return Query
     */
    public function Update();


    /**
     * Generate a Delete query
     *
     * @return Query
     */
    public function Delete();


    /**
     * Generate a custom query
     *
     * @param $query
     * @return Query
     */
    public function Custom($query);
}

/**
 * MySQL Implementation of the Database interface
 *
 * @package Database\MySQL
 */
class MySqlDatabase extends MySQLi implements Database
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

    public static function NewQuery($database = null)
    {
        return new MySqlDatabaseQuery($database);
    }

    public function Query($query)
    {
        Debug::Message('MySqlDatabase\Query: '.$query);
        $this->real_query($query);
        return new MySqlDatabaseResult($this);
    }

    public function Create() 
    {
        $query = new MySqlDatabaseQuery($this);
        return $query->Create();
    }
    
    public function Retrieve()
    {
        $query = new MySqlDatabaseQuery($this);
        return $query->Retrieve();
    }
    
    public function Update()
    {
        $query = new MySqlDatabaseQuery($this);
        return $query->Update();
    }
    
    public function Delete()
    {
        $query = new MySqlDatabaseQuery($this);
        return $query->Create();
    }

    public function Custom($queryString)
    {
        $query = new MySqlDatabaseQuery($this);
        return $query->Custom($queryString);
    }
}

/**
 * Database Query object that implements a fluent interface, abstracts away the syntax in creating simple DB queries
 *
 * @package Database\DatabaseResult
 */
interface Query
{   
    /* Constants used in queries */
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';
    const PUBLISHED = 1;
    const TO_PUBLISH = 2;
    const UNPUBLISHED = 0;
    const PRIORITY_HIGH = 1;
    const PRIORITY_MED = 2;
    const PRIORITY_LOW = 3;

    /**
     * Execute the query
     * @param Database $database database that will be used to execute the query
     * @return DatabaseResult The number of rows returned in the result
     */
    public function Send(Database $database = null);

    /**
     * Convert the query to a string
     * @return string
     */
    public function ToString();

    /**
     * Generate a select-type Query
     * @return \Query
     */
    public function Retrieve();

    /**
     * Generate a create-type Query
     * @return \Query
     */
    public function Create();

    /**
     * Generate a update-type Query
     * @return \Query
     */
    public function Update();

    /**
     * Generate a delete-type Query
     * @return \Query
     */
    public function Delete();

    /**
     * Generate a custom Query
     * @param string $query
     * @return \Query
     */
    public function Custom($query);

    /**
     * Select SQL table to perform operation on
     * @param string $table
     * @param string $tableAlias
     * @return \Query
     */
    public function UsingTable($table, $tableAlias = null);

    /**
     * Select a column from the SQL table
     * @param string $item
     * @param string $itemAlias
     * @return \Query
     */
    public function Item($item, $itemAlias = null);

    /**
     * Assign a value to the preceding item
     * @param string $value
     * @return \Query
     */
    public function SetValue($value);

    /**
     * Modify match relationship of proceeding clause to OR
     * @return \Query
     */
    public function OrWhere();

    /**
     * Modify match relationship of proceeding clause to AND
     * @return \Query
     */
    public function AndWhere();

    /**
     * Add an equal-to match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function Match($item, $condition);

    /**
     * Add a not-equal-to match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function NotMatch($item, $condition);

    /**
     * Add a less-than match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function LessThan($item, $condition);

    /**
     * Add a greater-than match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function GreaterThan($item, $condition);

    /**
     * Add a less-than-or-equal-to match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function LessThanOrEq($item, $condition);

    /**
     * Add a greater-than-or-equal-to match parameter to a SQL query
     * @param string $item
     * @param string $condition
     * @return \Query
     */
    public function GreaterThanOrEq($item, $condition);

    /**
     * Add a matching parameter to find <text> in SQL table items
     * @param string $items
     * @param string $text
     * @return \Query
     */
    public function FindIn($items, $text);

    /**
     * Add a sorting parameter on a column in the SQL table
     * @param string $item
     * @param string $direction
     * @return \Query
     */
    public function OrderBy($item, $direction);

    /**
     * Limits the amount of rows returned in the result set
     * @param int $count
     * @return \Query
     */
    public function Limit($count);

    /**
     * Set offset for the query result set
     * @param int $amount
     * @return \Query
     */
    public function Offset($amount);
}

class MySqlDatabaseQuery implements Query
{
    protected $database = null;
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

    public function __construct(MySqlDatabase $database = null)
    {
        if (isset($database)) {
            $this->database = $database;
        }
    }

    public function Send(Database $database = null)
    {
        if ($database instanceof MySqlDatabase) {
            return $database->Query($this->ToString());
        }
        elseif (isset($this->database)) {
            return $this->database->Query($this->ToString());
        }
        else {
            throw new ErrorException("No database was set up.");
        }
    }

    public function __toString()
    {
        return $this->ToString();
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
                    $sql .= ' LIMIT ' . $this->resultLimit;
                
                return $sql . PHP_EOL;
                break;
            default:
                return null;
        }
    }
    
    /* Action Methods */
    public function Retrieve()
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
    public function OrderBy($item, $direction = null)
    {
        if (null === $direction) {
            $direction = self::SORT_ASC;
        }

        $item = str_replace('.', '`.`', $item);
        $this->orderByLogic[] = '`' . $item . '` ' . $direction;

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

/**
 * Interface for the results that come from Query objects. May benefit from extending Seekable Iterator interface
 *
 * @package Database\DatabaseResult
 */
interface DatabaseResult
{
    /**
     * Fetch result from Query
     * @return array an associative array of strings representing the fetched row in the result
     * set, where each key in the array represents the name of one of the result
     * set's columns or null if there are no more rows in resultset.
     */
    public function Fetch();

    /**
     * Fetch complete result set from Query
     * @return array an array of all associative arrays of strings representing the fetched row in the result
     * set
     */
    public function FetchAll();

    /**
     * Count number of rows from Query
     * @return int The number of rows returned in the result
     */
    public function Count();
}

/**
 * MySQL implementation of Database Result interface, also extends the PDO MySQLi Result class,
 * which implements the Traversable interface
 * @package Database\MySQL\Result
 */
class MySqlDatabaseResult extends MySQLi_Result implements DatabaseResult
{
    /**
     * Fetch results from MySQL Query
     * @return array an associative array of strings representing the fetched row in the result
     * set, where each key in the array represents the name of one of the result
     * set's columns or null if there are no more rows in resultset.
     */
    public function Fetch()
    {
        return $this->fetch_assoc();
    }

    /**
     * Fetch complete result set from MySQL Query
     * @return array an array of all associative arrays of strings representing the fetched row in the result
     * set
     */
    public function FetchAll()
    {
        $rows = array();
        while($row = $this->Fetch())
        {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Count number of rows from MySQL Query
     * @return int The number of rows returned in the result
     */
    public function Count()
    {
        return $this->num_rows;
    }

    /* Inhereted Properties:
    int $current_field ;
    int $field_count;
    array $lengths;
    int $num_rows;
    $type;
    */

    /* Inhereted Methods:
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
    */
}

?>
