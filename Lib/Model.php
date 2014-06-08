<?php

namespace Web\Framework\Lib;

// Check for direct file access
if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

// Used classes
use Web\Framework\Lib\Data;
use Web\Framework\Lib\Abstracts\MvcAbstract;

/**
 * ORM like class to read from and write data to db
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Model extends MvcAbstract
{
    /**
     * Framwork component type
     * @var string
     */
    protected $type = 'Model';

    /**
     * Tablename
     * @var string
     */
    protected $tbl = '';

    /**
     * Table alias
     * @var string
     */
    protected $alias = '';

    /**
     * Database table prefix
     * @var string
     */
    protected $prefix = NULL;

    /**
     * Name of primary key
     * @var string
     */
    protected $pk = '';

    /**
     * Distinct flag
     * @var bool
     */
    private $distinct = false;

    /**
     * Filter statement
     * @var string
     */
    private $filter = '';

    /**
     * Group by statement
     * @var string
     */
    private $group_by = '';

    /**
     * Queryparameters
     * @var array
     */
    private $params = array();

    /**
     * Query types
     * @var string
     */
    private $query_type = 'row';

    /**
     * Order by string
     * @var string
     */
    private $order = '';

    /**
     * Having string
     * @var string
     */
    private $having = '';

    /**
     * Limit statement
     * @var string
     */
    private $limit = array();

    /**
     * List of fileds to query
     * @var array
     */
    private $fields = array();

    /**
     * Join storage for multiple table joins
     * @var unknown
     */
    private $join = array();

    /**
     * Flag for $this->data cleaning before insert or update
     * @var bool
     */
    private $clean = 1;

    /**
     * Validation rules.
     * Set in Childmodels. Here only for error prevention
     * @var array
     */
    protected $validate = array();

    /**
     * Errorstorage filled by validator
     * @var array
     */
    private $errors = array();

    /**
     * Stores the definitions of tables fields
     * @var \stdClass
     */
    private $columns;

    /**
     * List of fields which are serializable
     * @var array
     */
    protected $serialized = array();

    /**
     * Storage for the query results
     * @var Data
     */
    public $data = false;

    /**
     * Storage for attached validator object
     * @var validator
     */
    private $validator;

    /**
     * Stores sql string
     * @var unknown
     */
    private $sql = '';

    /**
     * Database instance
     * @var Database
     */
    private $db;

    /**
     * Get an instance of a model by name as parameter or by controller name
     *
     * @param string $app The name of the app our model is of
     * @param string $model Case sensitive name of our model
     * @param App $app Optional: You can provide an App object which will be used instead of getting the App object by calling.
     * @return Model
     */
    public static function factory(App $app, $model_name)
    {
        if (!isset($app) || (isset($app) && !$app instanceof App))
            Throw new Error('App object not set', 1000);

        // Framework, SMF or App model? Create proper namespace.
        $model_class = ($app->isSecure() ? '\\Web\\Framework\\AppsSec' : '\\Web\\Apps') . "\\" . $app->getName() . '\\Model\\' . $model_name . 'Model';

        // Create and return modelobject
        $model = new $model_class($model_name);

        // Inject app object
        $model->injectApp($app);

        return $model;
    }

    /**
     * Private constructor to prevent manual model object creation via new statment.
     */
    protected function __construct($name)
    {
        // Set basic data
        $this->setName($name);
        $this->setPK();

        // Inject db object
        $this->db = Database::getInstance();

        // Load table definition
        $this->getColumns();

        // Inject validator object
        $this->validator = new Validator();
        $this->validator->attachModel($this);
    }

    /**
     * Access to the apps config.
     * Without any paramter set this method returns the complete config.
     * With only key set, it returns the value associated with it.
     * Set key and value, and the config will be updated.
     * @param string $key
     * @param string $val
     */
    public function cfg($key = null, $val = null)
    {
        return $this->app->cfg($key, $val);
    }

    /**
     * Loads the table columns and stores them in the $columns property
     */
    public function getColumns()
    {
        // No related table set and no data definition set?
        if (empty($this->tbl) && !isset($this->definition))
            return false;

            // When no related table is set, the definition for the uses datafields
            // has to be set in the model. Otherwise you can not use the validator.
        if (empty($this->tbl) && isset($this->definition))
        {
            $this->columns = Lib::toObject($this->definition);
            unset($this->definition);
            return;
        }

        // Get fielddefinition from db table
        $structure = $this->db->getTblStructure($this->tbl);

        // Get the columns
        $this->columns = $structure->columns;

        // Get primary key column
        $this->pk = $structure->indexes->PRIMARY->columns->{0};

        return true;
    }

    /**
     * Returns the type of the requested field.
     * Returns false on none existing fields.
     * @param sting $fld
     * @return Ambigous <boolean, string>
     */
    public function getFieldtype($fld)
    {
        return isset($this->columns->{$fld}) ? $this->db->convertType($this->columns->{$fld}->type) : false;
    }

    /**
     * Checks the definition of the filed if it allows null values.
     * @param string $fld The name of the field to check
     */
    public function isNullAllowed($fld)
    {
        return $this->columns->{$fld}->null == 1 ? true : false;
    }

    /**
     * Search for a record by it's id.
     * If no fieldlist set, you will get all the complete row with all columns.
     * @param int $key
     * @param array $fields
     * @return array
     */
    public function find($key, $fields = null, $callbacks = array())
    {
        $this->reset(true);
        $this->setFilter($this->alias . '.' . $this->pk . '= {int:' . $this->pk . '}');
        $this->setParameter($this->pk, $key);

        if (isset($fields))
        {
            if (!is_array($fields))
                $fields = array(
                    $fields
                );

            $this->setField($fields);
        }

        return $this->read('row', $callbacks);
    }

    /**
     * Shorthand method to search for data.
     * @param string $filter
     * @param arrray $params
     * @param string $read_mode
     * @param array $callbacks
     * @return bool Data
     */
    public function search($filter, $params = array(), $read_mode = '*', $callbacks = array())
    {
        $this->reset(true);
        $this->setFilter($filter, $params);
        return $this->read($read_mode, $callbacks);
    }

    /**
     * Checks for a record and returns true if exists or false if not.
     * @param int $key
     * @return boolean
     */
    public function exists($key)
    {
        $this->setFilter($this->alias . '.' . $this->pk . '= {int:val}');
        $this->addParameter('val', $key);
        return count((array) $this->read()) == 0 ? false : true;
    }

    /**
     * Set the tablename we use.
     * Do not set prefixes. This will be done by the model on buildSqlString()
     * @param string $val name of table
     */
    public function setTable($val, $force = false)
    {
        // $tbl not set in model?
        if (!isset($this->tbl) || $force === true)
            $this->tbl = String::uncamelize($val);

        return $this;
    }

    /**
     * Sets an alias for the table.
     * if you provie a paramater to this method it woult be taken as alias
     */
    public function setAlias($alias)
    {
        if (isset($this->tbl) && isset($alias))
            $this->alias = $alias;

        return $this;
    }

    /**
     * Flags model to use DISTINCT mode in queries
     * @return \Web\Framework\Lib\Model
     */
    public function isDistinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * The are multiple types you can query the db.
     * By default we use the all-type, which returns an array of records where
     * the primary key is the index and each recordcolumns the datapart.
     *
     * @deprecated
     *
     * @param string $query_type
     */
    public function setQuerytypeX($query_type)
    {
        $this->query_type = $query_type;
        return $this;
    }

    /**
     * Sets the primary key of the table
     * Every table (except web_config) of the WebMod needs a integer based
     * primary key.
     * The primary key must be build like this: id_<name of table>
     * Example: The key column of web_news is id_news
     */
    private function setPK($val = null, $force = false)
    {
        if (isset($this->tbl))
        {
            if (!isset($this->pk) || $force === true)
                $this->pk = isset($val) ? $val : 'id_' . $this->alias;
        }

        return $this;
    }

    /**
     * Add a field to the fieldlist.
     * You can pass an array of fields or a single fieldname
     */
    public function addField($val)
    {
        // array as func param?
        if (is_array($val))
            foreach ( $val as $fld )
                $this->fields[] = $fld;
        else
            $this->fields[] = $val;

        return $this;
    }

    /**
     * Set $val as fieldlist
     */
    public function setField($val)
    {
        $this->fields = is_array($val) ? $val : array(
            $val
        );
        return $this;
    }

    /**
     * Unsets the complete fieldlists
     */
    public function resetFields()
    {
        $this->fields = array();
        return $this;
    }

    /**
     * Unsets the filterstatement and the parameterlist
     */
    public function resetFilter()
    {
        $this->filter = '';
        $this->params = array();
        return $this;
    }

    /**
     * Set a complete sql filterstatement
     * @param string $val Sql statement
     */
    public function setFilter($filter, $params = null)
    {
        $this->filter = $filter;

        if (isset($params))
            $this->params = $params;

        return $this;
    }

    /**
     * Set an integer id based filter
     * @param string $fld Id column WITHOUT tbl prefix
     * @param int $val The id you are looking for
     */
    public function setIdFilter($fld, $val)
    {
        $this->setFilter('id_' . $fld . '={int:id_' . $fld . '}');
        $this->setParameter('id_' . $fld, $val);
        return $this;
    }

    /**
     * Set an orderstatement
     * @param string $order Your order statemen
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Clears the order string
     */
    public function resetOrder()
    {
        $this->order = '';
        return $this;
    }

    /**
     * Adds named parameters to the model
     * @param mixed $data
     */
    public function addParameter()
    {
        if (!isset($this->params))
            $this->params = array();

        if (func_num_args() == 2)
            $this->params[func_get_arg(0)] = Lib::fromObjectToArray(func_get_arg(1));

        if (func_num_args() == 1 && (is_array(func_get_arg(0)) || is_object(func_get_arg(0))))
        {
            foreach ( func_get_arg(0) as $key => $val )
                $this->params[$key] = Lib::fromObjectToArray($val);
        }

        return $this;
    }

    /**
     * Sets named parameters to the model
     * @param mixed $data
     */
    public function setParameter()
    {
        if (func_num_args() == 2)
        {
            $this->params = array();
            $this->params[func_get_arg(0)] = func_get_arg(1);
        }

        if (func_num_args() == 1 && is_array(func_get_arg(0)))
        {
            $this->params = array();

            foreach ( func_get_arg(0) as $k => $v )
                $this->params[$k] = $v;
        }

        if (func_num_args() == 1 && is_object(func_get_arg(0)))
            $this->params = (array) func_get_arg(0);

        return $this;
    }

    /**
     * Resets the query parameter
     * @return \Web\Framework\Lib\Model
     */
    public function resetParameter()
    {
        $this->params = array();
        return $this;
    }

    /**
     * Set the upper bound of limit statement
     *
     * @param int $val
     */
    public function setLimit($val1, $val2)
    {
        $this->limit['lower'] = (int) $val1;
        $this->limit['upper'] = (int) $val2;
        return $this;
    }

    /**
     * Set the upper bound of limit statement
     *
     * @param int $val
     */
    public function setUpperLimit($val)
    {
        $this->limit['upper'] = (int) $val;
        return $this;
    }

    /**
     * Set the lower bound of limit statement
     * @param int $val
     */
    public function setLowerLimit($val)
    {
        $this->limit['lower'] = (int) $val;
        return $this;
    }

    /**
     * Clears the limit settings
     */
    public function resetLimit()
    {
        $this->limit = array();
        return $this;
    }

    /**
     * Add fields for GROUP BY clause
     * Can be an array of values to group by
     */
    public function setGroupBy($val)
    {
        if (is_array($val))
            $val = implode(', ', $val);

        $this->group_by = $val;
        return $this;
    }

    /**
     * Clears the group by string
     */
    public function resetGroupBy()
    {
        $this->group_by = '';
        return $this;
    }

    /**
     * Add the table to join from
     * @param string $tbl
     * @param string $as
     * @param string $by
     * @param string $condition
     */
    public function addJoin($tbl, $as, $by, $condition)
    {
        $this->join[] = array(
            'tbl' => $tbl,
            'as' => $as,
            'by' => $by,
            'cond' => $condition
        );

        return $this;
    }

    /**
     * Resets join array and calls addJoin()
     * @param string $tbl
     * @param string $as
     * @param string $by
     * @param string $condition
     */
    public function setJoin($tbl, $as, $by, $condition)
    {
        return $this->resetJoin()->addJoin($tbl, $as, $by, $condition);
    }

    /**
     * Reset join definitions
     */
    public function resetJoin()
    {
        $this->join = array();
        return $this;
    }

    /**
     * Set the data array to the parameter value.
     * Use this if you want to reset the data array with new content.
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Clears all set data from model
     */
    public function resetData()
    {
        $this->data = false;
        return $this;
    }

    /**
     * Resets the error storage
     */
    public function resetErrors()
    {
        $this->errors = array();
        return $this;
    }

    /**
     * Sets the cleanmode to use.
     * This means how the model treats values set to send to the db.
     * 0 = off		data will be send to db as it is
     * 1 = on		data will be sanitized before sent to db
     *
     * @param int $mode
     * @return \Web\Framework\Lib\Model
     */
    public function cleanMode($mode)
    {
        switch ($mode)
        {
            case 'off' :
                $this->clean = 0;
                break;
            case 'on' :
                $this->clean = 1;
                break;
            default :
                $this->clean = 1;
                break;
        }
        return $this;
    }

    /**
     * Builds the sql string for select queries.
     * @return string smf coded sql
     */
    private function buildSqlString()
    {
        $params = array();

        $join = '';
        $filter = '';
        $order = '';
        $limit = '';

        // Create the fieldprefix. If given as alias use this, otherwise we use the tablename
        $field_prefifx = !empty($this->alias) ? $this->alias : '{db_prefix}' . $this->tbl;

        // Biuld joins
        if (!empty($this->join))
        {
            $tmp = array();

            foreach ( $this->join as $def )
                $tmp[] = ' ' . $def['by'] . ' JOIN ' . '{db_prefix}' . (isset($def['as']) ? $def['tbl'] . ' AS ' . $def['as'] : $def['join']) . ' ON (' . $def['cond'] . ')';
        }

        $join = isset($tmp) ? implode(' ', $tmp) : '';

        // Create fieldlist
        if (!empty($this->fields))
        {
            // Add `` to some field names as reaction to those stupid developers who chose systemnames as fieldnames
            foreach ( $this->fields as $key_field => $field )
            {
                if (in_array($field, array(
                    'date',
                    'time'
                )))
                    $this->fields[$key_field] = '`' . $field . '`';
            }

            $fieldlist = implode(', ', $this->fields);
        } else
        {
            $fieldlist = '*';
        }

        // Create filterstatement
        $filter = !empty($this->filter) ? ' WHERE ' . $this->filter : null;

        // Create group by statement
        $group_by = !empty($this->group_by) ? ' GROUP BY ' . $this->group_by : null;

        // Create having statement
        $having = !empty($this->having) ? ' HAVING ' . $this->having : null;

        // Create order statement
        $order = !empty($this->order) ? ' ORDER BY ' . $this->order : null;

        // Create limit statement
        if (!empty($this->limit))
        {
            $limit = ' LIMIT ';

            if (isset($this->limit['lower']))
                $limit .= $this->limit['lower'];

            if (isset($this->limit['lower']) && isset($this->limit['upper']))
                $limit .= ',' . $this->limit['upper'];
        }

        // We need a string for the table. if there is an alias, we have to set it
        $tbl = !empty($this->alias) ? $this->tbl . ' AS ' . $this->alias : $this->tbl;

        // Is this an distinct query?
        $distinct = $this->distinct ? 'DISTINCT ' : '';

        // Create sql statement by joining all parts from above
        $this->sql = 'SELECT ' . $distinct . $fieldlist . ' FROM {db_prefix}' . $tbl . $join . $filter . $group_by . $having . $order . $limit;

        return $this->sql;
    }

    /**
     * Get the sql string which will be send to db
     * @return \Web\Framework\Lib\mysql_result
     */
    public function getSqlString()
    {
        return $this->buildSqlString();
    }

    /**
     * Returns debug informations about a query
     * @return string
     */
    public function getQueryDebug()
    {
        $sql = $this->getSqlString();

        $out = '
		<div class="debug">
			<h3>SQL</h3>
			<p>' . $sql . '</p>
			<h3>Params</h3>
			' . $this->debug($this->params) . '
			<h3>Full query</h3>
			' . $this->db->quote($sql, $this->params) . '
		</div>';

        return $out;
    }

    /**
     * Basic mathod to query data from db
     * @param string $query_type
     * @param string $callback Array of methodnames to call on loops through records
     * @return Ambigous <\Web\Framework\Lib\mixed, number, mixed, unknown>|stdClass
     */
    public function read($query_type = 'row', $callbacks = array(), $preserve = false)
    {
        // Manually set querytype
        if (isset($query_type))
            $this->query_type = $query_type;

            // On count we count only the pk column
        if ($query_type == 'num')
            $this->setField('Count(' . $this->pk . ')');

            // On pklist we only want the pk column
        if ($query_type == 'key')
            $this->setField($this->pk);

            // Build the sql string
        $this->buildSqlString();

        // Array check and conversion for list of serialized columns
        if (!is_array($this->serialized))
            $this->serialized = (array) $this->serialized;

            // Array check for callback parameter
        if (!is_array($callbacks))
            $callbacks = (array) $callbacks;

            // Are we trying to entend non exiting data?
        if ($this->query_type == 'ext' && $this->data == false)
            // Create Data object to prevent errors when it comes to extending
            $this->data = new Data();

            // Do the query!
        $res = $this->db->query($this->sql, $this->params);

        // Reset data on all queries not of type 'ext'
        if ($this->query_type !== 'ext')
            $this->resetData();

            // Process result
        switch ($this->query_type)
        {
            /**
             * Reads one record from db and extends the current data object with the fields and values
             * that are not set.
             *
             * Extends $this->data
             */
            case 'ext' :
                $counter = 0;

                while ( $row = $this->db->fetchAssoc($res) )
                {
                    $counter++;

                    foreach ( $row as $col => $val )
                    {
                        // Add this key/value if it is not already present. Checks value to be unserialized.
                        if (!isset($this->data->{$col}))
                            $val = in_array($col, $this->serialized) ? unserialize($val) : $val;

                    }

                    $this->data = $this->runCallbacks($callbacks, $this->data);

                    // We only want truely only one row. Not more!
                    if ($counter == 1)
                        break;
                }
                break;

            /**
             * Reads on record and returns the value of the first field.
             */
            case 'val' :
                $row = $this->db->fetchRow($res);

                if ($this->db->numRows($res) != 0 || !empty($row[0]))
                    $this->data = Lib::isSerialized($row[0]) ? unserialize($row[0]) : $row[0];

                $this->data = $this->runCallbacks($callbacks, $this->data);

                break;

            /**
             * Reads only the first two columns.
             * Good for key=>val data
             */
            case '2col' :
                if ($this->db->numRows($res) > 0)
                {
                    $this->data = new Data();

                    while ( $row = $this->db->fetchRow($res) )
                    {
                        $row = $this->runCallbacks($callbacks, $row);

                        // Skip row which is set to false by callback function
                        if ($row == false)
                            continue;

                        $this->data->{$row[0]} = $row[1];
                    }
                }
                break;

            /**
             * Reads all columns in all rows.
             */
            case '*' :

                while ( $row = $this->db->fetchAssoc($res) )
                {
                	// Prepare data object
                	if (!$this->data)
                		$this->data = new Data();

                    // Convert row to record object
                    $record = new Data($row);

                    // Serializationcheck
                    foreach ( $this->serialized as $col_to_unserialize )
                    {
                        if (isset($record->{$col_to_unserialize}))
                            $record->{$col_to_unserialize} = unserialize($record->{$col_to_unserialize});
                    }

                    // Run callback methods
                    $record = $this->runCallbacks($callbacks, $record, true);

                    // Not to use flagged records will be skipped.
                    if ($record == false)
                        continue;

                        // Get the index name
                    $cols = array_keys($row);

                    // Publish record to data
                    $this->data->{$record->{$cols[0]}} = $record;
                }
                break;

            /**
             * Reads th first and only the first row of a result
             */
            case 'row' :
                $counter = 0;

                while ( $row = $this->db->fetchAssoc($res) )
                {
                    $counter++;

                    $row = new Data($row);

                    foreach ( $this->serialized as $col_to_unserialize )
                    {
                        if (isset($row->{$col_to_unserialize}))
                            $row->{$col_to_unserialize} = unserialize($row->{$col_to_unserialize});
                    }

                    $this->data = $this->runCallbacks($callbacks, $row);

                    if ($counter == 1)
                        break;
                }

                break;

            /**
             * Reads one value
             */
            case 'num' :
                $row = $this->db->fetchRow($res);
                $this->data = $this->runCallbacks($callbacks, $row[0]);
                break;

            case 'key' :
                if ($this->db->numRows($res))
                {
                    $this->data = array();

                    while ( $row = $this->db->fetchRow($res) )
                    {
                        $row = $this->runCallbacks($callbacks, $row, true);

                        if ($row == false)
                            continue;

                        $this->data[$row[0]] = $row[0];
                    }
                }
                break;

            default :
                Throw new Error('Wrong query type', 1000, array($this->query_type));
                break;
        }

        $this->db->freeResult($res);

        return $this->data;
    }

    /**
     * For direct sql calls avoiding the model system.
     * @param string $sql (need to be smf conform)
     * @param array $params (optional paramter array)
     */
    public function sqlQuery($sql, $params = array())
    {
        $this->db->query($sql, $params);
    }

    /**
     * Save is a combined method to insert and/or update records.
     * This method reads all entries of $this->data and handles it's entries
     * by analyzing the records content.
     * If the model pk is found in data and is not empty, the method will
     * run an update on this record using th pk value as filter.
     * Is the pk not set the method will perfom an insert, store the created
     * pk value and returns it after the data has been processed.
     * @return boolean multitype:\Web\Framework\Lib\id_of_table
     */
    protected function save($validate = true)
    {
        // Make sure $this->data is an Data object
        if (!$this->data instanceof Data)
        {
            $this->addError('@', 'Save: Data given to save is no Dataobject.');
            return false;
        }

        // Validate given data.
        if ($validate)
            $this->validate();

        // Erros on validation means to end the saving process and return a boolean false.
        if ($this->hasErrors())
            return false;

        // When the pk isset in a record, this is the signal for an update.
        if (isset($this->data->{$this->pk}) && !empty($this->data->{$this->pk}))
            $this->internalUpdate();

        // No set pk or empty pk in record signals that this is an insert.
        if (!isset($this->data->{$this->pk}) || empty($this->data->{$this->pk}))
            return $this->insert();
    }

    /**
     * Insert method used by save()
     * @return mixed PK value of created record
     */
    private function insert()
    {
        // Run beforeCreate event methods and stop when one of them return bool false
        if ($this->runBefore('create') === false)
            return false;

        // Create tablename
        $tbl = '{db_prefix}' . $this->tbl;

        // Prepare query and content arrays
        $fields = array();
        $values = array();
        $keys = array();

        // Build insert fields
        foreach ( $this->data as $fld => $val )
        {
            // Skip datafields not in definition
            if (!$this->isField($fld))
                continue;

            // Regardless of all further actions, check and cleanup the value
            $val = $this->checkFieldvalue($fld, $val);

            // Put fieldname and the fieldtype to the fields array
            $fields[$fld] = $this->getFieldtype($fld);

            // Object or array values are stored serialized to db
            $values[] = is_array($val) || is_object($val) ? serialize($val) : $val;
        }

        // Add name of primary key field
        $keys[0] = $this->pk;

        // Run query and store insert id as pk value
        $this->data->{$this->pk} = $this->db->insert('insert', $tbl, $fields, $values, $keys);

        return $this->data->{$this->pk};
    }

    // Update method used by save()
    private function internalUpdate()
    {
        $params = array();

        // Run before update methods and stop here if the return bool false
        if ($this->runBefore('update') === false)
            return false;

        // Define fieldlist array
        $fieldlist = array();

        // Build updatefields
        foreach ( $this->data as $fld => $val )
        {
            // Skip datafields not in definition
            if (!$this->isField($fld))
                continue;

            $val = $this->checkFieldvalue($fld, $val);
            $type = $val == 'NULL' ? 'raw' : $this->getFieldtype($fld);

            $fieldlist[] = $this->alias . '.' . $fld . '={' . $type . ':' . $fld . '}';
            $params[$fld] = $val;
        }

        // Create filter
        $filter = ' WHERE ' . $this->alias . '.' . $this->pk . '={' . $this->getFieldtype($this->pk) . ':' . $this->pk . '}';

        // Even if the pk value is present in data, we set this param manually to prevent errors
        $params[$this->pk] = $this->data->{$this->pk};

        // Build fieldlist
        $fieldlist = implode(', ', $fieldlist);

        // Create complete sql string
        $sql = "UPDATE {db_prefix}{$this->tbl} AS {$this->alias} SET {$fieldlist}{$filter}";

        // Run query
        $this->db->query($sql, $params);

        // Run after update event methods
        if ($this->runAfter('update') === false)
            return false;
    }

    /**
     * Updates records of model with the data which was set
     */
    public function update()
    {
        if (isset($this->fields) && $this->data)
            Throw new Error('Fieldset and data records are set for update. You can only have the one or the other. Not both. Stopping Update.');

        $fieldlist = array();

        if (isset($this->fields))
        {
            foreach ( $this->fields as $fld )
            {
                if (!$this->getFieldtype($fld))
                    Throw new Error('The field you set to be updated does not exist in this table.<br />Table: ' . $this->tbl . '<br>Field: ' . $fld);

                if (!array_key_exists($fld, $this->params))
                    Throw new Error('The field "' . $fld . '" you set to be updated has no matching parameter.');

                $fieldlist[] = $this->alias . '.' . $fld . '={' . $this->getFieldtype($fld) . ':' . $fld . '}';

                // sanitize input?
                $this->params[$fld] = $this->checkFieldvalue($fld, $this->params[$fld]);
            }
        }

        if ($this->hasData())
        {
            // Build updatefields
            foreach ( $this->data as $fld => $val )
            {
                if (!$this->getFieldtype($fld))
                    Throw new Error('The field you set to be updated does not exist in this table.<br>Table: ' . $this->tbl . '<br>Field: ' . $fld);

                $fieldlist[] = $this->alias . '.' . $fld . '={' . $this->getFieldtype($fld) . ':' . $fld . '}';
                $this->params[$fld] = $this->checkFieldAndValue($fld, $val);
            }
        }

        // build fieldlist
        $fieldlist = implode(', ', $fieldlist);

        // create filterstatement
        $filter = isset($this->filter) ? ' WHERE ' . $this->filter : '';

        // create complete sql string
        $sql = "UPDATE {db_prefix}{$this->tbl} AS {$this->alias} SET {$fieldlist}{$filter}";

        $this->db->query($sql, $this->params);
    }

    /**
     * Event manager for onBefore actions
     * @param unknown $when
     * @return boolean
     */
    private function runBefore($when)
    {
        if ($when == 'create' && isset($this->beforeCreate))
        {
            if (!is_array($this->beforeCreate))
                $this->beforeCreate = array(
                    $this->beforeCreate
                );

            foreach ( $this->beforeCreate as $method_name )
            {
                if (method_exists($this, $method_name))
                    $ok = $this->{$method_name}();

                    // this is an exitcheck if runBefore func returned false,
                    // so the whole creation process can be stopped then
                if (isset($ok) && $ok === false)
                    return false;
            }
        }

        if ($when == 'update' && isset($this->beforeUpdate))
        {
            if (!is_array($this->beforeUpdate))
                $this->beforeUpdate = array(
                    $this->beforeUpdate
                );

            foreach ( $this->beforeUpdate as $method_name )
            {
                if (method_exists($this, $method_name))
                    $ok = $this->{$method_name}();

                    // this is an exitcheck if runBefore func returned false,
                    // so the whole creation process can be stopped then
                if (isset($ok) && $ok === false)
                    return false;
            }
        }
    }

    /**
     * Event manager for onAfter action
     * @param string $when event
     * @param referenced $data
     */
    private function runAfter($when, &$data = null)
    {
        if ($when == 'create' && isset($this->afterCreate))
        {
            if (!is_array($this->afterCreate))
                $this->afterCreate = array(
                    $this->afterCreate
                );

            foreach ( $this->afterCreate as $method_name )
            {
                if (method_exists($this, $method_name))
                    $this->{$method_name}($data);
            }
        }

        if ($when == 'update' && isset($this->afterUpdate))
        {
            if (!is_array($this->afterUpdate))
                $this->afterUpdate = array(
                    $this->afterUpdate
                );

            foreach ( $this->afterUpdate as $method_name )
            {
                if (method_exists($this, $method_name))
                    $this->{$method_name}($data);
            }
        }
    }

    /**
     * Deletes the database record by using a pk value as filter base or by a defined set of model filter and parameters.
     * Setting the $pk parameter will override a model filter.
     * @param mixed $pk
     */
    public function delete($pk = null)
    {
        $filter = isset($this->filter) ? ' WHERE ' . $this->filter : null;

        if (isset($pk))
        {
            $filter = ' WHERE ' . $this->pk . '={int:pk}';
            $this->params = array(
                'pk' => $pk
            );
        }

        $sql = "DELETE FROM {db_prefix}{$this->tbl}{$filter}";

        $this->db->query($sql, $this->params);

        $this->resetFilter();
        $this->resetParameter();
    }

    /**
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * Truncates the complete tablecontent of the table linked to the model
     * WITHOUT any further confirmation question
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     */
    public function truncate()
    {
        $sql = 'TRUNCATE {db_prefix}' . $this->tbl;
        $this->db->query($sql);
    }

    /**
     * Stars the validation process and returns true or false
     * @return boolean
     */
    public function validate()
    {
        $this->validator->validate();
        return $this->hasErrors() ? false : true;
    }

    /**
     * Adds a single rule for "$field" to the validator.
     * @param string $field The fieldname the validator is used for
     * @param string|array $rule Validator rule
     */
    public function addValidationRule($field, $rule)
    {
        $this->validate[$field][] = $rule;
        return $this;
    }

    /**
     * Adds an set (array) of rules for "$field" to the validator
     * @param string $field The fieldname the validator is used for
     * @param array $ruleset List of rules to add to the validator
     */
    public function addValidationRuleset($field, $ruleset)
    {
        if (!is_array($ruleset))
            $ruleset = (array) $ruleset;

        foreach ( $ruleset as $rule )
            $this->validate[$field][] = $rule;

        return $this;
    }

    /**
     * Add an error to the models errorlist.
     * If you want do set global and not field related errors, set $fld to '@'.
     * @param string $fld
     * @param string $msg
     */
    public function addError($fld, $msg)
    {
        if (!isset($this->errors[$fld]))
            $this->errors[$fld] = array();

        if (!is_array($msg))
            $msg = array(
                $msg
            );

        foreach ( $msg as $val )
            $this->errors[$fld][] = $val;

        return $this;
    }

    /**
     * Checks errors in the model and returns true or false
     * @return boolean
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Checks for no errors in the model and returns true or false
     * @return boolean
     */
    public function hasNoErrors()
    {
        return empty($this->errors);
    }

    /**
     * Returns the models errorlist
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Checks for set data and returns true or false
     * @return boolean
     */
    public function hasData()
    {
        return $this->data == false ? false : true;
    }

    public function hasNoData()
    {
        return $this->data == false ? true : false;
    }

    /**
     * Checks the fields for
     * @param string $fld Name of field to check
     * @param mixed $val Value to check
     * @return mixed The checked and processed value
     */
    public function checkFieldvalue($fld, $val)
    {
        // trim the string, baby!
        if (is_string($val))
            $val = trim($val);

            // convert string numbers into correct fieldtypes
        if ($this->isField($fld) && ($this->getFieldtype($fld) == 'int' || $this->getFieldtype($fld) == 'float') && is_string($val))
        {
            switch ($this->getFieldtype($fld))
            {
                case 'int' :
                    $val = intval($val);
                    break;

                case 'float' :
                    $val = floatval($val);
                    break;
            }
        }

        // check for not allowed empty field value
        if ($val === '' && $this->isField($fld) && $this->isNullAllowed($fld) == false)
        {
            switch ($this->getFieldtype($fld))
            {
                case 'string' :
                    $val = '';
                    break;

                case 'int' :
                case 'float' :
                    $val = 0;
                    break;
            }
        }

        if (is_string($val) && $this->clean == 1)
            $val = Lib::sanitizeUserInput($val);

        if (in_array($fld, $this->serialized))
            $val = serialize($val);

        return $val;
    }

    /**
     * Checks the parameter to be a field of the models table
     * @param string $fld
     * @return boolean
     */
    private function isField($fld)
    {
        return isset($this->columns->{$fld});
    }

    /**
     * Resets the model and all made changes.
     * If you set the parameter to true, also all data will be erased from memory.
     * @param boolean $with_data
     */
    public function reset($with_data = false)
    {
        $this->resetFields();
        $this->resetFilter();
        $this->resetGroupBy();
        $this->resetJoin();
        $this->resetLimit();
        $this->resetOrder();
        $this->resetErrors();

        if ($with_data == true)
            $this->resetData();

        return $this;
    }

    /**
     * Counts the number of data values.
     * If data represents a record, the fieldnumber will be returned.
     * If data represents a recordset, the number of records will be returnd
     * @return number
     */
    public function countData()
    {
        return $this->data == false ? 0 : $this->data->count();
    }

    /**
     * Method to count records
     * You do not need to set any field because this method overrides already set fields with "Count(pk_name)".
     * All other settings like filters, parameters or joins will be used.
     *
     * @return int
     */
    public function count()
    {
        return $this->read('num');
    }

    /**
     * Combines current set data with the data with the same pk value loaded from db.
     * Needs a set pk value in the current data. Otherwise you receive an fatal error.
     * @throws Error
     * @return Data
     */
    public function combine()
    {
        if (!isset($this->data->{$this->pk}))
            Throw new Error('No pk key/value set for combining data.');

        $model = $this->getModel();
        $model->find($this->data->{$this->pk});

        foreach ( $this->data as $key => $val )
            $model->data->{$key} = $val;

        return $model->data;
    }

    /**
     * Compares the value of set field from DB with the value currently set in dataset
     * @param string $fld
     * @return boolean
     */
    public function compare($fld)
    {
        if (!isset($this->data->{$this->pk}))
            Throw new Error('Db field compare is allowed only with existing pk value in your current dataset.');

            // Create a new model of our current model
        $model = $this->getModel();

        // We want only the field set as parameter
        $model->setField($fld);

        // The data to compare must be the current record in db
        $model->setFilter($this->pk . '={int:' . $this->pk . '}', array(
            $this->pk => $this->data->{$this->pk}
        ));

        // Only the value of field for comparision wanted
        $value = $model->read('val');

        // Is it different from the current set data?
        return $value == $this->data->{$fld};
    }

    /**
     * Add an specific definition to a field/column
     * @param string $fld
     * @param string $key
     * @param mixed $val
     */
    public function addColumn($fld, $key, $val)
    {
        if (!isset($this->columns))
            $this->columns = new Data();

        if (!isset($this->columns->{$fld}))
            $this->columns->{$fld} = new Data();

        $this->columns->{$fld}->{$key} = $val;

        return $this;
    }

    /**
     * Wrapper function for $this->app->getModel($model_name).
     * There is a little
     * difference in using this method than the long term. Not setting a model name
     * means, that you get a new instance of the currently used model.
     * @param string $model_name Optional: When not set the name of the current model will be used
     * @return Model
     */
    public function getModel($model_name = null)
    {
        if (!isset($model_name))
            $model_name = $this->getName();

        return $this->app->getModel($model_name);
    }

    /**
     * Executes callbacks.
     * Takes care of callbacks defined in a different model of the same app.
     * @param array $callbacks The name of callbacks to run
     * @param mixed $data Data to which will be processed by callback
     * @param bool $exit_on_false Optional flag to stop processing callbacks as soon as one callback methos return boolean false.
     * @return mixed Processed $data
     */
    public function runCallbacks($callbacks, $data, $exit_on_false = false)
    {
        foreach ( $callbacks as $callback )
        {
            // Callback method in a different model?
            if (strpos($callback, '::') !== false)
            {
                list($model_name, $callback) = explode('::', $callback);
                $model = $this->getModel($model_name);
                $data = $model->{$callback}($data);
            } else
                $data = $this->{$callback}($data);

            // Stop processing as soon as return value of callback is boolean false.
            if ($exit_on_false && $data === false)
                break;
        }

        return $data;
    }

    public function __get($key)
    {
        if (strpos($key, '@') !== 0)
            return;

        $key = substr($key, 1);

        if ($this->data !== false && isset($this->data->{$key}))
            return $this->data->{key};
    }
}
?>
