<?php
/**
 * Class.Database
 * @package SMPL\Database
 */

/**
 * ANSI SQL92-Compliant Chainable Query Interface
 * @package Database\Query
 */
class Query
{
    const TYPE_CREATE = 1;
    const TYPE_RETRIEVE = 2;
    const TYPE_UPDATE = 3;
    const TYPE_DELETE = 4;

    /* Constants used in queries */
    const LINK_AND = 0;
    const LINK_OR = 1;
    const SORT_ASC = 0;
    const SORT_DESC = 1;
    const PUB_NOT = 0;
    const PUB_ACTIVE = 1;
    const PUB_FUTURE = 2;
    const PRIORITY_HIGH = 1;
    const PRIORITY_MED = 2;
    const PRIORITY_LOW = 3;

    /* Predicate Constants */
    const IS_EQUAL = 1;
    const IS_NOT_EQUAL = 2;
    const IS_LESS_THAN = 3;
    const IS_LESS_OREQ = 4;
    const IS_GREATER_THAN= 5;
    const IS_GREATER_OREQ = 6;
    const IS_BETWEEN = 7;
    const IS_NOT_BETWEEN = 8;
    const IS_IN = 9;
    const IS_NOT_IN = 10;
    const IS_LIKE = 11;
    const IS_NOT_LIKE = 12;
    const IS_NULL = 13;
    const IS_NOT_NULL = 14;
        
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

    /**
     * Private constructor. Object can only be created using the Build() method.
     * @param string $description
     * @return Query
     */
    private function __construct($description = null)
    {
        if (isset($description) && !is_string($description)) {
            trigger_error('Description must be of string type.', E_USER_ERROR);
        }
        $this->description = $description;
    }
    
    /**
     * Magic method overloading executing the object as a string. Will return the 
     * Query description or simple 'Query' if description is null.     
     * @return string
     */
    public function __toString()
    {
        if (isset($this->description)) {
            return $this->description;
        }
        else {
            return __CLASS__;
        }
    }

    /**
     * Factory method to generate Query objects. Meta description is used whenever
     * the Query object is treated like a string.     
     * @param string $description Meta description of the Query
     * @return Query
     */
    public static function Build($description = null) {
        return new self($description);
    }

    /**
     * Pass Query data for validation or execution
     * @return array
     */
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
    
    /**
     * Set the Query type to perform a Create operation
     * @return Query
     */
    public function Create()
    {
        $this->type = self::TYPE_CREATE;
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Set the Query type to perform a Create operation
     * @return Query
     */    
    public function Retrieve()
    {
        $this->type = self::TYPE_RETRIEVE;
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Set the Query type to perform a Create operation
     * @return Query
     */    
    public function Update()
    {
        $this->type = self::TYPE_UPDATE;
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Set the Query type to perform a Create operation
     * @return Query
     */    
    public function Delete()
    {
        $this->type = self::TYPE_DELETE;
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Sets the tables used for the query. Optional alias must be non-numeric.
     * Array format: array( 'table', 'alias' => 'table', 'table', ...)           
     * @param string|array $table Table name or array of table names 
     * @param string $alias Optional alias for the table selected. Not used when $table is an array.      
     * @return Query
     */
    public function UseTable($table, $alias = null)
    {
        if (is_array($table)) {
            foreach($table as $key => $value) {
                if (is_numeric($key)) {
                    if (!Pattern::Validate(Pattern::SQL_NAME, $value)) {
                        trigger_error('Table and Alias names must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
                    }
                    $this->tables[] = array($value, null);
                }
                else {
                    if (!Pattern::Validate(Pattern::SQL_NAME, $key) || !Pattern::Validate(Pattern::SQL_NAME, $value)) {
                        trigger_error('Table and Alias names must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
                    }
                    $this->tables[] = array($value, $key);
                }
            }
        }
        else {
            if (!Pattern::Validate(Pattern::SQL_NAME, $table) || (isset($alias) && !Pattern::Validate(Pattern::SQL_NAME, $alias))) {
                trigger_error('Table and Alias names must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
            }
            $this->tables[] = array($table, $alias);
        }

        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Sets the columns returned in the DatabaseResults of this query. Optional alias must be non-numeric.
     * Array format: array( 'item', 'alias' => 'item', 'item', ...)          
     * @param string|array $item Column name or array of column names 
     * @param string $alias Optional alias for the column selected. Not used when $item is an array.      
     * @return Query
     */
    public function Get($item, $alias = null)
    {
        if ($this->type !== self::TYPE_RETRIEVE) {
            trigger_error('Get Method used when Query is not of Retrieve type.', E_USER_WARNING);
        }
        if (is_array($item)) {
            foreach($item as $key => $value) {
                if (!Pattern::Validate(Pattern::SQL_NAME_WITH_PREPEND, $value) || (!is_numeric($key) && !Pattern::Validate(Pattern::SQL_NAME, $key))) {
                    trigger_error('Item and Alias names must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
                }
                if (is_numeric($key)) {
                    $this->items[] = array($value, null);
                }
                else {
                    $this->items[] = array($value, $key);
                }
            }
        }
        else {
            if (!Pattern::Validate(Pattern::SQL_NAME_WITH_PREPEND, $item)  || (isset($alias) && !Pattern::Validate(Pattern::SQL_NAME, $alias))) {
                trigger_error('Item and Alias names must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
            }
            $this->items[] = array($item, $alias);
        }
        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Sets a value to a column used in the query.          
     * @param string $item Column name
     * @param string $value Value to be stored in the column      
     * @return Query
     */
    public function Set($item, $value)
    {
        if ($this->type !== self::TYPE_CREATE && $this->type !== self::TYPE_UPDATE) {
            trigger_error('Set Method used when Query is not of Create or Update type.', E_USER_WARNING);
        }
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->items[] = array($item, null);
        $this->values[] = $value;        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Semantic method to start the WHERE clause in a query. If used again, method will behave
     * like the AndWhere() method.                    
     * @return Query
     */
    public function Where()
    {
        $count = count($this->predicates);
        if ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_AND;
            trigger_error('The Where() method should appear before any predicate is set, and should only be used once.', E_USER_WARNING);
        }
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Semantic method to append on to the WHERE clause, using an AND to link the proceeding predictate.              
     * @return Query
     */
    public function AndWhere()
    {
        $count = count($this->predicates);
        if ($count === 0) {
            trigger_error('The AndWhere() method should not be used until a predicate has been set.', E_USER_WARNING);
        }
        elseif ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_AND;
        }
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Semantic method to append on to the WHERE clause, using an OR to link the proceeding predicate.
     * @return Query
     */
    public function OrWhere()
    {
        $count = count($this->predicates);
        if ($count === 0) {
            trigger_error('The OrWhere() method should not be used until a predicate has been set.', E_USER_WARNING);
        }
        elseif ($count > 0 && $this->predicates[($count - 1)]['link'] === null) {
            $this->predicates[($count - 1)]['link'] = self::LINK_OR;
        }
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Predicate to check: <item> = <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsEqual($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_EQUAL,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }    

    /**
     * Predicate to check: <item> != <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsNotEqual($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_NOT_EQUAL,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Predicate to check: <item> < <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsLessThan($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_LESS_THAN,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Predicate to check: <item> <= <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsLessOrEq($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_LESS_OREQ,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Predicate to check: <item> > <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsGreaterThan($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_GREATER_THAN,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Predicate to check: <item> >= <value>          
     * @param string $item
     * @param string|int $value      
     * @return Query
     */
    public function IsGreaterOrEq($item, $value)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_GREATER_OREQ,
            'item' => $item,
            'value' => $value,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Predicate to check: (<item> => <min> AND <item> <= <max>)          
     * @param string $item
     * @param string|int $min
     * @param string|int $max           
     * @return Query
     */
    public function IsBetween($item, $min, $max)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_BETWEEN,
            'item' => $item,
            'value' => array('min' => $min, 'max' => $max),
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Predicate to check: (<item> < <min> OR <item> > <max>), inclusive
     * @param string $item
     * @param string|int $min
     * @param string|int $max
     * @return Query
     */
    public function IsNotBetween($item, $min, $max)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_NOT_BETWEEN,
            'item' => $item,
            'value' => array('min' => $min, 'max' => $max),
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Predicate to check: <column> belongs to <set>{.,..,...}          
     * @param string $item
     * @param array $set      
     * @return Query
     */
    public function IsIn($item, $set)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_IN,
            'item' => $item,
            'value' => $set,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    /**
     * Predicate to check: <column> does not belong to <set>{.,..,...}          
     * @param string $item
     * @param array $set      
     * @return Query
     */
    public function IsNotIn($item, $set)
    {
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
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
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
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
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
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
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
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
        if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
            trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        $this->predicates[] = array(
            'type' => self::IS_NOT_NULL,
            'item' => $item,
            'value' => null,
            'link' => null
        );
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Cluster predicates into groups. Useful for complex WHERE clauses. Overrides
     * basic link logic in $this->predicates.
     * @param string $name Name of the cluster to be refernced in nested clusters
     * @param int $operator link cluster with either the LINK_AND or LINK_OR constant
     * @param int|string $index1
     * @param int|string $index2, ...
     * @return Query
     */
    public function Cluster(/** @noinspection PhpUnusedParameterInspection */
        $name, $operator, $index1, $index2)
    {
        $args = func_get_args();
        $name = array_shift($args);

        if (!Pattern::Validate(Pattern::SQL_NAME, $name)) {
            trigger_error('Cluster name \''. $name .'\' not valid.  Name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
        }
        if (array_key_exists($name, $this->clusters)) {
            trigger_error('\'' . $name . '\' cluster already exists. This will overwrite that cluster.', E_USER_WARNING);
        }
        
        $operator = array_shift($args);

        if ($operator !== Query::LINK_AND && $operator !== Query::LINK_OR) {
            trigger_error('Cluster operator must be Query::LINK_AND or Query::LINK_OR.', E_USER_ERROR);
        }

        for ($i = 0; $i < count($args); $i++) {
            if (is_numeric($args[$i]) && !isset($this->predicates[($args[$i] - 1)])) {
                trigger_error('There are only ' . count($this->predicates) . ' predicates available. \'' . $args[$i] . '\' is not a valid option.', E_USER_ERROR);
            }
            elseif (!is_numeric($args[$i]) && !isset($this->clusters[($args[$i])])) {
                trigger_error('\'' . $args[$i] . '\' is not a name of a cluster.', E_USER_ERROR);
            }

            /*/
            if (is_numeric($args[$i])) {
                $args[$i] = '<<' . $args[$i] . '>>';
            } //*/
        }

        $this->clusters[$name] = array($operator, implode(',', $args));
        
        $this->previous = __FUNCTION__;
        return $this;
    }

    /**
     * Add OrderBy predicate to Query
     * @param string|array $item
     * @param int $order
     * @return Query
     */
    public function OrderBy($item, $order = self::SORT_ASC)
    {
        // For submitting multiple items in a single request: array( 'item', 'item' => order, 'item', ...)
        if (is_array($item)) {
            foreach($item as $key => $value) {
                if (!Pattern::Validate(Pattern::SQL_NAME, $key)) {
                    trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
                }
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
            if (!Pattern::Validate(Pattern::SQL_NAME, $item)) {
                trigger_error('Item name must be alphanumeric, begin with a letter, and be less than or equal to 30 characters in length.', E_USER_ERROR);
            }
            if ($order !== self::SORT_ASC) {
                $order = self::SORT_DESC;
            }
            $this->order[] = array($item, $order);
        }
        
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Limit($max)
    {
        if (!is_int($max) || $max < 0) {
            trigger_error('Limit must be a positive integer.', E_USER_ERROR);
        }
        if (isset($this->limit)) {
            trigger_error('Limit has been previously set. Value will be overwritten.', E_USER_NOTICE);
        }
        
        $this->limit = $max;
        $this->previous = __FUNCTION__;
        return $this;
    }
    
    public function Offset($amount)    
    {
        if (!is_int($amount) || $amount < 0) {
            trigger_error('Offset must be a positive integer.', E_USER_ERROR);
        }
        if (!isset($this->limit)) {
            trigger_error('Limit has not previously been set. Offset can only be used with a Limit.', E_USER_WARNING);
        }
        if (isset($this->offset)) {
            trigger_error('Offset has been previously set. Value will be overwritten.', E_USER_NOTICE);
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
     * @return MySqlDatabaseResult
     */
    public function CustomQuery($query) {
        Debug::Log($query);
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
        // check type, clusters
        $data = $query->Extract();

        // Validate Query Type is set
        if (!isset($data['type'])) {
            Debug::Log('Database\\IsValid: Query('. $query .') Failed type integrity check.');
            return false;
        }

        // Validate Query Clusters
        if (!empty($data['clusters'])) {
            $clustered = array();
            $totalStatements = count($data['predicates']) + count($data['clusters']);

            foreach ($data['clusters'] as $value) {
                $cluster = explode(',', $value[1]);
                foreach ($cluster as $name) {
                    // Check if referenced predicate exists
                    if (is_numeric($name) && !array_key_exists(($name - 1), $data['predicates'])) {
                        Debug::Log('Database\\IsValid: Query('. $query .') Failed predicate integrity check.');
                        return false;
                    }
                    // Check if referenced cluster exists
                    if (!is_numeric($name) && !array_key_exists($name, $data['clusters'])) {
                        Debug::Log('Database\\IsValid: Query('. $query .') Failed cluster integrity check (1).');
                        return false;
                    }
                }
                $clustered = array_merge($clustered, explode(',', $value[1]));
            }

            if (($totalStatements - 1) !== count($clustered)) {
                Debug::Log('Database\\IsValid: Query('. $query .') Failed cluster integrity check (2).');
                return false;
            }
        }

        return true;
    }

    /**
     * Execute given query
     * @param \Query $query
     * @return MySqlDatabaseResult
     */
    public function Execute(Query $query)
    {
        if (!$this->IsValid($query)) {
            trigger_error('Query(' . $query . ') is not a valid MySQL Query.', E_USER_ERROR);
        }

        $data = $query->Extract();
        $sql = null;

        switch ($data['type'])
        {
            case Query::TYPE_CREATE:
                $items = array();
                $values = array();

                for ($i = 0; $i < count($data['items']); $i++) {
                    $items[] = $data['items'][$i][0];

                    if (is_numeric($data['values'][$i])) {
                        $values[] = $data['values'][$i];
                    }
                    else {
                        // SQL-ify non-numeric data
                        // Should XSS Sanitize ALL VALUES
                        $values[] = '\'' . $this->real_escape_string($data['values'][$i]) . '\'';
                    }
                }

                $sql = 'INSERT INTO `' . $data['tables'][0][0] . '`';
                $sql .= ' (`' . implode('`, `', $items) . '`)';
                $sql .= ' VALUES(' . implode(', ', $values) . ')';

                Debug::Log($sql);
                //return $sql . PHP_EOL;
                $this->real_query($sql);
                return new MySqlDatabaseResult($this);
            break;
            case Query::TYPE_RETRIEVE:
                $sql = "SELECT";

                /* Select Items */
                if(empty($data['items'])) {
                    $sql .= " *";
                }
                else {
                    $items = array();
                    foreach($data['items'] as $value) {
                        $expanded = '`' . str_replace('.', '`.`', $value[0]) . '`';
                        if(isset($value[1])) {
                            $items[] = $expanded . ' AS `'. $value[1] . '`';
                        }
                        else {
                            $items[] = $expanded;
                        }
                    }
                    $sql .= ' ' . implode(', ', $items);
                }

                /* Select Tables*/
                $tables = array();
                foreach($data['tables'] as $value) {
                    if(isset($value[1])) {
                        $tables[] = '`' . $value[0] . '` AS `'. $value[1] . '`';
                    }
                    else {
                        $tables[] = '`' . $value[0] . '`';
                    }
                }

                $sql .= ' FROM ' . implode(', ', $tables);
            break;
            case Query::TYPE_UPDATE:
                $sql = 'UPDATE `' . $data['tables'][0][0] . '`';
                $items = array();

                for ($i = 0; $i < count($data['items']); $i++) {
                    $item = '`' . $data['items'][$i][0] . '`';

                    if (is_numeric($data['values'][$i])) {
                        $items[] = $item . ' = ' . $data['values'][$i];
                    }
                    else {
                        $items[] = $item . ' = \'' . $data['values'][$i] . '\'';
                    }
                }

                $sql .= ' SET ' . implode(',', $items);
            break;
            case Query::TYPE_DELETE:
                $sql = 'DELETE FROM `' . $data['tables'][0][0] . '`';
            break;
            default:
                trigger_error('Not recognizable query type for Query(' . $query . ').', E_USER_ERROR);
            break;
        }

        /* Where Clauses*/
        if (!empty($data['predicates']))
        {
            $sql .= ' WHERE ';
            $predicates = array();
            $links = array();
            $item = array();

            foreach ($data['predicates'] as $predicate)
            {
                if (is_array($predicate['item'])) {
                    foreach ($predicate['item'] as $column) {
                        $item[] = '`' . str_replace('.', '`.`', $column) . '`';
                    }
                }
                // else treat item like a string
                else {
                    $item = '`' . str_replace('.', '`.`', $predicate['item']) . '`';                
                }

                // Sanitize values to be stored in Database
                // Should also check for XSS
                if (is_string($predicate['value'])) {
                    $predicate['value'] = '\'' . $this->real_escape_string($predicate['value']) . '\'';
                }

                switch ($predicate['type'])
                {
                    case Query::IS_EQUAL:
                        $predicates[] = $item . ' = ' . $predicate['value'];
                    break;
                    case Query::IS_NOT_EQUAL:
                        $predicates[] = $item . ' <> ' . $predicate['value'];
                    break;
                    case Query::IS_LESS_THAN:
                        $predicates[] = $item . ' < ' . $predicate['value'];
                    break;
                    case Query::IS_LESS_OREQ:
                        $predicates[] = $item . ' <= ' . $predicate['value'];
                    break;
                    case Query::IS_GREATER_THAN:
                        $predicates[] = $item . ' > ' . $predicate['value'];
                    break;
                    case Query::IS_GREATER_OREQ:
                        $predicates[] = $item . ' >= ' . $predicate['value'];
                    break;
                    case Query::IS_BETWEEN:
                        $predicates[] = '(' . $item . ' >= ' . $predicate['value']['min'] . ' AND '. $item . ' <= ' . $predicate['value']['max'] .')';
                        //$predicates[] = $item . ' BETWEEN ' . $predicate['value']['min'] . ' AND ' . $predicate['value']['max'];
                    break;
                    case Query::IS_NOT_BETWEEN:
                        $predicates[] = '(' . $item . ' < ' . $predicate['value']['min'] . ' OR '. $item . ' > ' . $predicate['value']['max'] .')';
                        //$predicates[] = $item . ' NOT BETWEEN ' . $predicate['value']['min'] . ' AND ' . $predicate['value']['max'];
                    break;
                    case Query::IS_IN:
                        for ($i = 0; $i < count($predicate['value']); $i++) {
                            if (!is_numeric($predicate['value'][$i])) {
                                $predicate['value'][$i] = '\'' . $predicate['value'][$i] . '\'';
                            }
                        }
                        $predicates[] = $item . ' IN (' . implode(', ', $predicate['value']) . ')';
                    break;
                    case Query::IS_NOT_IN:
                        for ($i = 0; $i < count($predicate['value']); $i++) {
                            if (!is_numeric($predicate['value'][$i])) {
                                $predicate['value'][$i] = '\'' . $predicate['value'][$i] . '\'';
                            }
                        }
                        $predicates[] = $item . ' NOT IN (' . implode(', ', $predicate['value']) . ')';
                    break;
                    case Query::IS_LIKE:
                        if (is_array($item)) {
                            $item = 'CONCAT(' . implode(',\' \',', $item) . ')';
                        } 
                        $predicates[] = $item . ' LIKE \'' . $predicate['value'] . '\'';
                    break;
                    case Query::IS_NOT_LIKE:
                        if (is_array($item)) {
                            $item = 'CONCAT(' . implode(',\' \',', $item) . ')';
                        } 
                        $predicates[] = $item . ' NOT LIKE \'' . $predicate['value'] . '\'';
                    break;
                    case Query::IS_NULL:
                        $predicates[] = $item . ' IS NULL';
                    break;
                    case Query::IS_NOT_NULL:
                        $predicates[] = $item . ' IS NOT NULL';
                    break;
                }


                if ($predicate['link'] === Query::LINK_AND) {
                    $links[] = ' AND ';
                }
                elseif ($predicate['link'] === Query::LINK_OR) {
                    $links[] = ' OR ';
                }
                else {
                    $links[] = null;
                }
            }

            if (!empty($data['clusters'])) {
                // Start with the last cluster
                list($link, $cluster) = array_pop($data['clusters']);

                $cluster = explode(',', $cluster);
                for ($i = 0; $i < count($cluster); $i++) {
                    if (is_numeric($cluster[$i])) {
                        $cluster[$i] = '<<' . $cluster[$i] . '>>';
                    }
                }
                $cluster = implode(',', $cluster);

                if ($link === Query::LINK_AND) {
                    $cluster = str_replace(',', ' AND ', $cluster);
                }
                else {
                    $cluster = str_replace(',', ' OR ', $cluster);
                }

                // Build complete where clause by inserting clusters
                foreach (array_reverse($data['clusters']) as $key => $value) {
                    list($link, $phrase) = $value;

                    $phrase = explode(',', $phrase);
                    for ($i = 0; $i < count($phrase); $i++) {
                        if (is_numeric($phrase[$i])) {
                            $phrase[$i] = '<<' . $phrase[$i] . '>>';
                        }
                    }
                    $phrase = implode(',', $phrase);

                    $cluster = str_replace($key, '(' . $phrase . ')', $cluster);

                    if ($link === Query::LINK_AND) {
                        $cluster = str_replace(',', ' AND ', $cluster);
                    }
                    else {
                        $cluster = str_replace(',', ' OR ', $cluster);
                    }
                }

                // Replace numbers with predicates
                for ($i = 0; $i < count($predicates); $i++) {
                    $cluster = str_replace('<<' . ($i + 1) . '>>', $predicates[$i], $cluster);
                }
            }
            // Use basic predicate logic
            else {
                $cluster = null;
                $end = count($predicates);
                for ($i = 0; $i < $end; $i++) {
                    if ($i != ($end - 1) && $links[$i] === null) {
                        trigger_error('No link type has been set for predicate \'' . $predicates[($i + 1)] . '\'. Using \'AND\'.', E_USER_WARNING);
                        $links[$i] = ' AND ';
                    }
                    $cluster .= $predicates[$i].$links[$i];
                }
            }

            //Append to query
            $sql .= $cluster;
        }

        /* Query Optimizers */
        if (!empty($data['order'])) {
            $order = array();

            for ($i = 0; $i < count($data['order']); $i++) {
                if ($data['order'][$i][1] === Query::SORT_ASC) {
                    $data['order'][$i][1] = 'ASC';
                }
                else {
                    $data['order'][$i][1] = 'DESC';
                }
                $order[] = '`' . $data['order'][$i][0] . '` ' . $data['order'][$i][1] ;//implode(' ',$data['order'][$i]);
            }

            $sql .= ' ORDER BY ' . implode(', ',$order);
        }
        if (isset($data['limit'])) {
            $sql .= ' LIMIT ' . $data['limit'];

            if (isset($data['offset'])) {
                $sql .= ' OFFSET ' . $data['offset'];
            }
        }

        Debug::Log($sql);
        //return $sql . PHP_EOL;
        $this->real_query($sql);
        return new MySqlDatabaseResult($this);
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

    /* Inherited Properties:
    int $current_field ;
    int $field_count;
    array $lengths;
    int $num_rows;
    $type;
    */

    /* Inherited Methods:
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
