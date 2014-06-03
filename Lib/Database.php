<?php

namespace Web\Framework\Lib;

// Used classes
use Web\Framework\Lib\Abstracts\SingletonAbstract;

/**
 * SMF db wrapper Class to work as some kind of ORM
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
class Database extends SingletonAbstract
{

    /**
     * Conversionlist from db fieldtype to smf fieldtypes
     * @var array
     */
    private $conversionlist = array(
        'text' => 'string',
        'char' => 'string',
        'int' => 'int',
        'decimal' => 'float',
        'double' => 'float',
        'float' => 'float',
        'numeric' => 'float',
        'date' => 'string',
        'time' => 'string',
        'string' => 'string'
    );

    /**
     * Sql string
     * @var string
     */
    private $sql;

    /**
     * Query params
     * @var array
     */
    private $params = array();

    /**
     * Converts as string  type to smf db type. Returns false on failed conversion.
     * @param string $type
     * @return string|bool
     */
    public function convertType($type)
    {
        foreach ( $this->conversionlist as $to_search => $new_type )
        {
            if (preg_match('/' . $to_search . '/', $type))
                return $new_type;
        }

        return false;
    }

    /**
     * Converts the table datafields into smf compatible datatypes for usage as parameter in queries
     * @param array $fieldlist
     * @return array $fieldlist
     */
    public function convFldTypes($fieldlist)
    {
        foreach ( $fieldlist as $field => $type )
        {
            foreach ( $this->conversionlist as $to_search => $new_type )
            {
                if (preg_match('/' . $to_search . '/', $type))
                    $fieldlist->{$field} = $new_type;
            }
        }

        return $fieldlist;
    }

    /**
     * Loads and returns the structure of a table
     * @param string $tbl
     * @return Data
     */
    public function getTblStructure($tbl)
    {
        global $smcFunc;
        db_extend('packages');
        return Lib::toObject($smcFunc['db_table_structure']('{db_prefix}' . $tbl));
    }

    /**
     * This is the db query method.
     * It's a simple wrapper for the $smcFunc['db_query']
     * @param string $sql
     * @param optional array $params
     * @return mysql_result $res
     */
    public function query($sql, $params = array(), $log_query = true)
    {
        global $smcFunc;

        // Convert data params to array
        if (isset($params) && $params instanceof Data)
            $params = (array) $params;

            // Log this query when log and db log are enabled.
        if ($log_query && Cfg::get('Web', 'log'))
            Log::add($this->quote($sql, $params), null, null, 'log_db');

            // Run query
        return $smcFunc['db_query']('', $sql, $params);
    }

    /**
     * This is the db query method.
     * It'S a simple wrapper for the $smcFunc['db_quote']
     * @param string $sql
     * @param optional array $params
     * @return string $res
     */
    public function quote($sql, $params = array())
    {
        global $smcFunc;
        return $smcFunc['db_quote']($sql, $params);
    }

    /**
     *
     * @param string $method "Insert" or "Replace"
     * @param string $tbl Full name of the table to insert data. Do not forget {db_prefix}!
     * @param array $fields Array of the coloums we have data for
     * @param array $values The value to insert
     * @param array $keys Array of table keys
     * @return integer The id of the last inserted record
     */
    public function insert($method, $tbl, $fields, $values, $keys)
    {
        global $smcFunc;

        $smcFunc['db_insert']($method, $tbl, $fields, $values, $keys);

        return $this->insertID($tbl, $keys[0]);
    }

    /**
     * This is the db last_insert_id method.
     * It's a simple wrapper for the $smcFunc[db_insert_id]
     * @param string $table
     * @param string $field
     * @return integer
     */
    public function insertId($table, $field)
    {
        global $smcFunc;
        return $smcFunc['db_insert_id']($table, $field);
    }

    /**
     * Wrapper method for $smcFunc[db_fetch_assoc]
     * @param $res
     */
    public function fetchAssoc($res)
    {
        global $smcFunc;
        return $smcFunc['db_fetch_assoc']($res);
    }

    /**
     * Wrapper method for $smcFunc[db_fetch_row]
     * @param $res
     */
    public function fetchRow($res)
    {
        global $smcFunc;
        return $smcFunc['db_fetch_row']($res);
    }

    /**
     * Wrapper method for $smcFunc[db_num_rows]
     * @param $res
     */
    public function numRows($res)
    {
        global $smcFunc;
        return $smcFunc['db_num_rows']($res);
    }

    /**
     * Queries the db with the given sql-string an returns the number auf row affected.
     * Ideal for SELECTs without Count(*).
     *
     * @param string $sql sql-string
     * @param array $params filter or value parameter
     * @return int number of rows
     */
    public function numRowsFromSql($sql, $params = array())
    {
        global $smcFunc;
        return $smcFunc['db_num_rows']($this->query($sql, $params));
    }

    /**
     * Queries DB an returns the first requested colums as array
     * Perfect for queries wher you only want to get keys
     *
     * @param string $sql
     * @return array $arr
     */
    public function getKeys($sql, $params = array())
    {
        $res = $this->query($sql, $params);

        $data = array();

        while ( $row = $this->fetchRow($res) )
            $data[] = $row[0];

        $this->freeResult($res);

        return $data;
    }

    /**
     * Counts the colums of ea given result
     *
     * @param mixed $res db result
     * @return int number of columns in result
     */
    public function countCols($res)
    {
        global $smcFunc;
        return $smcFunc['db_num_fields']($res);
    }

    /**
     * Queries the DB with the paramter sql string and returns
     * an array where the primarykey of the query represents one key
     * of the array and each of the other requested colums the
     * elememtkeys and values represent.
     *
     * @param string $sql
     * @return array $arr
     *
     *         IMPORTANT: YOU HAVE TO PUT YOUR PRIMARY KEY IN THE FIRST
     *         POSITION OF YOUR SQL STRING. OTHERWISE THE OUTPUT ARRAY WON'T
     *         BE CORRECT!
     *
     *         Example SQL: (in our table are three rows)
     *         <code>
     *         "SELECT KeyColumn, Column1, Columns2 FROM table"
     *         </code>
     *
     *         will result in
     *         <code>
     *         array[1st valKeyColumn] = array (
     *         [0] = valColumn1
     *         [1] => valColumn2
     *         )
     *         array[2nd valKeyColumn] = array (
     *         [0] = valColumn1
     *         [1] => valColumn2
     *         )
     *         array[3rd valKeyColumn] = array (
     *         [0] = valColumn1
     *         [1] => valColumn2
     *         )
     *         </code>
     */
    public function getAll($sql, $params = array(), $serialized = array())
    {
        $res = $this->query($sql, $params);

        while ( $row = $this->fetchAssoc($res) )
        {
            $record = (object) $row;

            foreach ( $serialized as $col_to_unserialize )
                $record->{$col_to_unserialize} = $this->checkSerialized($record->{$col_to_unserialize});

                // Get the index
            $cols = array_keys($row);

            $this->{$row[$cols[0]]} = $record;
        }

        $this->freeResult($res);

        return $this->getData();
    }

    /**
     * Queries the DB and returns one row in form of an assoc array.
     * Each value is accesible by it's name in the sql string. Only the FIRST row of a result will be processed.
     * All other rows of a result will be skipped.
     *
     * This method is ideal for databaserequests where you only want
     * to retreive on row.
     *
     * @param string $sql
     * @return array $arr
     *
     *         Input SQL String:
     *         <code>
     *         SELECT Column1, Column2, Column2 FROM table WHERE ID=1
     *         </code>
     *         <code>
     *         SELECT Column1, Column2, Column2 FROM table ORDER BY Column1 DESC LIMIT 1
     *         </code> *
     *         Output Array:
     *         <code>
     *         array (
     *         'colName0' => Value of Column1
     *         'colName1' => Value of Column2
     *         'colName2' => Value of Column3
     *         )
     *         </code>
     */
    public function getRow($sql, $params = array(), $serialized = array())
    {
        $res = $this->query($sql, $params);

        $counter = 0;

        while ( $row = $this->fetchAssoc($res) )
        {
            $counter++;

            $row = (object) $row;

            foreach ( $serialized as $col_to_unserialize )
                $row->{$col_to_unserialize} = $this->checkSerialized($row->{$col_to_unserialize});

            if ($counter == 1)
                break;
        }

        $this->freeResult($res);

        return $row;
    }

    /**
     * Queries the DB and returns result row in form of an array with an automated numeric index.
     *
     * This method is ideal for databaserequests where you only want
     * to retreive on row.
     *
     * @param string $sql
     * @return array $arr
     *
     *         Input SQL String:
     *         <code>
     *         SELECT Column1, Column2, Column2 FROM table WHERE ID=1
     *         </code>
     *         Output Array:
     *         <code>
     *         0 => array (
     *         '0' => Value of Column1
     *         '1' => Value of Column2
     *         '2' => Value of Column3
     *         )
     *         </code>
     */
    public function getConfig($sql, $params = array(), $serialized = array())
    {
        $res = $this->query($sql, $params);

        if ($this->numRows($res) > 0)
            $this->data = new \stdClass();

        while ( $row = $this->fetchRow($res) )
            $this->data->{$row[0]}->{$row[1]} = in_array($row[1], $serialized) ? $this->checkSerialized($row[2]) : $row[2];

        $this->freeResult($res);

        return $this->data;
    }

    /**
     * Queries the db and returns the values of the first column as array.
     * The first column represents the elementkey. The second the elementvalue.
     *
     * @param string $sql - ignores all requested columns except the first and second
     * @param array $params - possible filter parameter
     * @return array - data of the requested column
     */
    public function getTwoCols($sql, $params = array(), $serialized = array())
    {
        $res = $this->query($sql, $params);

        $data = array();

        while ( $row = $this->fetchRow($res) )
        {
            $data[$row[0]] = $row[1];
        }

        $this->freeResult($res);

        return $data;
    }

    /**
     * Queries the db and returns only one value.
     * Ideal for SELECT Count(*) or similiar requests.
     * @param string $sql - ignores all requested columns except the first and second
     * @param array $params - possible filter parameter
     * @return mixed $val - requested value
     */
    public function getOneValue($sql, $params = array())
    {
        $res = $this->query($sql, $params);
        $row = $this->fetchRow($res);

        if ($this->numRows($res) != 0 || !empty($row[0]))
            $value = $this->checkSerialized($row[0]);

        $this->freeResult($res);

        return $value;
    }

    /**
     * Kills the given db result.
     * @param mysql_result $res
     */
    public function freeResult($res)
    {
        global $smcFunc;
        $smcFunc['db_free_result']($res);
    }

    /**
     * Returns the list of queries done.
     * @return multitype:
     */
    public function getQueryList()
    {
        return self::$queries;
    }

    /**
     * Returns the number counter of queries done.
     * @return number
     */
    public function getQueryCounter()
    {
        return $this->query_counter;
    }

    private function checkSerialized($val = null)
    {
        if ($val === null)
            return $val;

        return Lib::isSerialized($val) ? unserialize($val) : $val;
    }
}
?>
