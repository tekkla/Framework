<?php
namespace Web\Framework\Lib;

if (!defined('WEB'))
	die('Cannot run without WebExt framework...');

/**
 * Handles all WebExt low level config related stuff
 * @author Michael "Tekkla" Zorn (tekkla@tekkla.de)
 * @copyright 2014
 * @license BSD
 * @package WebExt
 * @subpackage Lib
 */
final class Cfg
{
    /**
     * Config storage
     * @var Data
     */
    private static $config;

    /**
     * Get an cfg setting
     * @param string $app
     * @param string $key
     * @throws Error
     * @return mixed
     */
    public static function get($app, $key = null)
    {
        // Calls only with app name indicates, that the complete app config is requested
        if (!isset($key) && isset(self::$config->{$app}))
            return self::$config->{$app};

        // Calls with app and key are normal cfg requests
        if (isset($key) && isset(self::$config->{$app}) && isset(self::$config->{$app}->{$key}))
            return self::$config->{$app}->{$key};

        // All other will result in an error exception
        Throw new Error('Config not found', 4000, array($app, $key));
    }

    /**
     * Set a cfg setting
     * @param string $app
     * @param string $key
     * @param mixed $val
     */
    public static function set($app, $key, $val)
    {
        if (!isset($app))
            return;

        self::$config->{$app}->{$key} = $val;
    }

    /**
     * Checks the state of a cfg setting.
     * Returns true for set and false for not set.
     * @param string $app
     * @param string $key
     */
    public static function exists($app, $key = null)
    {
        // No app found = false
        if (!isset(self::$config->{$app}))
            return false;

            // app found and no key requested? true
        if (!isset($key))
            return true;

            // key requested and found? true
        if (isset($key) && isset(self::$config->{$app}->{$key}))
            return true;

            // All other: false
        return false;
    }

    /**
     * Loads config from database
     */
    public static function load()
    {
        // Init config storage
        self::$config = new Data();

        /* @var $db Database */
        $db = Database::getInstance();

        $res = $db->query("SELECT * FROM {db_prefix}web_config ORDER BY app, cfg", null, false);

        while ( $row = $db->fetchRow($res) )
        {
            $val = $row[3];

            // Check for serialized data and unserialize it
            if (Lib::isSerialized($val))
                $val = unserialize($val);

            self::$config->{$row[1]}->{$row[2]} = $val;
        }

        self::$loaded = true;
    }

    /**
     * Saves a config value to db
     * @param string $app Related app name
     * @param string $key Config key name
     * @param mixed $val Config value
     */
    public static function save($app, $key, $val)
    {
        /* @var $db Database */
        $db = Database::getInstance();

        $sql = "DELETE FROM {db_prefix}web_config WHERE app={string:app} AND key={string:key} AND val={string:val}";
        $params = array(
            'app' => $app,
            'key' => $key
        );

        $db->query($sql, $params);

        if (is_object($val) || is_array($val))
            $val = serialize($val);

        $db->insert('Insert', '{db_prefix}web_config', array(
            'app' => 'string',
            'key' => 'string',
            'val' => 'string'
        ), array(
            $app,
            $key,
            $val
        ));
    }

    /**
     * Returns the complete config storage
     * @return \Web\Framework\Lib\Data
     */
    public static function getConfig()
    {
        return self::$config;
    }
}
?>
