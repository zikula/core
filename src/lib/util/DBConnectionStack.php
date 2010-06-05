<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This class maintains a stack of database connections. Getting a connection
 * will always return the connection object which is currently on top of the
 * connections stack (ie: the latest added connection).
 */
class DBConnectionStack
{
    /**
     * Reference to Doctrine_Manager instance.
     * The DBConnectionStack acts only as a forwarder, as it is more limited in its use cases.
     *
     * @var Doctrine_Manager
     */
    private static $manager;

    protected static $cacheDriver;

    /**
     * Contains additional connection configuration arrays,
     * taken from config.php
     *
     * @var array
     */
    private static $connectionInfo = null;

    private function __construct()
    {
    }

    /**
     * Initialize a DBConnection and place it on the connection stack
     *
     * @param name The database alias name in the DBInfo configuration array (optional) (default=null which then defaults to 'default')
     * @return Doctrine_Connection desired database connection reference
     */
    static function init($name = 'default', $lazyConnect = false)
    {
        if (!isset(self::$manager)) {
            self::$manager = Doctrine_Manager::getInstance();
            self::configureDoctrine(self::$manager);
            // setup caching
            if (!System::isInstalling() && System::getVar('OBJECT_CACHE_ENABLE')) {
                $type = System::getVar('OBJECT_CACHE_TYPE');

                // Setup Doctrine Caching
                $type = ucfirst(strtolower($type));
                $doctrineCacheClass = "Doctrine_Cache_$type";
                self::$cacheDriver = new $doctrineCacheClass();
                self::$manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, self::$cacheDriver);
                self::$manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, self::$cacheDriver);
                // TODO B implment resultcache lifespan configuration variable
                //$manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, 3600);
            }
        }

        if (isset(self::$connectionInfo[$name])) {
            return self::$connectionInfo[$name];
        }
        $connInfo = $GLOBALS['ZConfig']['DBInfo'][$name];

        // collect information for DBConnectionStack
        $dsnParts = self::$manager->parseDsn($connInfo['dsn']);
        $connInfo['dbtype'] = strtolower($dsnParts['scheme']);
        $connInfo['dbhost'] = $dsnParts['host'];
        $connInfo['dbname'] = $dsnParts['database'];
        $connInfo['prefix'] = System::getVar('prefix') . '_';

        // test the DB connection works or just set lazy
        try {
            if ($lazyConnect) {
                $connection = Doctrine_Manager::connection($connInfo['dsn'], $name);
            } else {
                $dbh = new PDO("$connInfo[dbtype]:host=$connInfo[dbhost];dbname=$connInfo[dbname]", $dsnParts['user'], $dsnParts['pass']);
                $connection = Doctrine_Manager::connection($dbh, $name);
                $connection->setOption('username', $dsnParts['user']);
                $connection->setOption('password', $dsnParts['pass']);
            }
            self::configureDoctrine($connection);
        } catch (PDOException $e) {
            throw new PDOException(__('Connection failed to database') . ': ' . $e->getMessage());
        }

        // set driver
        $connInfo['dbdriver'] = strtolower($connection->getDriverName());

        // set mysql/mysqli engine type
        // TODO A add cases for other drivers? - drak
        if ($connInfo['dbdriver'] == 'mysql') {
            $connection->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE, $connInfo['dbtabletype']);
        }

        self::configureDoctrine($connection);

        Doctrine::debug(System::getVar('development'));

        if (isset($connInfo['dbcharset']) && !System::isInstalling()) {
            $connection->setCharset($connInfo['dbcharset']);
        }
        if (isset($connInfo['dbcollate']) && !System::isInstalling()) {
            $connection->setCollate($connInfo['dbcollate']);
        }

        if ($dsnParts['scheme'] != 'oracle') {
            $connection->setAttribute(Doctrine_Core::ATTR_PORTABILITY, Doctrine_Core::PORTABILITY_ALL ^ Doctrine_Core::PORTABILITY_EMPTY_TO_NULL);
        }

        if ($GLOBALS['ZConfig']['Debug']['sql_verbose']) {
            $profiler = new Doctrine_Connection_Profiler();
            $connection->setListener($profiler);
        }

        self::$connectionInfo[$name] = $connInfo;

        return $connection;
    }

    /**
     * Get the DB connection info structure for a connection as defined in config.php.
     * If $field is supplied, the value of the specified field is retuerned, otherwise
     * the entire connection info array is returned.
     *
     * @param  name   the name of the connection info to get. Passing null returns the current (ie: top) connection (optional) (default=null)
     * @param  field  the field of the connection info record to return
     * @return string The connection info array or the specified field value
     */
    static function getConnectionInfo($name = null, $field = null)
    {
        if (!self::$manager instanceof Doctrine_Manager) {
            self::init($name);
        }

        if (!self::$manager->count()) {
            if (System::isInstalling()) {
                return;
            }
            throw new Exception(__('Attempted to get info from empty connection stack'));
        }

        // look if $name points to a valid connection
        if (!is_null($name) && !self::$manager->contains($name)) {
            throw new Exception(__f('Invalid connection key [%s]', $name));
        }

        if (is_null($name)) {
            // take the current connection which is the last element on the stack
            $name = self::$manager->getCurrentConnection()->getName();
        }

        if (!isset(self::$connectionInfo[$name])) {
            self::init($name);
        }

        if (!isset(self::$connectionInfo[$name])) {
            throw new Exception(__f('Invalid connection key [%s]', $name));
        }

        $connectionInfo = self::$connectionInfo[$name];

        if ($field) {
            if ($field == 'alias') {
                return $name;
            }

            // only return a specific field
            if (!isset($connectionInfo[$field])) {
                throw new Exception(__f('Unknown field [%s] requested', $field));
            }
            return $connectionInfo[$field];
        }

        // return the complete information array
        return $connectionInfo;
    }

    /**
     * Get the alias name name of the currently active connection
     *
     * @return string the name of the currently active connection
     */
    static function getConnectionName()
    {
        return self::getConnectionInfo(null, 'alias');
    }

    /**
     * Get the DB Alias name of the currently active connection
     *
     * @return string the dbname of the currently active connection
     */
    static function getConnectionDBName()
    {
        return self::getConnectionInfo(null, 'dbname');
    }

    /**
     * Get the DB Host of the currently active connection
     *
     * @return string the host of the currently active connection
     */
    static function getConnectionDBHost()
    {
        return self::getConnectionInfo(null, 'dbhost');
    }

    /**
     * Get the DB Type of the currently active connection
     *
     * @return string the type of the currently active connection
     */
    static function getConnectionDBType()
    {
        return self::getConnectionInfo(null, 'dbtype');
    }

    /**
     * Get the DB driver of the currently active connection.
     * This is not necessarily the same as the DB Type and
     * should be used to distinguish between different database types.
     *
     * @return string the driver of the currently active connection
     */
    static function getConnectionDBDriver()
    {
        return self::getConnectionInfo(null, 'dbdriver');
    }

    /**
     * Get the default DB charset of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    static function getConnectionDBCharset()
    {
        return self::getConnectionInfo(null, 'dbcharset');
    }

    /**
     * Get the default DB collation of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    static function getConnectionDBCollate()
    {
        return self::getConnectionInfo(null, 'dbcollate');
    }

    /**
     * Get the default DB table type of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    static function getConnectionDBTableType()
    {
        return self::getConnectionInfo(null, 'dbtabletype');
    }

    /**
     * Get the DSN string of the currently active connection
     *
     * @return string the DSN of the currently active connection
     */
    static function getConnectionDSN()
    {
        return self::getConnectionInfo(null, 'dsn');
    }

    /**
     * Check whether the current connection is the default one
     *
     * @return boolean whether or not the current connection is the default one
     */
    static function isDefaultConnection()
    {
        return (self::getConnectionName() == 'default');
    }

    /**
     * Get the currently active connection (the connection on top of the connection stack)
     *
     * @param fetchmode        The fetchmode to set for the connection
     * @return the connection object
     */
    static function getConnection($fetchmode = Doctrine::HYDRATE_NONE)
    {
        if (!isset(self::$manager)) {
            self::init();
        }

        if (!self::$manager->count()) {
            if (System::isInstalling()) {
                return;
            }
            throw new Exception(__('Attempted to get connection from empty connection stack'));
        }
        $connection = self::$manager->getCurrentConnection();
        //$connection->setHydrationMode(Doctrine::HYDRATE_ARRAY);
        return $connection;
    }

    /**
     * Push a new database connection onto the connection stack
     *
     * @param name        The database alias name in the DBInfo configuration array
     * @return The database connection
     */
    static function pushConnection($name)
    {
        if (self::init($name)) {
            return self::getConnection();
        }

        return false;
    }

    /**
     * Pop the currently active connection off the stack.
     *
     * @param close       Whether or not to close the connection (optional) (default=false)
     * @return boolean The newly active connection
     */
    static function popConnection($close = false)
    {
        if (!self::$manager->count()) {
            throw new Exception(__('Attempted to pop connection from empty connection stack'));
        }

        $connection = self::$manager->getConnection();
        if ($close) {
            $name = $connection->getName();
            $connInfo = self::$connectionInfo[$name];

            // close
            $connection->close();

            // reopen connection
            self::$manager->openConnection($connInfo['dsn'], $name, true);
        }

        return self::$manager->getConnection();
    }

    /**
     * Sets configuration attributes for Doctrine
     *
     * @param mixed object The object which is being configured. This can be:
     *      - on global level (Doctrine_Manager instance)
     *      - on connection level (Doctrine_Connection instance)
     *      - on table level (Doctrine_Table instance)
     *
     * Doctrine can set every attribute on every level.
     */
    function configureDoctrine($object)
    {
        if ($object instanceof Doctrine_Manager) {
            // set global options


            // Cross-DBMS portability options
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_1/en/configuration#portability:portability-mode-attributes
            // Turn on all portability features (commented out as this is the default setting)
            $object->setAttribute('portability', Doctrine::PORTABILITY_ALL);

            // Turn of identifier quoting, as it causes more problems than it solves
            // See http://www.doctrine-project.org/documentation/manual/1_1/en/configuration#identifier-quoting
            $object->setAttribute(Doctrine::ATTR_QUOTE_IDENTIFIER, false);

            // What should be exported when exporting classes to the db
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_1/en/configuration#exporting
            $object->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL);

            // Validation attributes (default is VALIDATE_NONE)
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_1/en/configuration#naming-convention-attributes:validation-attributes
            // Turn on all validation functionality, at least while we are in development mode
            $object->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);

            // naming convention of database related elements
            // affect importing schemas from the database to classes
            // as well as exporting classes into database tables.


            // Index names (default: [name]_idx)
            $object->setAttribute(Doctrine::ATTR_IDXNAME_FORMAT, '%s');


            // Sequence names (default: [name]_seq)
            // $object->setAttribute(Doctrine::ATTR_SEQNAME_FORMAT, '%s_sequence');


            // Database names
            // $object->setAttribute(Doctrine::ATTR_DBNAME_FORMAT, 'myframework_%s');
            // TODO B [use ATTR_DBNAME_FORMAT for MultiSites possibilities] (Guite)


            // Table name prefixes
            $tablePrefix = System::getVar('prefix');
            $object->setAttribute(Doctrine::ATTR_TBLNAME_FORMAT, "{$tablePrefix}_%s");

            // Allow overriding of accessors
            $object->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

            // Enable auto loading of custom Doctrine_Table classes in addition to Doctrine_Record
            $object->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

            // Set model loading strategy to conservative
            // see http://www.doctrine-project.org/documentation/manual/1_1/en/introduction-to-models#autoloading-models
            $object->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
            //$object->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);


            return;
        }
        if ($object instanceof Doctrine_Connection) {
            // set connection options


            // fetch / hydration mode
            //            $object->setAttribute(Doctrine::ATTR_FETCHMODE, Doctrine::FETCH_ASSOC);
            //            $object->setAttribute(Doctrine::ATTR_HYDRATE_OVERWRITE, Doctrine::HYDRATE_RECORD);


            // default column options
            /*            $object->setAttribute(Doctrine::ATTR_DEFAULT_COLUMN_OPTIONS,
                                                        array('type' => 'string',
                                                              'length' => 255,
                                                              'notnull' => true));
*/
            // properties of default added primary key in models
            // %s is replaced with the table name
            /*            $object->setAttribute(Doctrine::ATTR_DEFAULT_IDENTIFIER_OPTIONS,
                                                        array('name' => '%s_id',
                                                              'type' => 'string',
                                                              'length' => 16));
*/
            return;
        } elseif ($object instanceof Doctrine_Table) {
            // set table options
            return;
        }

        throw new Exception(get_class($object) . ' is not valid in configureDoctrine()');
    }

    /**
     * Returns the current cache driver
     *
     * @return cache object
     */
    public static function getCacheDriver()
    {
        return self::$cacheDriver;
    }

}
