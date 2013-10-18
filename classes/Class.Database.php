<?php
/**
 * Class.Database
 * @package SMPL\Database
 */

/*
ANSI SQL92-Compliant Chainable Query Interface
http://savage.net.au/SQL/sql-92.bnf.html
example: http://docs.kohanaphp.com/libraries/database/builder
Before: 
$database = Config::Database();
$database->Update()
    ->UsingTable("content")
    ->UsingTable("blocks")
    ->Item("publish-publish_flag-dropdown")->SetValue(Query::PUBLISHED)
    ->Match("publish-publish_flag-dropdown", Query::TO_PUBLISH)
    ->AndWhere()->LessThanOrEq("publish-publish_date-date", Date::Now()->ToInt())
    ->Send(); // Slightly more terse, but also very tightly coupled, must have a Query type for each Database type
    
After:
$query = Query::Build('Publish pending articles')
    ->Update()
    ->UseTable(array('content','blocks'))
    ->Set('publish-publish_flag-dropdown', Query::PUBLISHED)
    ->Where()->IsEqual('publish-publish_flag-dropdown', Query::TO_PUBLISH)
    ->AndWhere()->IsLessOrEq('publish-publish_date-date', Date::Now()->ToInt()); //Slightly more verbose, but domain agnostic and atomic
Config::Database()->Execute($query); //Validation and Execution happens inside the Database class
*/

/**
 * ANSI SQL92-Compliant Chainable Query Interface
 * @package Database\Query
 */
class Query
{
    const CREATE = 1;
    const RETRIEVE = 2;
    const UPDATE = 3;
    const DELETE = 4;

    /* Constants used in queries */
    const LINK_AND = 0;
    const LINK_OR = 1;
    const SORT_ASC = 0;
    const SORT_DESC = 1;
    const PUBLISHED = 1;
    const TO_PUBLISH = 2;
    const UNPUBLISHED = 0;
    const PRIORITY_HIGH = 1;
    const PRIORITY_MED = 2;
    const PRIORITY_LOW = 3;

    /* Predicate Constants */
    const IS_EQUAL = 1;
    const IS_NOT_EQUAL = 2;
    const IS_LESS_THAN= 3;
    const IS_LESS_OREQ = 4;
    const IS_GREATER_THAN= 5;
    const IS_GREATER_OREQ = 6;
    const IS_BETWEEN = 7;
    const IS_IN = 8;
    const IS_NOT_IN = 9;
    const IS_LIKE = 10;
    const IS_NOT_LIKE = 11;
    const IS_NULL = 12;
    const IS_NOT_NULL = 13;
    const IS_MATCH = 14;
        
    private $description = null;
    private $type = null;
    private $previous = null;
    
    private $tables = array();
    private $items = array();
    private $values = array();
    private $predicates = array();
    private $clusters = array();
    private $order = array();
    private $limit = null;
    private $offset = null;
    
    // Optional meta description to give context to a query
    private function __construct($description = null)
    {
        if (isset($description) && !is_string($description)) {
            throw new ErrorException('Description must be of string type.');
        }
        
        $this->description = $description;
    }
    
    public function __toString()
    {
        if (isset($this->description)) {
            return $this->description;
        }
        else {
            return __CLASS__;
        }
    }

    public static function Build($description = null) {
        return new self($description);
    }

    public function Extract() {
        return array(
            'type' => $this->type,
            'tables' => $this->tables,
            'items' => $this->items,
            'values' => $this->values,
            'predicates' => $this->predicates,
            'clusters' => $this->clusters,
            'order' => $this->order,
            'limit' => $this->limit,
            'offset' => $this->offset
        );
    }
    
    public function Create()
    {
        $this->type = self::CREATE;
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Retrieve()
    {
        $this->type = self::RETRIEVE;
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Update()
    {
        $this->type = self::UPDATE;
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Delete()
    {
        $this->type = self::DELETE;
        $this->previous = __FUNCTION__;
        return $this;
    }
    // Can accept arrays
    public function UseTable($table, $alias = null)
    {
        // For submitting multiple items in a single request: array( 'item', 'alias' => 'item', 'item', ...)
        if (is_array($table)) {
            foreach($table as $key => $value) {
                if (is_numeric($key)) {
                    $this->tables[] = array($value, null);
                }
                else {
                    $this->tables[] = array($value, $key);
                }
            }
        }
        else {
            $this->tables[] = array($table, $alias);
        }

        $this->previous = __FUNCTION__;
        return $this;
    }
    // Can accept arrays
    public function Get($item, $alias = null)
    {
        if ($this->type !== self::RETRIEVE) {
            throw new WarningException('Get Method used when Query is not of Retrieve type.');
        }
        // For submitting multiple items in a single request: array( 'item', 'alias' => 'item', 'item', ...)
        if (is_array($item)) {
            foreach($item as $key => $value) {
                if (is_numeric($key)) {
                    $this->items[] = array($value, null);
                }
                else {
                    $this->items[] = array($value, $key);
                }
            }
        }
        else {
            $this->items[] = array($item, $alias);
        }
        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Set($item, $value)
    {
        if ($this->type !== self::CREATE && $this->type !== self::UPDATE) {
            throw new WarningException('Set Method used when Query is not of Create or Update type.');
        }
        
        $this->items[] = array($item, null);
        $this->values[] = $value;        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Where()
    {
        $count = count($this->predicates);
        if ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_AND;
            throw new StrictException('The Where() method should appear before any predicate is set, and should only be used once.');
        }
        $this->previous = __FUNCTION__;
        return $this;
    }
    // Should throw Notice regarding Semantics of Fluid Interface if called before Where()
    public function AndWhere()
    {
        $count = count($this->predicates);
        if ($count === 0) {
            throw new StrictException('The AndWhere() method should not be used until a predicate has been set.');
        }
        elseif ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_AND;
        }
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function OrWhere()
    {
        $count = count($this->predicates);
        if ($count === 0) {
            throw new StrictException('The OrWhere() method should not be used until a predicate has been set.');
        }
        elseif ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_OR;
        }
        $this->previous = __FUNCTION__;
        return $this;
    }

    public function IsEqual($item, $value)
    {        
        $this->predicates[] = array(
            'type' => self::IS_EQUAL,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }    
    
    public function IsNotEqual($item, $value)
    {
        $this->predicates[] = array(
            'type' => self::IS_NOT_EQUAL,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsLessThan($item, $value)
    {
        $this->predicates[] = array(
            'type' => self::IS_LESS_THAN,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
              
    public function IsLessOrEq($item, $value)
    {
        $this->predicates[] = array(
            'type' => self::IS_LESS_OREQ,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsGreaterThan($item, $value)
    {
        $this->predicates[] = array(
            'type' => self::IS_GREATER_THAN,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsGreaterOrEq($item, $value)
    {
        $this->predicates[] = array(
            'type' => self::IS_GREATER_OREQ,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsBetween($item, $min, $max)
    {
        $this->predicates[] = array(
            'type' => self::IS_BETWEEN,
            'item' => $item,
            'value' => array('min' => $min, 'max' => $max),
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsIn($item, $set)
    {
        $this->predicates[] = array(
            'type' => self::IS_IN,
            'item' => $item,
            'value' => $set,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsNotIn($item, $set)
    {
        $this->predicates[] = array(
            'type' => self::IS_NOT_IN,
            'item' => $item,
            'value' => $set,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsLike($item, $string)
    {
        $this->predicates[] = array(
            'type' => self::IS_LIKE,
            'item' => $item,
            'value' => $string,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsNotLike($item, $string)
    {
        $this->predicates[] = array(
            'type' => self::IS_NOT_LIKE,
            'item' => $item,
            'value' => $string,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }

    public function IsNull($item)
    {
        $this->predicates[] = array(
            'type' => self::IS_NULL,
            'item' => $item,
            'value' => null,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsNotNull($item)
    {
        $this->predicates[] = array(
            'type' => self::IS_NOT_NULL,
            'item' => $item,
            'value' => null,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function IsMatch($item, $string)
    {
        $this->predicates[] = array(
            'type' => self::IS_MATCH,
            'item' => $item,
            'value' => $string,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    // Allow for complex clustering of predicates
    public function Cluster($alias, $operator, $index1, $index2) {}
    
    // Can accept arrays
    public function OrderBy($item, $order = self::SORT_ASC)
    {
        // For submitting multiple items in a single request: array( 'item', 'item' => order, 'item', ...)
        if (is_array($item)) {
            foreach($item as $key => $value) {
                if (is_numeric($key)) {
                    $this->order[] = array($value, self::SORT_ASC);
                }
                else {
                    if ($value !== self::SORT_ASC) {
                        $value = self::SORT_DESC;
                    }
                    $this->order[] = array($key, $value);
                }
            }
        }
        else {
            if ($order !== self::SORT_ASC) {
                $order = self::SORT_DESC;
            }
            $this->items[] = array($item, $order);
        }
        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Limit($max)
    {
        if (isset($this->limit)) {
            throw new NoticeException('Limit has been previously set. Value will be overwritten.');
        }
        
        $this->limit = $max;
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Offset($amount)    
    {
        if (isset($this->offset)) {
            throw new NoticeException('Offset has been previously set. Value will be overwritten.');
        }
        
        $this->offset = $amount;
        $this->previous = __FUNCTION__;
        return $this;
    }   
}

/**
 * Database interface
 * @package Database\Interface
 */
interface Database
{
    /**
     * Perform a customized domain-specific query
     * @param string $query
     * @return DatabaseResult
     */
    public function CustomQuery($query);

    /**
     * Validate a query object
     * @param \Query $query
     * @return bool
     */
    public function IsValid(Query $query);

    /**
     * Perform a customized domain-specific query
     * @param \Query $query
     * @return DatabaseResult
     */
    public function Execute(Query $query);
}


/**
 * MySQL Implementation of the Database interface
 * @package Database\MySQL
 */
class MySqlDatabase extends MySQLi implements Database
{
    /**
     * MySQL Database host
     * @var string $host
     * @access protected     
     */
    protected $host;

    /**
     * MySQL Database name
     * @var string $name
     * @access protected     
     */
    protected $name;
    
    /**
     * MySQL Database user name
     * @var string $username
     * @access protected     
     */
    protected $username;
    
    /**
     * MySQL Database password
     * @var string $password
     * @access protected     
     */
    protected $password;
    
    /**
     * MySQL Database table prefix
     * @var string $prefix
     * @access protected     
     */
    protected $prefix;

    /**
     * MySQL Database constructor
     * @param string $host
     * @param string $name
     * @param string $username
     * @param string $password
     * @param string $prefix
     * @return \MySqlDatabase
     */
    public function __construct($host, $name, $username, $password, $prefix)
    {
        $this->host = $host;
        $this->name = $name;
        $this->username = $username;
        $this->password = $password;
        $this->prefix = $prefix;        
        parent::__construct($host, $username, $password, $name);
    }

    /**
     * Perform a customized domain-specific query
     * @param string $query
     * @return DatabaseResult
     */
    public function CustomQuery($query) {
        Debug::Message('MySqlDatabase\Query: '.$query);
        $this->real_query($query);
        return new MySqlDatabaseResult($this);
    }

    /**
     * Check if a query contains valid information to be successfully executed by the database.
     * This does not check if the tables/columns referenced actually exist
     * or IsValid()
     * @param \Query $query
     * @return bool
     */
    public function IsValid(Query $query)
    {
        // check predicates, clusters, order, limit, offset
        $data = $query->Extract();
        return false;
    }

    /**
     * Perform a customized domain-specific query
     * @param \Query $query
     * @return DatabaseResult
     */
    public function Execute(Query $query)
    {
        return null;
        if (isset($this->custom)) {
            return $this->custom;
        }
        elseif (empty($this->action)) {
            // [MUSTCHANGE]
            //throw new WarningException("No query action was set."); // Should output
            return get_class($this);
        }

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
                // [MUSTCHANGE]
                //throw new WarningException("No query action was set."); // Should output
                return get_class($this);
        }
    }

    /**
     * Override MySQLi query() method to return MySqlDatabaseResult object
     * This may not be necessary anymore
     * @param string $query
     * @return DatabaseResult
     *
    public function Query($query)
    {
    Debug::Message('MySqlDatabase\Query: '.$query);
    $this->real_query($query);
    return new MySqlDatabaseResult($this);
    } //*/
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
