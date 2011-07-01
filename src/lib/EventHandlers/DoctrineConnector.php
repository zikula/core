<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine listeners.
 */
class DoctrineListener extends Zikula_AbstractEventHandler
{
    /**
     * The Doctrine Manager instance.
     *
     * @var Doctrine_Manager
     */
    protected $doctrineManager;

    /**
     * Setup handlers.
     *
     * @return void
     */
    public function setupHandlerDefinitions()
    {
        $this->addHandlerDefinition('doctrine.init_connection', 'doctrineInit');
        $this->addHandlerDefinition('doctrine.configure', 'configureDoctrine');
        $this->addHandlerDefinition('doctrine.cache', 'configureCache');
    }

    /**
     * Inialise a Doctrine 1 connection.
     *
     * Listens for 'doctrine.init_connection' events.
     *
     * Event arguments are:
     * boolean 'lazy'  - lazy connect.
     * string 'name' - connection name.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function doctrineInit(Zikula_Event $event)
    {
        if (!$this->doctrineManager) {
            Doctrine_Core::debug(System::isDevelopmentMode());
            $this->doctrineManager = Doctrine_Manager::getInstance();
            $internalEvent = new Zikula_Event('doctrine.configure', $this->doctrineManager);
            $this->eventManager->notify($internalEvent);

            $internalEvent = new Zikula_Event('doctrine.cache', $this->doctrineManager);
            $this->eventManager->notify($internalEvent);
        }

        $lazyConnect = isset($event['lazy']) ? $event['lazy'] : false;
        $name = isset($event['name']) ? $event['name'] : 'default';

        $connectionInfo = $this->serviceManager['databases'][$name];

        // test the DB connection works or just set lazy
        try {
            if ($lazyConnect) {
                $dsn = "$connectionInfo[dbdriver]://$connectionInfo[user]:$connectionInfo[password]@$connectionInfo[host]/$connectionInfo[dbname]";
                $connection = Doctrine_Manager::connection($dsn, $name);
            } else {
                $dbh = new PDO("$connectionInfo[dbdriver]:host=$connectionInfo[host];dbname=$connectionInfo[dbname]", $connectionInfo['user'], $connectionInfo['password']);
                $connection = Doctrine_Manager::connection($dbh, $name);
                $connection->setOption('username', $connectionInfo['user']);
                $connection->setOption('password', $connectionInfo['password']);
            }
            $internalEvent = new Zikula_Event('doctrine.configure', $connection);
            $this->eventManager->notify($internalEvent);
        } catch (PDOException $e) {
            throw new PDOException(__('Connection failed to database') . ': ' . $e->getMessage());
        }

        // set mysql engine type
        if ($connectionInfo['dbdriver'] == 'mysql') {
            $connection->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE, $connectionInfo['dbtabletype']);
        }

        try {
            if (isset($connectionInfo['charset'])) {
                $connection->setCharset($connectionInfo['charset']);
            }
            if (isset($connectionInfo['collate'])) {
                $connection->setCollate($connectionInfo['collate']);
            }
        } catch (Exception $e) {
            //if (!System::isInstalling()) {
            //    throw new Exception(__('Error setting database characterset and collation.'));
            //}
        }

        if ($connectionInfo['dbdriver'] != 'oracle') {
            $connection->setAttribute(Doctrine_Core::ATTR_PORTABILITY, Doctrine_Core::PORTABILITY_ALL ^ Doctrine_Core::PORTABILITY_EMPTY_TO_NULL);
        }

        if (isset($this->serviceManager['log.enabled']) && $this->serviceManager['log.enabled']) {
            // add listener that sends events for all sql queries
            $connection->setListener(new Zikula_Doctrine_Listener_Profiler());
        }

        $event->data = $connection;
    }

    /**
     * Configure caching.
     *
     * Listens for 'doctrine.configure' events.
     * Subject is expected to be the Doctrine_Manager.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function configureCache(Zikula_Event $event)
    {
        $manager = $event->getSubject();
        if (!System::isInstalling() && $this->serviceManager['dbcache.enable']) {
            $type = $this->serviceManager['dbcache.type'];

            // Setup Doctrine Caching
            $type = ucfirst(strtolower($type));
            $doctrineCacheClass = "Doctrine_Cache_$type";
            $r = new ReflectionClass($doctrineCacheClass);
            $options = array('prefix' => 'dd');
            if (strpos($type, 'Memcache') === 0) {
                $servers = $this->serviceManager['dbcache.servers'];
                $options = array_merge($options, array('servers' => $servers, 'compression' => $this->serviceManager['dbcache.compression']));
            }

            $cacheDriver = $this->serviceManager->attachService('doctrine.cachedriver', $r->newInstance($options));
            $manager->setAttribute(Doctrine_Core::ATTR_QUERY_CACHE, $cacheDriver);
            $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE, $cacheDriver);

            // implment resultcache lifespan configuration variable
            $manager->setAttribute(Doctrine_Core::ATTR_RESULT_CACHE_LIFESPAN, $this->serviceManager['dbcache.cache_result_ttl']);

            // Support for multisites to prevent clashes
            $name = 'default'; // todo - drak
            $cacheDriver->setOption('prefix', md5(serialize($this->serviceManager['databases'][$name])));
        }
    }

    /**
     * Configure Doctrine 1.x instance.
     *
     * Listens for 'doctrine.configure' events.
     * Subject is either Doctrine_Manager, Doctrine_Connection or Doctrine_Table.
     *
     * @param Zikula_Event $event Event.
     *
     * @return void
     */
    public function configureDoctrine(Zikula_Event $event)
    {
        $object = $event->getSubject();
        if ($object instanceof Doctrine_Manager) {
            // Cross-DBMS portability options
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_2/en/configuration#portability:portability-mode-attributes
            // Turn on all portability features (commented out as this is the default setting)
            $object->setAttribute('portability', Doctrine_Core::PORTABILITY_ALL);

            // Turn off identifier quoting, as it causes more problems than it solves
            // See http://www.doctrine-project.org/documentation/manual/1_2/en/configuration#identifier-quoting
            $object->setAttribute(Doctrine_Core::ATTR_QUOTE_IDENTIFIER, false);

            // What should be exported when exporting classes to the db
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_2/en/configuration#exporting
            $object->setAttribute(Doctrine_Core::ATTR_EXPORT, Doctrine_Core::EXPORT_ALL);

            // Validation attributes (default is VALIDATE_NONE)
            // Modes are bitwised, so they can be combined using | and removed using ^.
            // See http://www.doctrine-project.org/documentation/manual/1_2/en/configuration#naming-convention-attributes:validation-attributes
            // Turn on all validation functionality, at least while we are in development mode
            $object->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_ALL);

            // naming convention of database related elements
            // affect importing schemas from the database to classes
            // as well as exporting classes into database tables.


            // Index names (default: [name]_idx)
            $object->setAttribute(Doctrine_Core::ATTR_IDXNAME_FORMAT, '%s');

            // Sequence names (default: [name]_seq)
            // $object->setAttribute(Doctrine_Core::ATTR_SEQNAME_FORMAT, '%s_sequence');

            // Database names
            // $object->setAttribute(Doctrine_Core::ATTR_DBNAME_FORMAT, 'myframework_%s');

            // Allow overriding of accessors
            $object->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);

            // Enable auto loading of custom Doctrine_Table classes in addition to Doctrine_Record
            $object->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);

            // Set model loading strategy to conservative
            // see http://www.doctrine-project.org/documentation/manual/1_2/en/introduction-to-models#autoloading-models
            $object->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);
            //$object->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);

            // enable dql hooks (used by Categorisable doctrine template)
            $object->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);

            $object->registerHydrator(DoctrineUtil::HYDRATE_SINGLE_SCALAR_ARRAY, 'Zikula_Doctrine_Hydrator_SingleScalarArray');

            // tell doctrine our extended Doctrine_Query class (Doctrine_Query::create() returns a Zikula_Doctrine_Query instance)
            $object->setAttribute(Doctrine_Core::ATTR_QUERY_CLASS, 'Zikula_Doctrine_Query');

            return;
        }
        if ($object instanceof Doctrine_Connection) {
            // set connection options


            // fetch / hydration mode
            //            $object->setAttribute(Doctrine_Core::ATTR_FETCHMODE, Doctrine_Core::FETCH_ASSOC);
            //            $object->setAttribute(Doctrine_Core::ATTR_HYDRATE_OVERWRITE, Doctrine_Core::HYDRATE_RECORD);


            // default column options
            //            $object->setAttribute(Doctrine_Core::ATTR_DEFAULT_COLUMN_OPTIONS,
            //                                            array('type' => 'string',
            //                                                  'length' => 255,
            //                                                  'notnull' => true));

            // properties of default added primary key in models
            // %s is replaced with the table name
            //            $object->setAttribute(Doctrine_Core::ATTR_DEFAULT_IDENTIFIER_OPTIONS,
            //                                            array('name' => '%s_id',
            //                                                  'type' => 'string',
            //                                                  'length' => 16));

            return;
        } elseif ($object instanceof Doctrine_Table) {
            // set table options
            return;
        }

        throw new Exception(get_class($object) . ' is not valid in configureDoctrine()');
    }

}
