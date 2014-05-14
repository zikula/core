<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Legacy
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

include 'lib/legacy/Api.php';

// backwards compatibility references

global $PNRuntime;
$PNRuntime = array();
$GLOBALS['PNConfig'] = &$GLOBALS['ZConfig'];
$GLOBALS['pntables'] = &$GLOBALS['dbtables'];

define('_MARKER_NONE', '&nbsp;&nbsp;');
define('_REQUIRED_MARKER',  '<span style="font-size:larger;color:blue"><strong>*</strong></span>');
define('_VALIDATION_MARKER', '<span style="font-size:larger;color:red"><strong>!</strong></span>');

/**
 * Alias to the Zikula_View class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see Zikula_View
 */
class pnRender extends Zikula_View
{
    /**
     * Constructs a new instance of pnRender.
     *
     * @param string $module  Name of the module.
     * @param bool   $caching If true, then caching is enabled.
     */
    public function __construct($module = '', $caching = null)
    {
        parent::__construct(ServiceUtil::getManager(), $module, $caching);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Zikula_View')), E_USER_DEPRECATED);
    }
}

/**
 * Theme.
 *
 * @deprecated
 * @see Zikula_View_Theme
 */
class Theme extends Zikula_View_Theme
{
    public function __construct($theme)
    {
        parent::__construct(ServiceUtil::getManager(), $theme);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Zikula_View_Theme')), E_USER_DEPRECATED);
    }
}

/**
 * ZWorkflow.
 *
 * @deprecated
 * @see Zikula_Workflow
 */
class ZWorkflow extends Zikula_Workflow
{
    public function __construct($schema, $module)
    {
        parent::__construct($schema, $module);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Zikula_Workflow')), E_USER_DEPRECATED);
    }
}

/**
 * ZWorkflowParser.
 *
 * @deprecated
 * @see Zikula_Workflow_Parser
 */
class ZWorkflowParser extends Zikula_Workflow_Parser
{

}

/**
 * WorkflowUtil.
 *
 * @deprecated
 * @see Zikula_Workflow_Util
 */
class WorkflowUtil extends Zikula_Workflow_Util
{

}

/**
 * This class maintains a stack of database connections.
 *
 * Getting a connection will always return the connection object which is
 * currently on top of the connections stack (ie: the latest added connection).
 */
class DBConnectionStack
{
    /**
     * Reference to Doctrine_Manager instance.
     *
     * The DBConnectionStack acts only as a forwarder, as it is more limited in its use cases.
     *
     * @var Doctrine_Manager
     */
    private static $manager;

    /**
     * Cache driver.
     *
     * @var ReflectionClass
     */
    protected static $cacheDriver;

    /**
     * Contains additional connection configuration arrays.
     *
     * Taken from config.php.
     *
     * @var array
     */
    private static $connectionInfo = null;

    /**
     * Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Initialize a DBConnection and place it on the connection stack.
     *
     * @param string  $name        The database alias name in the DBInfo configuration array (optional) (default=null which then defaults to 'default').
     * @param boolean $lazyConnect Whether or not to connect lazy.
     *
     * @deprecated
     *
     * @throws PDOException        If database connection failed.
     * @return Doctrine_Connection Desired database connection reference.
     */
    public static function init($name = 'default', $lazyConnect = false)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);
        $serviceManager = ServiceUtil::getManager();
        $eventManager = EventUtil::getManager();

        // Lazy load DB connection to avoid testing DSNs that are not yet valid (e.g. no DB created yet)
        $dbEvent = new Zikula_Event('doctrine.init_connection', null, array('lazy' => $lazyConnect, 'name' => $name));
        $connection = $eventManager->notify($dbEvent)->getData();
        if (!self::$manager instanceof Doctrine_Manager) {
            self::$manager = Doctrine_Manager::getInstance();
        }
        $databases = $serviceManager['databases'];
        self::$connectionInfo[$name] = $databases[$name];

        return $connection;
    }

    /**
     * Get the DB connection info structure for a connection as defined in config.php.
     *
     * If $field is supplied, the value of the specified field is retuerned, otherwise
     * the entire connection info array is returned.
     *
     * @param string $name  The name of the connection info to get. Passing null returns the current (ie: top) connection (optional) (default=null).
     * @param string $field The field of the connection info record to return.
     *
     * @throws Exception  If no connection is available.
     * @throws Exception  If the given connection does not exist.
     * @throws Exception  If the given field does not exist.
     * @return void|mixed The connection info array or the specified field value.
     */
    public static function getConnectionInfo($name = null, $field = null)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);
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
    public static function getConnectionName()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'alias');
    }

    /**
     * Get the DB Alias name of the currently active connection
     *
     * @return string the dbname of the currently active connection
     */
    public static function getConnectionDBName()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dbname');
    }

    /**
     * Get the DB Host of the currently active connection
     *
     * @return string the host of the currently active connection
     */
    public static function getConnectionDBHost()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dbhost');
    }

    /**
     * Get the DB Type of the currently active connection
     *
     * @return string the type of the currently active connection
     */
    public static function getConnectionDBType()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return strtolower(self::getConnectionInfo(null, 'dbdriver')); // this is a duplicate of DBDriver
    }

    /**
     * Get the DB driver of the currently active connection.
     *
     * This is not necessarily the same as the DB Type and
     * should be used to distinguish between different database types.
     *
     * @return string the driver of the currently active connection
     */
    public static function getConnectionDBDriver()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return strtolower(self::getConnectionInfo(null, 'dbdriver'));
    }

    /**
     * Get the default DB charset of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    public static function getConnectionDBCharset()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dbcharset');
    }

    /**
     * Get the default DB collation of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    public static function getConnectionDBCollate()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dbcollate');
    }

    /**
     * Get the default DB table type of the currently active connection.
     *
     * @return string the driver of the currently active connection
     */
    public static function getConnectionDBTableType()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dbtabletype');
    }

    /**
     * Get the DSN string of the currently active connection
     *
     * @return string the DSN of the currently active connection
     */
    public static function getConnectionDSN()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return self::getConnectionInfo(null, 'dsn');
    }

    /**
     * Check whether the current connection is the default one
     *
     * @return boolean whether or not the current connection is the default one
     */
    public static function isDefaultConnection()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);

        return (self::getConnectionName() == 'default');
    }

    /**
     * Get the currently active connection (the connection on top of the connection stack).
     *
     * @throws Exception If no connection is available.
     *
     * @return void|Doctrine_Connection The connection object.
     */
    public static function getConnection()
    {
        LogUtil::log(__f('Warning! %1$s is deprecated. Use %2$s instead.', array(__CLASS__ . '::' . __FUNCTION__, 'Doctrine_Manager::getInstance()->getCurrentConnection()')), E_USER_DEPRECATED);
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

        return $connection;
    }

    /**
     * Push a new database connection onto the connection stack
     *
     * @param string $name The database alias name in the DBInfo configuration array.
     *
     * @return Doctrine_Connection The database connection.
     */
    public static function pushConnection($name)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);
        if (self::init($name)) {
            return self::getConnection();
        }

        return false;
    }

    /**
     * Pop the currently active connection off the stack.
     *
     * @param boolean $close Whether or not to close the connection (optional) (default=false).
     *
     * @throws Exception           If no connection is available.
     * @return Doctrine_Connection The newly active connection.
     */
    public static function popConnection($close = false)
    {
        LogUtil::log(__f('Warning! %1$s is deprecated.', array(__CLASS__ . '::' . __FUNCTION__)), E_USER_DEPRECATED);
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
}

/**
 * Alias to the DBObject class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see DBObject::
 */
class PNObject extends DBObject
{
    public $_GET_FROM_DB = 'D'; // get data from DB
    public $_GET_FROM_GET = 'G'; // get data from $_GET
    public $_GET_FROM_POST = 'P'; // get data from $_POST
    public $_GET_FROM_REQUEST = 'R'; // get data from $_REQUEST
    public $_GET_FROM_SESSION = 'S'; // get data from $_SESSION
    public $_GET_FROM_VALIDATION_FAILED = 'V'; // get data from failed validation

    /**
     * Constructor, init everything to sane defaults and handle parameters.
     *
     * @param object|string $init  Initialization value (see {@link DBObject::_init()} for details).
     * @param mixed         $key   The DB key to use to retrieve the object (optional) (default=null)
     * @param string        $field The field containing the key value (optional) (default=null)
     */
    public function PNObject($init = null, $key = null, $field = null)
    {
        $this->_init($init, $key, $field);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'DBObject')), E_USER_DEPRECATED);
    }

    public function _init($init = null, $key = null, $field = null)
    {
        static $initTypes = array('D'   => self::GET_FROM_DB,
                                  'G'   => self::GET_FROM_GET,
                                  'P'   => self::GET_FROM_POST,
                                  'R'   => self::GET_FROM_REQUEST,
                                  'S'   => self::GET_FROM_SESSION,
                                  'V'   => self::GET_FROM_VALIDATION_FAILED);
        if (is_string($init) && array_key_exists($init, $initTypes)) {
            $init = $initTypes[$init];
        }

        return parent::_init($init, $key, $field);
    }
}

/**
 * Alias to the DBObjectArray class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see DBObjectArray::
 */
class PNObjectArray extends DBObjectArray
{
    public $_GET_FROM_DB = 'D'; // get data from DB
    public $_GET_FROM_GET = 'G'; // get data from $_GET
    public $_GET_FROM_POST = 'P'; // get data from $_POST
    public $_GET_FROM_REQUEST = 'R'; // get data from $_REQUEST
    public $_GET_FROM_SESSION = 'S'; // get data from $_SESSION
    public $_GET_FROM_VALIDATION_FAILED = 'V'; // get data from failed validation

    /**
     * Constructor, init everything to sane defaults and handle parameters.
     *
     * @param object|string $init  Initialization value (see _init() for details)
     * @param string        $where The where clause to apply to the DB get/select (optional) (default='')
     */
    public function PNObjectArray($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        $this->_init($init, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'DBObjectArray')), E_USER_DEPRECATED);
    }


    public function _init($init = null, $where = null, $orderBy = null, $limitOffset = -1, $limitNumRows = -1, $assocKey = null)
    {
        static $initTypes = array('D'   => self::GET_FROM_DB,
                                  'G'   => self::GET_FROM_GET,
                                  'P'   => self::GET_FROM_POST,
                                  'R'   => self::GET_FROM_REQUEST,
                                  'S'   => self::GET_FROM_SESSION,
                                  'V'   => self::GET_FROM_VALIDATION_FAILED);
        if (is_string($init) && array_key_exists($init, $initTypes)) {
            $init = $initTypes[$init];
        }

        return parent::_init($init, $where, $orderBy, $limitOffset, $limitNumRows, $assocKey);
    }
}

class PNCategory extends Categories_DBObject_Category
{
}

class PNCategoryArray extends Categories_DBObject_CategoryArray
{
}

class PNCategoryRegistry extends Categories_DBObject_Registry
{
}

class PNCategoryRegistryArray extends Categories_DBObject_Registry
{
}


/**
 * Alias to the Zikula_Form_View class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see Zikula_Form_View::
 */
class pnFormRender extends Zikula_Form_View
{
    /**
     * Alias to Zikula_Form_View::State for backward compatibility to Zikula 1.2.x.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::State
     */
    public $pnFormState;

    /**
     * List of included files required to recreate plugins (Smarty function.xxx.php files).
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::Includes
     */
    public $pnFormIncludes;

    /**
     * List of instantiated plugins.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::Plugins
     */
    public $pnFormPlugins;

    /**
     * Stack with all instantiated blocks (push when starting block, pop when ending block).
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::BlockStack
     */
    public $pnFormBlockStack;

    /**
     * List of validators on page.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::Validators
     */
    public $pnFormValidators;

    /**
     * Flag indicating if validation has been done or not.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::ValidationChecked
     */
    public $pnFormValidationChecked;

    /**
     * Indicates whether page is valid or not.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::_IsValid
     */
    public $_pnFormIsValid;

    /**
     * Current ID count - used to assign automatic ID's to all items.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::IdCount
     */
    public $pnFormIdCount;

    /**
     * Reference to the main user code event handler.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::EventHandler
     */
    public $pnFormEventHandler;

    /**
     * Error message has been set.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::ErrorMsgSet
     */
    public $pnFormErrorMsgSet;

    /**
     * Set to true if pnFormRedirect was called. Means no HTML output should be returned.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::Redirected
     */
    public $pnFormRedirected;

    /**
     * Constructs a new instance of pnFormRender.
     *
     * @deprecated
     * @see Zikula_Form_View::__construct()
     */
    public function __construct($module)
    {
        $serviceManager = ServiceUtil::getManager();
        parent::__construct($serviceManager, $module);

        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Zikula_Form_View')), E_USER_DEPRECATED);

        $this->pnFormState = &$this->state;
        $this->pnFormIncludes = &$this->includes;
        $this->pnFormPlugins = &$this->plugins;
        $this->pnFormBlockStack = &$this->blockStack;
        $this->pnFormValidators = &$this->validators;
        $this->pnFormValidationChecked = &$this->validationChecked;
        $this->_pnFormIsValid = &$this->_isValid;
        $this->pnFormIdCount = &$this->idCount;
        $this->pnFormEventHandler = &$this->eventHandler;
        $this->pnFormErrorMsgSet = &$this->errorMsgSet;
        $this->pnFormRedirected = &$this->redirected;
    }

    /**
     * Alias to Zikula_Form_View::execute for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::execute
     *
     * @param boolean       $template     Name of template file.
     * @param pnFormHandler $eventHandler Instance of object that inherits from pnFormHandler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function pnFormExecute($template, &$eventHandler)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::execute')), E_USER_DEPRECATED);

        return $this->execute($template, $eventHandler);
    }

    /**
     * Alias to Zikula_Form_View::execute for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::execute
     *
     * @param boolean       $template     Name of template file.
     * @param pnFormHandler $eventHandler Instance of object that inherits from pnFormHandler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function execute($template, Zikula_Form_AbstractHandler $eventHandler)
    {
        if (!$eventHandler instanceof pnFormHandler) {
            throw new Zikula_Exception_Fatal('Form handlers must inherit from pnFormHandler.');
        }

        return parent::execute($template, $eventHandler);
    }

    /**
     * Alias to Zikula_Form_View::registerPlugin for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::registerPlugin
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array   &$params    Parameters passed from the Smarty plugin function.
     * @param boolean $isBlock Indicates whether the plugin is a Smarty block or a Smarty function (internal).
     *
     * @return string Returns what the render() method of the plugin returns.
     */
    public function pnFormRegisterPlugin($pluginName, &$params, $isBlock = false)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::registerPlugin')), E_USER_DEPRECATED);

        return $this->registerPlugin($pluginName, $params, $isBlock = false);
    }

    /**
     * Alias to Zikula_Form_View::registerBlock for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::registerBlock
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     */
    public function pnFormRegisterBlock($pluginName, &$params, &$content)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::registerBlock')), E_USER_DEPRECATED);

        return $this->registerBlock($pluginName, $params, $content);
    }

    /**
     * Alias to Zikula_Form_View::registerBlockBegin for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::registerBlockBegin
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     */
    public function pnFormRegisterBlockBegin($pluginName, &$params)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::registerBlockBegin')), E_USER_DEPRECATED);
        $this->registerBlockBegin($pluginName, $params);
    }

    /**
     * Alias to Zikula_Form_View::registerBlockEnd for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::registerBlockEnd
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     *
     * @return string Rendered output.
     */
    public function pnFormRegisterBlockEnd($pluginName, &$params, $content)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::registerBlockEnd')), E_USER_DEPRECATED);

        return $this->registerBlockEnd($pluginName, $params, $content);
    }

    /**
     * Alias to Zikula_Form_View::getPluginId for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Zikula_Form_View::getPluginId
     *
     * @param array  &$params    Parameters passed from the Smarty block function.
     *
     * @return mixed The contents of $params['id'] if set, else the value 'plg#' where # is the IdCount incremented by one
     */
    public function pnFormGetPluginId(&$params)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getPluginId')), E_USER_DEPRECATED);

        return $this->getPluginId($params);
    }

    /**
     * Alias to Zikula_Form_View::isPostBack for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::isPostBack
     *
     * @return bool True if $_POST['__pnFormSTATE'] is set; otherwise false.
     */
    public function pnFormIsPostBack()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::isPostBack')), E_USER_DEPRECATED);

        return $this->isPostBack();
    }

    /**
     * Alias to Zikula_Form_View::formDie for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::formDie
     *
     * @param string $msg The message to display.
     */
    public function pnFormDie($msg)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::formDie')), E_USER_DEPRECATED);
        $this->formDie($msg);
    }

    /**
     * Alias to Zikula_Form_View::translateForDisplay for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::translateForDisplay
     *
     * @param string  $txt      Text to translate for display.
     * @param boolean $doEncode True to formatForDisplay.
     *
     * @return string Text.
     */
    public function pnFormTranslateForDisplay($txt, $doEncode = true)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::translateForDisplay')), E_USER_DEPRECATED);

        return $this->translateForDisplay($txt, $doEncode = true);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::addValidator
     *
     * @param validator $validator Validator to add.
     */
    public function pnFormAddValidator(&$validator)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::addValidator')), E_USER_DEPRECATED);
        $this->addValidator($validator);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::isValid
     *
     * @return boolean True if all validators are valid.
     */
    public function pnFormIsValid()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::isValid')), E_USER_DEPRECATED);

        return $this->isValid();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::validate
     */
    public function pnFormValidate()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::validate')), E_USER_DEPRECATED);
        $this->validate();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::clearValidation
     */
    public function pnFormClearValidation()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::clearValidation')), E_USER_DEPRECATED);
        $this->clearValidation();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::setState
     */
    public function pnFormSetState($region, $varName, &$varValue)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::setState')), E_USER_DEPRECATED);
        $this->setState($region, $varName, $varValue);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::setErrorMsg
     */
    public function pnFormSetErrorMsg($msg)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::setErrorMsg')), E_USER_DEPRECATED);

        return $this->setErrorMsg($msg);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getErrorMsg
     */
    public function pnFormGetErrorMsg()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getErrorMsg')), E_USER_DEPRECATED);

        return $this->getErrorMsg();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::hasError
     */
    public function pnFormHasError()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::hasError')), E_USER_DEPRECATED);

        return $this->hasError();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::registerError
     */
    public function pnFormRegisterError($dummy)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::registerError')), E_USER_DEPRECATED);

        return $this->registerError($dummy);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::redirect
     */
    public function pnFormRedirect($url)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::redirect')), E_USER_DEPRECATED);
        $this->redirect($url);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getPostBackEventReference
     */
    public function pnFormGetPostBackEventReference($plugin, $commandName)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getPostBackEventReference')), E_USER_DEPRECATED);

        return $this->getPostBackEventReference($plugin, $commandName);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::raiseEvent
     */
    public function pnFormRaiseEvent($eventHandlerName, $args)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::raiseEvent')), E_USER_DEPRECATED);

        return $this->raiseEvent($eventHandlerName, $args);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::initializeIncludes
     */
    public function pnFormInitializeIncludes()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::initializeIncludes')), E_USER_DEPRECATED);
        $this->initializeIncludes();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getIncludesText
     */
    public function pnFormGetIncludesText()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getIncludesText')), E_USER_DEPRECATED);

        return $this->getIncludesText();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getIncludesHTML
     */
    public function pnFormGetIncludesHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getIncludesHTML')), E_USER_DEPRECATED);

        return $this->getIncludesHTML();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodeIncludes
     */
    public function pnFormDecodeIncludes()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodeIncludes')), E_USER_DEPRECATED);

        return $this->decodeIncludes();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getAuthKeyHTML
     */
    public function pnFormGetAuthKeyHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getAuthKeyHTML')), E_USER_DEPRECATED);

        return $this->getAuthKeyHTML();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::initializeState
     */
    public function pnFormInitializeState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::initializeState')), E_USER_DEPRECATED);
        $this->initializeState();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getStateText
     */
    public function pnFormGetStateText()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getStateText')), E_USER_DEPRECATED);
        $this->getStateText();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getPluginState
     */
    public function pnFormGetPluginState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getPluginState')), E_USER_DEPRECATED);

        return $this->getPluginState();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getPluginById
     */
    function &pnFormGetPluginById($id)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getPluginById')), E_USER_DEPRECATED);

        return $this->getPluginById($id);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getPluginState_rec
     */
    public function pnFormGetPluginState_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getPluginState_rec')), E_USER_DEPRECATED);

        return $this->getPluginState_rec($plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getStateHTML
     */
    public function pnFormGetStateHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getStateHTML')), E_USER_DEPRECATED);

        return $this->getStateHTML();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodeState
     */
    public function pnFormDecodeState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodeState')), E_USER_DEPRECATED);
        $this->decodeState();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodeEventHandler
     */
    public function pnFormDecodeEventHandler()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated.', array(__CLASS__ . '#' . __FUNCTION__)), E_USER_DEPRECATED);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::initializePlugins
     */
    public function pnFormInitializePlugins()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::initializePlugins')), E_USER_DEPRECATED);

        return $this->initializePlugins();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::initializePlugins_rec
     */
    public function pnFormInitializePlugins_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::initializePlugins_rec')), E_USER_DEPRECATED);
        $this->initializePlugins_rec($plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodePlugins
     */
    public function pnFormDecodePlugins()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodePlugins')), E_USER_DEPRECATED);

        return $this->decodePlugins();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodePlugins_rec
     */
    public function pnFormDecodePlugins_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodePlugins_rec')), E_USER_DEPRECATED);
        $this->decodePlugins_rec($plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodePostBackEvent
     */
    public function pnFormDecodePostBackEvent()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodePostBackEvent')), E_USER_DEPRECATED);
        $this->decodePostBackEvent();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::decodePostBackEvent_rec
     */
    public function pnFormDecodePostBackEvent_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::decodePostBackEvent_rec')), E_USER_DEPRECATED);

        return $this->decodePostBackEvent_rec($plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::postRender
     */
    public function pnFormPostRender()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::postRender')), E_USER_DEPRECATED);

        return $this->postRender();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::postRender_rec
     */
    public function pnFormPostRender_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::postRender_rec')), E_USER_DEPRECATED);
        $this->postRender_rec($plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getValues
     */
    public function pnFormGetValues()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getValues')), E_USER_DEPRECATED);

        return $this->getValues();
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::getValues_rec
     */
    public function pnFormGetValues_rec($plugins, &$result)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::getValues_rec')), E_USER_DEPRECATED);
        $this->getValues_rec($plugins, $result);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::setValues
     */
    public function pnFormSetValues(&$values, $group = null)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::setValues')), E_USER_DEPRECATED);

        return $this->setValues($values, $group);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::setValues2
     */
    public function pnFormSetValues2(&$values, $group = null, $plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::setValues2')), E_USER_DEPRECATED);

        return $this->setValues2($values, $group, $plugins);
    }

    /**
     * Alias to equivalent function in Zikula_Form_View for backward compatibility.
     *
     * @deprecated
     * @see Zikula_Form_View::setValues_rec
     */
    public function pnFormSetValues_rec(&$values, $group, $plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Zikula_Form_View::setValues_rec')), E_USER_DEPRECATED);
        $this->setValues_rec($values, $group, $plugins);
    }
}

/**
 * Alias to the Zikula_Form_AbstractPlugin class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_AbstractPlugin::
 */
class pnFormPlugin extends Zikula_Form_AbstractPlugin
{
    /**
     * Alias to Zikula_Form_AbstractPlugin constructor.
     *
     * @deprecated
     * @see Zikula_Form_AbstractPlugin::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_AbstractPlugin')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_AbstractStyledPlugin class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_AbstractStyledPlugin::
 */
class pnFormStyledPlugin extends Zikula_Form_AbstractStyledPlugin
{
    /**
     * Alias to Zikula_Form_AbstractStyledPlugin constructor.
     *
     * @deprecated
     * @see Zikula_Form_AbstractStyledPlugin::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_AbstractStyledPlugin')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_AbstractHandler class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_AbstractHandler::
 */
class pnFormHandler extends Zikula_Form_AbstractHandler
{
    /**
     * Alias to Zikula_Form_AbstractHandler constructor.
     *
     * @deprecated
     * @see Zikula_Form_AbstractHandler::__construct()
     */
    public function __construct()
    {
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_AbstractHandler')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_BaseListSelector class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_BaseListSelector::
 */
class pnFormBaseListSelector extends Zikula_Form_Plugin_BaseListSelector
{
    /**
     * Alias to Zikula_Form_Plugin_BaseListSelector constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_BaseListSelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_BaseListSelector')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_Button class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_Button::
 */
class pnFormButton extends Zikula_Form_Plugin_Button
{
    /**
     * Alias to Zikula_Form_Plugin_Button constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_Button::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_Button')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_CategoryCheckboxList class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_CategoryCheckboxList::
 */
class pnFormCategoryCheckboxList extends Zikula_Form_Plugin_CategoryCheckboxList
{
    /**
     * Alias to Zikula_Form_Plugin_CategoryCheckboxList constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_CategoryCheckboxList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_CategoryCheckboxList')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_CategorySelector class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_CategorySelector::
 */
class pnFormCategorySelector extends Zikula_Form_Plugin_CategorySelector
{
    /**
     * Alias to Zikula_Form_Plugin_CategorySelector constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_CategorySelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_CategorySelector')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_Checkbox class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_Checkbox::
 */
class pnFormCheckbox extends Zikula_Form_Plugin_Checkbox
{
    /**
     * Alias to Zikula_Form_Plugin_Checkbox constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_Checkbox::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_Checkbox')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_CheckboxList class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_CheckboxList::
 */
class pnFormCheckboxList extends Zikula_Form_Plugin_CheckboxList
{
    /**
     * Alias to Zikula_Form_Plugin_CheckboxList constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_CheckboxList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_CheckboxList')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Block_ContextMenu class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Block_ContextMenu::
 */
class pnFormContextMenu extends Zikula_Form_Block_ContextMenu
{
    /**
     * Alias to Zikula_Form_Block_ContextMenu constructor.
     *
     * @deprecated
     * @see Zikula_Form_Block_ContextMenu::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Block_ContextMenu')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ContextMenu_Item class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ContextMenu_Item::
 */
class pnFormContextMenuItem extends Zikula_Form_Plugin_ContextMenu_Item
{
    /**
     * Alias to Zikula_Form_Plugin_ContextMenu_Item constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ContextMenu_Item::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ContextMenu_Item')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ContextMenu_Reference class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ContextMenu_Reference::
 */
class pnFormContextMenuReference extends Zikula_Form_Plugin_ContextMenu_Reference
{
    /**
     * Alias to Zikula_Form_Plugin_ContextMenu_Reference constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ContextMenu_Reference::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ContextMenu_Reference')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ContextMenu_Separator class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ContextMenu_Separator::
 */
class pnFormContextMenuSeparator extends Zikula_Form_Plugin_ContextMenu_Separator
{
    /**
     * Alias to Zikula_Form_Plugin_ContextMenu_Separator constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ContextMenu_Separator::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ContextMenu_Separator')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_DateInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_DateInput::
 */
class pnFormDateInput extends Zikula_Form_Plugin_DateInput
{
    /**
     * Alias to Zikula_Form_Plugin_DateInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_DateInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_DateInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_DropdownRelationList class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_DropdownRelationList::
 */
class pnFormDropDownRelationlist extends Zikula_Form_Plugin_DropdownRelationList
{
    /**
     * Alias to Zikula_Form_Plugin_DropdownRelationList constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_DropdownRelationList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_DropdownRelationList')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_DropdownList class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_DropdownList::
 */
class pnFormDropdownList extends Zikula_Form_Plugin_DropdownList
{
    /**
     * Alias to Zikula_Form_Plugin_DropdownList constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_DropdownList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_DropdownList')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_EmailInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_EmailInput::
 */
class pnFormEMailInput extends Zikula_Form_Plugin_EmailInput
{
    /**
     * Alias to Zikula_Form_Plugin_EmailInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_EmailInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_EmailInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ErrorMessage class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ErrorMessage::
 */
class pnFormErrorMessage extends Zikula_Form_Plugin_ErrorMessage
{
    /**
     * Alias to Zikula_Form_Plugin constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ErrorMessage::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ErrorMessage')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_FloatInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_FloatInput::
 */
class pnFormFloatInput extends Zikula_Form_Plugin_FloatInput
{
    /**
     * Alias to Zikula_Form_Plugin_FloatInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_FloatInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_FloatInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ImageButton class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ImageButton::
 */
class pnFormImageButton extends Zikula_Form_Plugin_ImageButton
{
    /**
     * Alias to Zikula_Form_Plugin_ImageButton constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ImageButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ImageButton')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_IntInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_IntInput::
 */
class pnFormIntInput extends Zikula_Form_Plugin_IntInput
{
    /**
     * Alias to Zikula_Form_Plugin_IntInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_IntInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_IntInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_Label class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_Label::
 */
class pnFormLabel extends Zikula_Form_Plugin_Label
{
    /**
     * Alias to Zikula_Form_Plugin_Label constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_Label::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_Label')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_LanguageSelector class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_LanguageSelector::
 */
class pnFormLanguageSelector extends Zikula_Form_Plugin_LanguageSelector
{
    /**
     * Alias to Zikula_Form_Plugin_LanguageSelector constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_LanguageSelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_LanguageSelector')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_LinkButton class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_LinkButton::
 */
class pnFormLinkButton extends Zikula_Form_Plugin_LinkButton
{
    /**
     * Alias to Zikula_Form_Plugin_LinkButton constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_LinkButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_LinkButton')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_PostbackFunction class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_PostbackFunction::
 */
class pnFormPostBackFunction extends Zikula_Form_Plugin_PostbackFunction
{
    /**
     * Alias to Zikula_Form_Plugin_PostbackFunction constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_PostbackFunction::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_PostbackFunction')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_RadioButton class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_RadioButton::
 */
class pnFormRadioButton extends Zikula_Form_Plugin_RadioButton
{
    /**
     * Alias to Zikula_Form_Plugin_RadioButton constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_RadioButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_RadioButton')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Block_TabbedPanel class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Block_TabbedPanel::
 */
class pnFormTabbedPanel extends Zikula_Form_Block_TabbedPanel
{
    /**
     * Alias to Zikula_Form_Block_TabbedPanel constructor.
     *
     * @deprecated
     * @see Zikula_Form_Block_TabbedPanel::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Block_TabbedPanel')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Block_TabbedPanelSet class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Block_TabbedPanelSet::
 */
class pnFormTabbedPanelSet extends Zikula_Form_Block_TabbedPanelSet
{
    /**
     * Alias to Zikula_Form_Block_TabbedPanelSet constructor.
     *
     * @deprecated
     * @see Zikula_Form_Block_TabbedPanelSet::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Block_TabbedPanelSet')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_TextInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_TextInput::
 */
class pnFormTextInput extends Zikula_Form_Plugin_TextInput
{
    /**
     * Alias to Zikula_Form_Plugin_TextInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_TextInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_TextInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_UrlInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_UrlInput::
 */
class pnFormURLInput extends Zikula_Form_Plugin_UrlInput
{
    /**
     * Alias to Zikula_Form_Plugin_UrlInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_UrlInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_UrlInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_UploadInput class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_UploadInput::
 */
class pnFormUploadInput extends Zikula_Form_Plugin_UploadInput
{
    /**
     * Alias to Zikula_Form_Plugin_UploadInput constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_UploadInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_UploadInput')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Plugin_ValidationSummary class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Plugin_ValidationSummary::
 */
class pnFormValidationSummary extends Zikula_Form_Plugin_ValidationSummary
{
    /**
     * Alias to Zikula_Form_Plugin_ValidationSummary constructor.
     *
     * @deprecated
     * @see Zikula_Form_Plugin_ValidationSummary::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Plugin_ValidationSummary')), E_USER_DEPRECATED);
    }
}

/**
 * Alias to the Zikula_Form_Block_Volatile class for backward compatibility.
 *
 * @deprecated
 * @see Zikula_Form_Block_Volatile::
 */
class pnFormVolatile extends Zikula_Form_Block_Volatile
{
    /**
     * Alias to Zikula_Form_Block_Volatile constructor.
     *
     * @deprecated
     * @see Zikula_Form_Block_Volatile::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Zikula_Form_Block_Volatile')), E_USER_DEPRECATED);
    }
}

/**
 * @deprecated since 1.2
 * we now directly analyse the 2-digit language and country codes
 * Language list for auto detection of browser language
 */
function cnvlanguagelist()
{
    // sprintf() is deliberate here, do not change - drak.
    LogUtil::log(sprintf('Warning! Function %1$s is deprecated.', __FUNCTION__), E_USER_DEPRECATED);

    $cnvlang = array();
    $cnvlang['KOI8-R'] = 'rus';
    $cnvlang['af'] = 'eng';
    $cnvlang['ar'] = 'ara';
    $cnvlang['ar-ae'] = 'ara';
    $cnvlang['ar-bh'] = 'ara';
    $cnvlang['ar-bh'] = 'ara';
    $cnvlang['ar-dj'] = 'ara';
    $cnvlang['ar-dz'] = 'ara';
    $cnvlang['ar-eg'] = 'ara';
    $cnvlang['ar-iq'] = 'ara';
    $cnvlang['ar-jo'] = 'ara';
    $cnvlang['ar-km'] = 'ara';
    $cnvlang['ar-kw'] = 'ara';
    $cnvlang['ar-lb'] = 'ara';
    $cnvlang['ar-ly'] = 'ara';
    $cnvlang['ar-ma'] = 'ara';
    $cnvlang['ar-mr'] = 'ara';
    $cnvlang['ar-om'] = 'ara';
    $cnvlang['ar-qa'] = 'ara';
    $cnvlang['ar-sa'] = 'ara';
    $cnvlang['ar-sd'] = 'ara';
    $cnvlang['ar-so'] = 'ara';
    $cnvlang['ar-sy'] = 'ara';
    $cnvlang['ar-tn'] = 'ara';
    $cnvlang['ar-ye'] = 'ara';
    $cnvlang['be'] = 'eng';
    $cnvlang['bg'] = 'bul';
    $cnvlang['bo'] = 'tib';
    $cnvlang['ca'] = 'eng';
    $cnvlang['cs'] = 'ces';
    $cnvlang['da'] = 'dan';
    $cnvlang['de'] = 'deu';
    $cnvlang['de-at'] = 'deu';
    $cnvlang['de-ch'] = 'deu';
    $cnvlang['de-de'] = 'deu';
    $cnvlang['de-li'] = 'deu';
    $cnvlang['de-lu'] = 'deu';
    $cnvlang['el'] = 'ell';
    $cnvlang['en'] = 'eng';
    $cnvlang['en-au'] = 'eng';
    $cnvlang['en-bz'] = 'eng';
    $cnvlang['en-ca'] = 'eng';
    $cnvlang['en-gb'] = 'eng';
    $cnvlang['en-ie'] = 'eng';
    $cnvlang['en-jm'] = 'eng';
    $cnvlang['en-nz'] = 'eng';
    $cnvlang['en-ph'] = 'eng';
    $cnvlang['en-tt'] = 'eng';
    $cnvlang['en-us'] = 'eng';
    $cnvlang['en-za'] = 'eng';
    $cnvlang['en-zw'] = 'eng';
    $cnvlang['es'] = 'spa';
    $cnvlang['es-ar'] = 'spa';
    $cnvlang['es-bo'] = 'spa';
    $cnvlang['es-cl'] = 'spa';
    $cnvlang['es-co'] = 'spa';
    $cnvlang['es-cr'] = 'spa';
    $cnvlang['es-do'] = 'spa';
    $cnvlang['es-ec'] = 'spa';
    $cnvlang['es-es'] = 'spa';
    $cnvlang['es-gt'] = 'spa';
    $cnvlang['es-hn'] = 'spa';
    $cnvlang['es-mx'] = 'spa';
    $cnvlang['es-ni'] = 'spa';
    $cnvlang['es-pa'] = 'spa';
    $cnvlang['es-pe'] = 'spa';
    $cnvlang['es-pr'] = 'spa';
    $cnvlang['es-py'] = 'spa';
    $cnvlang['es-sv'] = 'spa';
    $cnvlang['es-uy'] = 'spa';
    $cnvlang['es-ve'] = 'spa';
    $cnvlang['eu'] = 'eng';
    $cnvlang['fi'] = 'fin';
    $cnvlang['fo'] = 'eng';
    $cnvlang['fr'] = 'fra';
    $cnvlang['fr-be'] = 'fra';
    $cnvlang['fr-ca'] = 'fra';
    $cnvlang['fr-ch'] = 'fra';
    $cnvlang['fr-fr'] = 'fra';
    $cnvlang['fr-lu'] = 'fra';
    $cnvlang['fr-mc'] = 'fra';
    $cnvlang['ga'] = 'eng';
    $cnvlang['gd'] = 'eng';
    $cnvlang['gl'] = 'eng';
    $cnvlang['hr'] = 'cro';
    $cnvlang['hu'] = 'hun';
    $cnvlang['in'] = 'ind';
    $cnvlang['is'] = 'isl';
    $cnvlang['it'] = 'ita';
    $cnvlang['it-ch'] = 'ita';
    $cnvlang['it-it'] = 'ita';
    $cnvlang['ja'] = 'jpn';
    $cnvlang['ka'] = 'kat';
    $cnvlang['ko'] = 'kor';
    $cnvlang['mk'] = 'mkd';
    $cnvlang['nl'] = 'nld';
    $cnvlang['nl-be'] = 'nld';
    $cnvlang['nl-nl'] = 'nld';
    $cnvlang['no'] = 'nor';
    $cnvlang['pl'] = 'pol';
    $cnvlang['pt'] = 'por';
    $cnvlang['pt-br'] = 'por';
    $cnvlang['pt-pt'] = 'por';
    $cnvlang['ro'] = 'ron';
    $cnvlang['ro-mo'] = 'ron';
    $cnvlang['ro-ro'] = 'ron';
    $cnvlang['ru'] = 'rus';
    $cnvlang['ru-mo'] = 'rus';
    $cnvlang['ru-ru'] = 'rus';
    $cnvlang['sk'] = 'slv';
    $cnvlang['sl'] = 'slv';
    $cnvlang['sq'] = 'eng';
    $cnvlang['sr'] = 'eng';
    $cnvlang['sv'] = 'swe';
    $cnvlang['sv-fi'] = 'swe';
    $cnvlang['sv-se'] = 'swe';
    $cnvlang['th'] = 'tha';
    $cnvlang['tr'] = 'tur';
    $cnvlang['uk'] = 'ukr';
    $cnvlang['zh-cn'] = 'zho';
    $cnvlang['zh-tw'] = 'zho';

    return $cnvlang;
}

/**
 * clean user input
 *
 * Gets a global variable, cleaning it up to try to ensure that
 * hack attacks don't work
 *
 * @deprecated
 * @see FormUtil::getPassedValues
 * @param var $ name of variable to get
 * @param  $ ...
 *
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarCleanFromInput()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarCleanFromInput()',
        'FormUtil::getPassedValue()')), E_USER_DEPRECATED);

    $vars = func_get_args();
    $resarray = array();
    foreach ($vars as $var) {
        $resarray[] = FormUtil::getPassedValue($var);
    }

    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Function that compares the current php version on the
 * system with the target one
 *
 * Deprecate function reverting to php detecion function
 *
 * @deprecated
 */
function pnPhpVersionCheck($vercheck = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnPhpVersionCheck()',
        'version_compare()')), E_USER_DEPRECATED);
    $minver = str_replace(".", "", $vercheck);
    $curver = str_replace(".", "", phpversion());

    if ($curver >= $minver) {
        return true;
    } else {
        return false;
    }
}

/**
 * see if a user is authorised to carry out a particular task
 *
 * @deprecated
 * @see SecurityUtil::checkPermission()
 * @param realm the realm under test
 * @param component the component under test
 * @param instance the instance under test
 * @param level the level of access required
 * @return bool true if authorised, false if not
 */
function pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel, $testuser = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAuthAction()',
        'SecurityUtil::checkPermission()')), E_USER_DEPRECATED);

    return SecurityUtil::checkPermission($testcomponent, $testinstance, $testlevel, $testuser);
}

/**
 * get authorisation information for this user
 *
 * @deprecated
 * @see SecurityUtil::getAuthInfo()
 * @return array two element array of user and group permissions
 */
function pnSecGetAuthInfo($testuser = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGetAuthInfo()',
        'SecurityUtil::getAuthInfo()')), E_USER_DEPRECATED);

    return SecurityUtil::getAuthInfo($testuser);
}

/**
 * calculate security level for a test item
 *
 * @deprecated
 * @see SecurityUtil::getSecurityLevel
 * @param perms $ array of permissions to test against
 * @param testrealm $ realm of item under test
 * @param testcomponent $ component of item under test
 * @param testinstance $ instance of item under test
 * @return int matching security level
 */
function pnSecGetLevel($perms, $testrealm, $testcomponent, $testinstance)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGetLevel()',
        'SecurityUtil::getSecurityLevel()')), E_USER_DEPRECATED);

    return SecurityUtil::getSecurityLevel($perms, $testcomponent, $testinstance);
}

/**
 * generate an authorisation key
 *
 * The authorisation key is used to confirm that actions requested by a
 * particular user have followed the correct path.  Any stage that an
 * action could be made (e.g. a form or a 'delete' button) this function
 * must be called and the resultant string passed to the client as either
 * a GET or POST variable.  When the action then takes place it first calls
 * <code>pnSecConfirmAuthKey()</code> to ensure that the operation has
 * indeed been manually requested by the user and that the key is valid
 *
 * @deprecated
 * @see SecurityUtil::generateAuthKey
 * @param modname $ the module this authorisation key is for (optional)
 * @return string an encrypted key for use in authorisation of operations
 */
function pnSecGenAuthKey($modname = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecGenAuthKey()',
        'SecurityUtil::generateAuthKey()')), E_USER_DEPRECATED);

    return SecurityUtil::generateAuthKey($modname);
}

/**
 * confirm an authorisation key is valid
 *
 * See description of <code>pnSecGenAuthKey</code> for information on
 * this function
 *
 * @deprecated
 * @see SecurityUtil::confirmAuthKey()
 * @return bool true if the key is valid, false if it is not
 */
function pnSecConfirmAuthKey()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'SecurityUtil::confirmAuthKey()')), E_USER_DEPRECATED);

    return SecurityUtil::confirmAuthKey();
}

/**
 * Wrapper for new pnSecAuthAction() function
 *
 * @deprecated
 * @see SecurityUtil::checkPermission()
 */
function authorised($testrealm, $testcomponent, $testinstance, $testlevel)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAuthAction()',
        'SecurityUtil::checkPermission()')), E_USER_DEPRECATED);

    return pnSecAuthAction($testrealm, $testcomponent, $testinstance, $testlevel);
}

/**
 * add security schema
 *
 * @deprecated
 * @see SecurityUtil::registerPermissionSchema()
 * @param unknown_type $component
 * @param unknown_type $schema
 * @return bool
 */
function pnSecAddSchema($component, $schema)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAddSchema()',
        'SecurityUtil::registerPermissionSchema()')), E_USER_DEPRECATED);

    return SecurityUtil::registerPermissionSchema($component, $schema);
}

/**
 * addinstanceschemainfo - register an instance schema with the security
 * Will fail if an attempt is made to overwrite an existing schema
 *
 * @deprecated
 * @see SecurityUtil::registerPermissionSchema()
 * @param unknown_type $component
 * @param unknown_type $schema
 */
function addinstanceschemainfo($component, $schema)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSecAddSchema()',
        'SecurityUtil::registerPermissionSchema()')), E_USER_DEPRECATED);
    pnSecAddSchema($component, $schema);
}

/**
 * Translation functions - avoids globals in external code
 * Translate level -> name
 *
 * @deprecated
 * @see SecurityUtil::accesslevelname()
 */
function accesslevelname($level)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'accesslevelname()',
        'SecurityUtil::accesslevelname()')), E_USER_DEPRECATED);

    return SecurityUtil::accesslevelname($level);
}

/**
 * get access level names
 *
 * @deprecated
 * @see SecurityUtil::accesslevelnames()
 * @return array of access names
 */
function accesslevelnames()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'accesslevelnames()',
        'SecurityUtil::accesslevelnames()')), E_USER_DEPRECATED);

    return SecurityUtil::accesslevelnames();
}

/**
 * get a Time String in the right format
 *
 * @deprecated
 *
 * @param time $ - prefix string
 * @return mixed string if successfull, false if not
 */
function GetUserTime($time)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'GetUserTime'), E_USER_DEPRECATED);
    if (empty($time)) {
        return;
    }

    if (pnUserLoggedIn()) {
        $time += (pnUserGetVar('tzoffset') - System::getVar('timezone_server')) * 3600;
    } else {
        $time += (System::getVar('timezone_offset') - System::getVar('timezone_server')) * 3600;
    }

    return ($time);
}

/**
 * get status message from previous operation
 *
 * Obtains any status message, and also destroys
 * it from the session to prevent duplication
 *
 *
 * @deprecated
 * @see LogUtil::getStatusMessages()
 * @return string the status message
 */
function pnGetStatusMsg()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnGetStatusMsg()',
        'LogUtil::getStatusMessages()')), E_USER_DEPRECATED);
    $msgStatus = SessionUtil::getVar('_ZStatusMsg');
    SessionUtil::delVar('_ZStatusMsg');
    $msgError = SessionUtil::getVar('_ZErrorMsg');
    SessionUtil::delVar('_ZErrorMsg');
    // Error message overrides status message
    if (!empty($msgError)) {
        $msgStatus = $msgError;
    }

    return $msgStatus;
}

/**
 * ready operating system output
 *
 * Gets a variable, cleaning it up such that any attempts
 * to access files outside of the scope of the Zikula
 * system is not allowed.
 *
 * @deprecated
 * @see DataUtil::formatForOS()
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 **/
function pnVarPrepForOS()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForOS()',
        'DataUtil::formatForOS()')), E_USER_DEPRECATED);

    $resarray = array();

    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForOS($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * ready user output
 *
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected
 *
 * @deprecated
 * @see DataUtil::formatForDisplay
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForDisplay()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForDisplay()',
        'DataUtil::formatForDisplay()')), E_USER_DEPRECATED);

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForDisplay($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    } else {
        return $resarray;
    }
}

/**
 * ready HTML output
 *
 * Gets a variable, cleaning it up such that the text is
 * shown exactly as expected, except for allowed HTML tags which
 * are allowed through
 *
 * @deprecated
 * @see DataUtil::formatForDisplayHTML
 * @param var variable to prepare
 * @param ...
 * @return string/array prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepHTMLDisplay()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepHTMLDisplay()',
        'DataUtil::formatForDisplayHTML()')), E_USER_DEPRECATED);

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForDisplayHTML($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * ready database output
 *
 * Gets a variable, cleaning it up such that the text is
 * stored in a database exactly as expected
 *
 * @deprecated
 * @see DataUtil::formatForStore()
 * @param var $ variable to prepare
 * @param  $ ...
 * @return mixed prepared variable if only one variable passed
 * in, otherwise an array of prepared variables
 */
function pnVarPrepForStore()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnVarPrepForStore()',
        'DataUtil::formatForStore()')), E_USER_DEPRECATED);

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::formatForStore($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Exit the program after displaying the appropriate messages
 *
 * @deprecated
 * @see z_exit()
 * @param msg         The messgage to show
 * @param html        whether or not to generate HTML (can be turned off for command line execution)
 */
if (!function_exists('pn_exit')) {
    function pn_exit($msg, $html = true)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
            'pn_exit()',
            'z_exit()')), E_USER_DEPRECATED);
        z_exit($msg, $html);
    }
}

/**
 * log a string to the designated output destination
 *
 * @deprecated
 * @param file             The file (passed from assertion handler)
 * @param line             The line (passed from assertion handler)
 * @param assert_trigger   The assert trigger (passed from assertion handler)
 */
if (!function_exists('pn_assert_callback_function')) {
    function pn_assert_callback_function($file, $line, $assert_trigger)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'pn_assert_callback_function()', E_USER_DEPRECATED));

        return pn_exit(__('Assertion failed'));
    }
}

/* Legacy APIs to be removed at a later date */

/**
 * Get a session variable
 *
 * @deprecated
 * @see SessionUtil::getVar()
 * @param sring $name of the session variable to get
 * @param string $default the default value to return if the requested session variable is not set
 * @return string session variable requested
 */
function pnSessionGetVar($name, $default = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionGetVar()',
        'SessionUtil::getVar()')), E_USER_DEPRECATED);

    return SessionUtil::getVar($name, $default);
}

/**
 * Set a session variable
 *
 * @deprecated
 * @see SessionUtil::setVar()
 * @param string $name of the session variable to set
 * @param value $value to set the named session variable
 * @return bool true
 */
function pnSessionSetVar($name, $value)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionsetVar()',
        'SessionUtil::setVar()')), E_USER_DEPRECATED);

    return SessionUtil::setVar($name, $value);
}

/**
 * Delete a session variable
 *
 * @deprecated
 * @see SessionUtil::delVar()
 * @param string $name of the session variable to delete
 * @return bool true
 */
function pnSessionDelVar($name)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(
        'pnSessionDelVar()',
        'SessionUtil::delVar()')), E_USER_DEPRECATED);

    return SessionUtil::delVar($name);
}

/**
 * remove censored words
 * @deprecated
 */
function pnVarCensor()
{
    LogUtil::log(__f('Error! The \'pnVarCensor\' function used in \'%s\' is deprecated. Instead, please activate the \'MultiHook\' for this module.', DataUtil::formatForDisplay(pnModGetName())));

    $resarray = array();
    $ourvars = func_get_args();
    foreach ($ourvars as $ourvar) {
        $resarray[] = DataUtil::censor($ourvar);
    }

    // Return vars
    if (func_num_args() == 1) {
        return $resarray[0];
    }

    return $resarray;
}

/**
 * Clear theme engine compiled templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Theme::clear_compiled()
 */
function theme_userapi_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_compiled', 'Theme::clear_compiled()')), E_USER_DEPRECATED);
    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_compiled();

    return $res;
}

/**
 * Clear theme engine cached templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Theme::clear_all_cache()
 */
function theme_userapi_clear_cache()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_cache', 'Theme::clear_all_cache()')), E_USER_DEPRECATED);
    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_all_cache();

    return $res;
}

/**
 * Clear render compiled templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Zikula_View::clear_compiled()
 */
function theme_userapi_render_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_compiled', 'Zikula_View::clear_compiled()')), E_USER_DEPRECATED);
    $view = Zikula_View::getInstance();
    $res      = $view->clear_compiled();

    return $res;
}

/**
 * Clear render cached templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Zikula_View::clear_cache()
 * @param module the module where to clear the cache, emptys = clear all caches
 * @return true or false
 */
function theme_userapi_render_clear_cache($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_cache', 'Zikula_View::clear_cache()')), E_USER_DEPRECATED);
    if (isset($args['module']) && !empty($args['module']) && pnModAvailable($args['module'])) {
        $view = Zikula_View::getInstance($args['module']);
        $res      = $view->clear_cache();
    } else {
        $renderer = Zikula_View::getInstance();
        $res      = $view->clear_all_cache();
    }

    return $res;
}

function pnModInitCoreVars()
{
    return ModUtil::initCoreVars();
}

/**
 * Checks to see if a module variable is set.
 *
 * @deprecated
 * @see ModUtil::hasVar()
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable.
 *
 * @return boolean True if the variable exists in the database, false if not.
 */
function pnModVarExists($modname, $name)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::hasVar()')), E_USER_DEPRECATED);

    return ModUtil::hasVar($modname, $name);
}

/**
 * The pnModGetVar function gets a module variable.
 *
 * If the name parameter is included then function returns the
 * module variable value.
 * if the name parameter is ommitted then function returns a multi
 * dimentional array of the keys and values for the module vars.
 *
 * @deprecated
 * @see ModUtil::getVar()
 *
 * @param string  $modname The name of the module.
 * @param string  $name    The name of the variable.
 * @param boolean $default The value to return if the requested modvar is not set.
 *
 * @return string|array If the name parameter is included then function returns
 *          string - module variable value
 *          if the name parameter is ommitted then function returns
 *          array - multi dimentional array of the keys
 *                  and values for the module vars.
 */
function pnModGetVar($modname, $name = '', $default = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getVar()')), E_USER_DEPRECATED);

    return ModUtil::getVar($modname, $name, $default);
}


/**
 * The pnModSetVar Function sets a module variable.
 *
 * @deprecated
 * @see ModUtil::setVar()
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable.
 * @param string $value   The value of the variable.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModSetVar($modname, $name, $value = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::setVar()')), E_USER_DEPRECATED);

    return ModUtil::setVar($modname, $name, $value);
}

/**
 * The pnModSetVars function sets multiple module variables.
 *
 * @deprecated
 * @see ModUtil::setVars()
 *
 * @param string $modname The name of the module.
 * @param array  $vars    An associative array of varnames/varvalues.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModSetVars($modname, $vars)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::setVars()')), E_USER_DEPRECATED);

    return ModUtil::setVars($modname, $vars);
}

/**
 * The pnModDelVar function deletes a module variable.
 *
 * Delete a module variables. If the optional name parameter is not supplied all variables
 * for the module 'modname' are deleted.
 *
 * @deprecated
 * @see ModUtil::delVar()
 *
 * @param string $modname The name of the module.
 * @param string $name    The name of the variable (optional).
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModDelVar($modname, $name = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::delVar()')), E_USER_DEPRECATED);

    return ModUtil::delVar($modname, $name);
}

/**
 * The pnModGetIDFromName function gets module ID given its name.
 *
 * @deprecated
 * @see ModUtil::getIdFromName()
 *
 * @param string $module The name of the module.
 *
 * @return integer module ID.
 */
function pnModGetIDFromName($module)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getIdFromName()')), E_USER_DEPRECATED);

    return ModUtil::getIdFromName($module);
}

/**
 * The pnModGetInfo function gets information on module.
 *
 * Return array of module information or false if core ( id = 0 ).
 *
 * @deprecated
 * @see ModUtil::getInfo()
 *
 * @param integer $modid The module ID.
 *
 * @return array|boolean Module information array or false.
 */
function pnModGetInfo($modid = 0)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getInfo()')), E_USER_DEPRECATED);

    return ModUtil::getInfo($modid);
}

/**
 * The pnModGetUserMods function gets a list of user modules.
 *
 * @deprecated
 * @see ModUtil::getUserMods()
 *
 * @return array An array of module information arrays.
 */
function pnModGetUserMods()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getUserMods()')), E_USER_DEPRECATED);

    return ModUtil::getUserMods();
}

/**
 * The pnModGetProfilesMods function gets a list of profile modules.
 *
 * @deprecated
 * @see ModUtil::getProfileMods()
 *
 * @return array An array of module information arrays.
 */
function pnModGetProfileMods()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getProfileMods()')), E_USER_DEPRECATED);

    return ModUtil::getProfileMods();
}

/**
 * The pnModGetMessageMods function gets a list of message modules.
 *
 * @deprecated
 * @see ModUtil::getMessageMods()
 *
 * @return array An array of module information arrays.
 */
function pnModGetMessageMods()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getMessageMods()')), E_USER_DEPRECATED);

    return ModUtil::getMessageMods();
}

/**
 * The pnModGetAdminMods function gets a list of administration modules.
 *
 * @deprecated
 * @see ModUtil::getAdminMods()
 *
 * @return array An array of module information arrays.
 */
function pnModGetAdminMods()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getAdminMods()')), E_USER_DEPRECATED);

    return ModUtil::getAdminMods();
}

/**
 * The pnModGetTypeMods function gets a list of modules by module type.
 *
 * @deprecated
 * @see ModUtil::getTypeMods()
 *
 * @param string $type The module type to get (either 'user' or 'admin') (optional) (default='user').
 *
 * @return array An array of module information arrays.
 */
function pnModGetTypeMods($type = 'user')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getTypeMods()')), E_USER_DEPRECATED);

    return ModUtil::getTypeMods($type);
}

/**
 * The pnModGetAllMods function gets a list of all modules.
 *
 * @deprecated
 * @see ModUtil::getAllMods()
 *
 * @return array An array of module information arrays.
 */
function pnModGetAllMods()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getAllMods()')), E_USER_DEPRECATED);

    return ModUtil::getAllMods();
}

/**
 * Loads datbase definition for a module.
 *
 * @deprecated
 * @see ModUtil::dbInfoLoad()
 *
 * @param string  $modname   The name of the module to load database definition for.
 * @param string  $directory Directory that module is in (if known).
 * @param boolean $force     Force table information to be reloaded.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModDBInfoLoad($modname, $directory = '', $force = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::dbInfoLoad()')), E_USER_DEPRECATED);

    return ModUtil::dbInfoLoad($modname, $directory, $force);
}

/**
 * Loads a module.
 *
 * @deprecated
 * @see ModUtil::load()
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModLoad($modname, $type = 'user', $force = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::load()')), E_USER_DEPRECATED);

    return ModUtil::load($modname, $type, $force);
}

/**
 * Load an API module.
 *
 * @deprecated
 * @see ModUtil::loadApi()
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModAPILoad($modname, $type = 'user', $force = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::loadApi()')), E_USER_DEPRECATED);

    return ModUtil::loadApi($modname, $type, $force);
}

/**
 * Load a module.
 *
 * @deprecated
 * @see ModUtil::loadGeneric()
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of functions to load.
 * @param boolean $force   Determines to load Module even if module isn't active.
 * @param boolean $api     Whether or not to load an API (or regular) module.
 *
 * @return string|boolean Name of module loaded, or false on failure.
 */
function pnModLoadGeneric($modname, $type = 'user', $force = false, $api = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::loadGeneric()')), E_USER_DEPRECATED);

    return ModUtil::loadGeneric($modname, $type, $force, $api);
}

/**
 * Run a module function.
 *
 * @deprecated
 * @see ModUtil::func()
 *
 * @param string $modname The name of the module.
 * @param string $type    The type of function to run.
 * @param string $func    The specific function to run.
 * @param array  $args    The arguments to pass to the function.
 *
 * @return mixed.
 */
function pnModFunc($modname, $type = 'user', $func = 'main', $args = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::func()')), E_USER_DEPRECATED);

    return ModUtil::func($modname, $type, $func, $args);
}

/**
 * Run an module API function.
 *
 * @deprecated
 * @see ModUtil::apiFunc()
 *
 * @param string $modname The name of the module.
 * @param string $type    The type of function to run.
 * @param string $func    The specific function to run.
 * @param array  $args    The arguments to pass to the function.
 *
 * @return mixed.
 */
function pnModAPIFunc($modname, $type = 'user', $func = 'main', $args = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::apiFunc()')), E_USER_DEPRECATED);

    return ModUtil::apiFunc($modname, $type, $func, $args);
}

/**
 * Run a module function.
 *
 * @deprecated
 * @see ModUtil::exec()
 *
 * @param string  $modname The name of the module.
 * @param string  $type    The type of function to run.
 * @param string  $func    The specific function to run.
 * @param array   $args    The arguments to pass to the function.
 * @param boolean $api     Whether or not to execute an API (or regular) function.
 *
 * @return mixed.
 */
function pnModFuncExec($modname, $type = 'user', $func = 'main', $args = array(), $api = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::exec()')), E_USER_DEPRECATED);

    return ModUtil::exec($modname, $type, $func, $args);
}

/**
 * Generate a module function URL.
 *
 * If the module is non-API compliant (type 1) then
 * a) $func is ignored.
 * b) $type=admin will generate admin.php?module=... and $type=user will generate index.php?name=...
 *
 * @deprecated
 * @see ModUtil::url()
 *
 * @param string       $modname      The name of the module.
 * @param string       $type         The type of function to run.
 * @param string       $func         The specific function to run.
 * @param array        $args         The array of arguments to put on the URL.
 * @param boolean|null $ssl          Set to constant null,true,false $ssl = true not $ssl = 'true'  null - leave the current status untouched,
 *                                   true - create a ssl url, false - create a non-ssl url.
 * @param string       $fragment     The framgment to target within the URL.
 * @param boolean|null $fqurl        Fully Qualified URL. True to get full URL, eg for redirect, else gets root-relative path unless SSL.
 * @param boolean      $forcelongurl Force pnModURL to not create a short url even if the system is configured to do so.
 * @param string       $forcelang    Force the inclusion of the $forcelang or default system language in the generated url.
 *
 * @return string Absolute URL for call
 */
function pnModURL($modname, $type = 'user', $func = 'main', $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::url()')), E_USER_DEPRECATED);

    return ModUtil::url($modname, $type, $func, $args, $ssl, $fragment, $fqurl, $forcelongurl, $forcelang);
}

/**
 * Check if a module is available.
 *
 * @deprecated
 * @see ModUtil::available()
 *
 * @param string  $modname The name of the module.
 * @param boolean $force   Force.
 *
 * @return boolean True if the module is available, false if not.
 */
function pnModAvailable($modname = null, $force = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::available()')), E_USER_DEPRECATED);

    return ModUtil::available($modname, $force);
}

/**
 * Get name of current top-level module.
 *
 * @deprecated
 * @see ModUtil::getName()
 *
 * @return string The name of the current top-level module, false if not in a module.
 */
function pnModGetName()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getName()')), E_USER_DEPRECATED);

    return ModUtil::getName();
}

/**
 * Register a hook function.
 *
 * @deprecated
 * @see ModUtil::registerHook()
 *
 * @param object $hookobject The hook object.
 * @param string $hookaction The hook action.
 * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
 * @param string $hookmodule Name of the hook module.
 * @param string $hooktype   Name of the hook type.
 * @param string $hookfunc   Name of the hook function.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModRegisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::registerHook()')), E_USER_DEPRECATED);

    return ModUtil::registerHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc);
}


/**
 * Unregister a hook function.
 *
 * @deprecated
 * @see ModUtil::unregisterHook()
 *
 * @param string $hookobject The hook object.
 * @param string $hookaction The hook action.
 * @param string $hookarea   The area of the hook (either 'GUI' or 'API').
 * @param string $hookmodule Name of the hook module.
 * @param string $hooktype   Name of the hook type.
 * @param string $hookfunc   Name of the hook function.
 *
 * @return boolean True if successful, false otherwise.
 */
function pnModUnregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::unregisterHook()')), E_USER_DEPRECATED);

    return ModUtil::unregisterHook($hookobject, $hookaction, $hookarea, $hookmodule, $hooktype, $hookfunc);
}

/**
 * Carry out hook operations for module.
 *
 * @deprecated
 * @see ModUtil::callHooks()
 *
 * @param string  $hookobject The object the hook is called for - one of 'item', 'category' or 'module'.
 * @param string  $hookaction The action the hook is called for - one of 'new', 'create', 'modify', 'update', 'delete', 'transform', 'display', 'modifyconfig', 'updateconfig'.
 * @param integer $hookid     The id of the object the hook is called for (module-specific).
 * @param array   $extrainfo  Extra information for the hook, dependent on hookaction.
 * @param boolean $implode    Implode collapses all display hooks into a single string - default to true for compatability with .7x.
 *
 * @return string|array String output from GUI hooks, extrainfo array for API hooks.
 */
function pnModCallHooks($hookobject, $hookaction, $hookid, $extrainfo = array(), $implode = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::callHooks()')), E_USER_DEPRECATED);

    return ModUtil::callHooks($hookobject, $hookaction, $hookid, $extrainfo, $implode);
}

/**
 * Determine if a module is hooked by another module.
 *
 * @deprecated
 * @see ModUtil::isHooked()
 *
 * @param string $tmodule The target module.
 * @param string $smodule The source module - default the current top most module.
 *
 * @return boolean True if the current module is hooked by the target module, false otherwise.
 */
function pnModIsHooked($tmodule, $smodule)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::isHooked()')), E_USER_DEPRECATED);

    return ModUtil::isHooked($tmodule, $smodule);
}

/**
 * The pnModLangLoad function loads the language files for a module.
 *
 * @deprecated define based language system support stopped with Zikula 1.3.0
 *
 * @param string  $modname Name of the module.
 * @param string  $type    Type of the language file to load e.g. user, admin.
 * @param boolean $api     Load api lang file or gui lang file.
 *
 * @return boolean False as this function is depreciated.
 */
function pnModLangLoad($modname, $type = 'user', $api = false)
{
    return LogUtil::registerError(__('Error! Function pnModLangLoad is deprecated.', 404));
}

/**
 * Get the base directory for a module.
 *
 * Example: If the webroot is located at
 * /var/www/html
 * and the module name is Template and is found
 * in the modules directory then this function
 * would return /var/www/html/modules/Template
 *
 * If the Template module was located in the system
 * directory then this function would return
 * /var/www/html/system/Template
 *
 * This allows you to say:
 * include(pnModGetBaseDir() . '/includes/private_functions.php');.
 *
 * @deprecated
 * @see ModUtil::getBaseDir()
 *
 * @param string $modname Name of module to that you want the base directory of.
 *
 * @return string The path from the root directory to the specified module.
 */
function pnModGetBaseDir($modname = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getBaseDir()')), E_USER_DEPRECATED);

    return ModUtil::getBaseDir($modname);
}

/**
 * Gets the modules table.
 *
 * Small wrapper function to avoid duplicate sql.
 *
 * @deprecated
 * @see ModUtil::getModsTable()
 *
 * @return array An array modules table.
 */
function pnModGetModsTable()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getModsTable()')), E_USER_DEPRECATED);

    return ModUtil::getModsTable();
}

class ModuleUtil
{
    /**
     * Generic modules select function. Only modules in the module
     * table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @deprecated
     * @see ModUtil::getModules()
     *
     * @param where The where clause to use for the select
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModules ($where='', $sort='displayname')
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'ModUtil::getModules()')), E_USER_DEPRECATED);

        return ModUtil::getModules($where, $sort);
    }


    /**
     * Return an array of modules in the specified state, only modules in
     * the module table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @deprecated
     * @see ModUtil::getModulesByState()
     *
     * @param state    The module state (optional) (defaults = active state)
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModulesByState($state=3, $sort='displayname')
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'ModUtil::getModulesByState()')), E_USER_DEPRECATED);

        return ModUtil::getModulesByState($state, $sort);
    }
}

// blocks

/**
 * display all blocks in a block position
 *
 * @deprecated
 * @see BlockUtil::displayPosition()
 *
 * @param $side block position to render
 */
function pnBlockDisplayPosition($side, $echo = true, $implode = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::displayPosition()')), E_USER_DEPRECATED);

    return BlockUtil::displayPosition($side, $echo, $implode);
}

/**
 * show a block
 *
 * @deprecated
 * @see BlockUtil::show()
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @param array $blockinfo information parameters
 * @return mixed blockinfo array or null
 */
function pnBlockShow($modname, $block, $blockinfo = array())
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::show()')), E_USER_DEPRECATED);

    return BlockUtil::show($modname, $block, $blockinfo);
}

/**
 * Display a block based on the current theme
 *
 * @deprecated
 * @see BlockUtil::themeBlock()
 */
function pnBlockThemeBlock($row)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::themeBlock()')), E_USER_DEPRECATED);

    return BlockUtil::themeBlock($row);
}

/**
 * load a block
 *
 * @deprecated
 * @see BlockUtil::load()
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @return bool true on successful load, false otherwise
 */
function pnBlockLoad($modname, $block)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::load()')), E_USER_DEPRECATED);

    return BlockUtil::load($modname, $block);
}

/**
 * load all blocks
 *
 * @deprecated
 * @see BlockUtil::loadAll()
 *
 * @return array array of blocks
 */
function pnBlockLoadAll()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::loadAll()')), E_USER_DEPRECATED);

    return BlockUtil::loadAll();
}

/**
 * extract an array of config variables out of the content field of a
 * block
 *
 * @deprecated
 * @see BlockUtil::varsFromContent()
 *
 * @param the $ content from the db
 */
function pnBlockVarsFromContent($content)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::varsFromContent()')), E_USER_DEPRECATED);

    return BlockUtil::varsFromContent($content);
}

/**
 * put an array of config variables in the content field of a block
 *
 * @deprecated
 * @see BlockUtil::varsToContent()
 *
 * @param the $ config vars array, in key->value form
 */
function pnBlockVarsToContent($vars)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::varsToContent()')), E_USER_DEPRECATED);

    return BlockUtil::varsToContent($vars);
}

/**
 * Checks if user controlled block state
 *
 * Checks if the user has a state set for a current block
 * Sets the default state for that block if not present
 *
 * @deprecated
 * @see BlockUtil::checkUserBlock()
 *
 * @access private
 */
function pnCheckUserBlock($row)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::checkUserBlock()')), E_USER_DEPRECATED);

    return BlockUtil::checkUserBlock($row);
}

/**
 * get block information
 *
 * @deprecated
 * @see BlockUtil::getBlocksInfo()
 *
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlocksGetInfo()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::getBlocksInfo()')), E_USER_DEPRECATED);

    return BlockUtil::getBlocksInfo();
}

/**
 * get block information
 *
 * @deprecated
 * @see BlockUtil::getBlockInfo()
 *
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlockGetInfo($value, $assocKey = 'bid')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::getBlockInfo()')), E_USER_DEPRECATED);

    return BlockUtil::getBlockInfo($value, $assocKey);
}

/**
 * get block information
 * @param title the block title
 * @return array array of block information
 */
function pnBlockGetInfoByTitle($title)
{
    return BlockUtil::getInfoByTitle($title);
}

/**
 * alias to BlockUtil::displayPosition()
 *
 * @deprecated
 * @see BlockUtil::displayPosition()
 */
function blocks($side)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::displayPosition()')), E_USER_DEPRECATED);

    return BlockUtil::displayPosition($side);
}

/**
 * alias to BlockUtil::themesideblock()
 *
 * @deprecated
 * @see BlockUtil::themesideblock()
 */
function themesideblock($row)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::themesideblock()')), E_USER_DEPRECATED);

    return BlockUtil::themesideblock($row);
}

// user

/**
 * Log the user in
 *
 * @deprecated
 * @see UserUtil::loginUsing()
 *
 * @param uname $ the name of the user logging in
 * @param pass $ the password of the user logging in
 * @param rememberme whether $ or not to remember this login
 * @param checkPassword bool true whether or not to check the password
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogIn($uname, $pass, $rememberme = false, $checkPassword = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::loginUsing()')), E_USER_DEPRECATED);

    $authenticationMethod = array(
        'modname'   => 'Users',
    );
    if (ModUtil::getVar(Users_Constant::MODNAME, Users_Constant::MODVAR_LOGIN_METHOD, Users_Constant::LOGIN_METHOD_UNAME) == Users_Constant::LOGIN_METHOD_EMAIL) {
        $authenticationMethod['method'] = 'email';
    } else {
        $authenticationMethod['method'] = 'uname';
    }

    return (bool)UserUtil::loginUsing($authenticationMethod, array('login_id' => $uname, 'pass' => $pass), $rememberme, null, $checkPassword);
}

/**
 * Log the user in via the REMOTE_USER SERVER property. This routine simply
 * checks if the REMOTE_USER exists in the PN environment: if he does a
 * session is created for him, regardless of the password being used.
 *
 * @deprecated
 * @see UserUtil::loginHttp()
 *
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogInHTTP()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::loginHttp()')), E_USER_DEPRECATED);

    return UserUtil::loginHttp();
}

/**
 * Log the user out
 *
 * @deprecated
 * @see UserUtil::logout()
 *
 * @public
 * @return bool true if the user successfully logged out, false otherwise
 */
function pnUserLogOut()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::logout()')), E_USER_DEPRECATED);

    return UserUtil::logout();
}

/**
 * is the user logged in?
 *
 * @deprecated
 * @see UserUtil::isLoggedIn()
 *
 * @public
 * @returns bool true if the user is logged in, false if they are not
 */
function pnUserLoggedIn()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::isLoggedIn()')), E_USER_DEPRECATED);

    return UserUtil::isLoggedIn();
}

/**
 * Get all user variables, maps new style attributes to old style user data.
 *
 * @deprecated
 * @see UserUtil::getVars()
 *
 * @param uid $ the user id of the user
 * @return array an associative array with all variables for a user
 */
function pnUserGetVars($id, $force = false, $idfield = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getVars()')), E_USER_DEPRECATED);

    return UserUtil::getVars($id, $force, $idfield);
}

/**
 * get a user variable
 *
 * @deprecated
 * @see UserUtil::getVar()
 *
 * @param name $ the name of the variable
 * @param uid $ the user to get the variable for
 * @param default $ the default value to return if the specified variable doesn't exist
 * @return string the value of the user variable if successful, null otherwise
 */
function pnUserGetVar($name, $uid = -1, $default = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getVar()')), E_USER_DEPRECATED);

    return UserUtil::getVar($name, $uid, $default);
}

/**
 * Set a user variable. This can be
 * - a field in the users table
 * - or an attribute and in this case either a new style attribute or an old style user information.
 *
 * Examples:
 * pnUserSetVar('pass', 'mysecretpassword'); // store a password (should be hashed of course)
 * pnUserSetVar('avatar', 'mypicture.gif');  // stores an users avatar, new style
 * (internally both the new and the old style write the same attribute)
 *
 * If the user variable does not exist it will be created automatically. This means with
 * pnUserSetVar('somename', 'somevalue');
 * you can easily create brand new users variables onthefly.
 *
 * This function does not allow you to set uid or uname.
 *
 * @deprecated
 * @see UserUtil::setVar()
 *
 * @param name $ the name of the variable
 * @param value $ the value of the variable
 * @param uid $ the user to set the variable for
 * @return bool true if the set was successful, false otherwise
 */
function pnUserSetVar($name, $value, $uid = -1)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::setVar()')), E_USER_DEPRECATED);

    return UserUtil::setVar($name, $value, $uid);
}

/**
 * Alias to UserUtil::setVar for setting the password on the account.
 *
 * @deprecated
 * @see UserUtil::setPassword()
 *
 * @param string $pass The password.
 * @return bool True if set; otherwise false.
 */
function pnUserSetPassword($pass)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::setPassword()')), E_USER_DEPRECATED);

    return UserUtil::setPassword($pass);
}

/**
 * Delete the contents of a user variable. This can either be
 * - a variable stored in the users table or
 * - an attribute to the users table, either a new style sttribute or the old style user information
 *
 * Examples:
 * pnUserDelVar('ublock');  // clears the recent users table entry for 'ublock'
 * pnUserDelVar('_YOURAVATAR', 123), // removes a users avatar, old style (uid = 123)
 * pnUserDelVar('avatar', 123);  // removes a users avatar, new style (uid=123)
 * (internally both the new style and the old style clear the same attribute)
 *
 * It does not allow the deletion of uid, email, uname and pass (word) as these are mandatory
 * fields in the users table.
 *
 * @deprecated
 * @see UserUtil::delVar()
 *
 * @param name $ the name of the variable
 * @param uid $ the user to delete the variable for
 * @return boolen true on success, false on failure
 */
function pnUserDelVar($name, $uid = -1)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::delVar()')), E_USER_DEPRECATED);

    return UserUtil::delVar($name, $uid);
}

/**
 * get the user's theme
 * This function will return the current theme for the user.
 * Order of theme priority:
 *  - page-specific
 *  - category
 *  - user
 *  - system
 *
 * @deprecated
 * @see UserUtil::getTheme()
 *
 * @public
 * @return string the name of the user's theme
 **/
function pnUserGetTheme($force = false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getTheme()')), E_USER_DEPRECATED);

    return UserUtil::getTheme($force);
}

/**
 * get the user's language
 *
 * This function returns the deprecated 3 digit language codes, you need to switch APIs
 *
 * @deprecated
 * @see ZLanguage::getLanguageCode()
 *
 * @return string the name of the user's language
 */
function pnUserGetLang()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ZLanguage::getLanguageCode()')), E_USER_DEPRECATED);

    return ZLanguage::getLanguageCodeLegacy();
}

/**
 * get a list of user information
 *
 * @deprecated
 * @see UserUtil::getAll()
 *
 * @public
 * @return array array of user arrays
 */
function pnUserGetAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = -1, $startnum = -1, $activated = '', $regexpfield = '', $regexpression = '', $where = '')
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getAll()')), E_USER_DEPRECATED);

    return UserUtil::getAll($sortbyfield, $sortorder, $limit, $startnum, $activated, $regexpfield, $regexpression, $where);
}

/**
 * Get the uid of a user from the username
 *
 * @deprecated
 * @see UserUtil::getIdFromName()
 *
 * @param uname $ the username
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromName($uname)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getIdFromName()')), E_USER_DEPRECATED);

    return UserUtil::getIdFromName($uname);
}

/**
 * Get the uid of a user from the email (case for unique emails)
 *
 * @deprecated
 * @see UserUtil::getIdFromEmail()
 *
 * @param email $ the user email
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromEmail($email)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getIdFromEmail()')), E_USER_DEPRECATED);

    return UserUtil::getIdFromEmail($email);
}

/**
 * Checks the alias and returns if we save the data in the
 * Profile module's user_data table or the users table.
 * This should be removed if we ever go fully dynamic
 *
 * @deprecated
 * @see UserUtil::fieldAlias()
 *
 * @param label $ the alias of the field to check
 * @return true if found, false if not, void upon error
 */
function pnUserFieldAlias($label)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::fieldAlias()')), E_USER_DEPRECATED);

    return UserUtil::fieldAlias($label);
}

/**
 * Load a theme
 *
 * include theme.php for the requested theme
 *
 * @return bool true if successful, false otherwiese
 */
function pnThemeLoad($theme)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeLoad()', 'ThemeUtil::load()')), E_USER_DEPRECATED);

    return ThemeUtil::load($theme);
}

/**
 * return a theme variable
 *
 * @return mixed theme variable value
 */
function pnThemeGetVar($name = null, $default = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetVar()', 'ThemeUtil::getVar()')), E_USER_DEPRECATED);

    return ThemeUtil::getVar($name, $default);
}

/**
 * pnThemeGetAllThemes
 *
 * list all available themes
 *
 * possible values of filter are
 * PNTHEME_FILTER_ALL - get all themes (default)
 * PNTHEME_FILTER_USER - get user themes
 * PNTHEME_FILTER_SYSTEM - get system themes
 * PNTHEME_FILTER_ADMIN - get admin themes
 *
 * @param filter - filter list of returned themes by type
 * @return array of available themes
 **/
function pnThemeGetAllThemes($filter = PNTHEME_FILTER_ALL, $state = PNTHEME_STATE_ACTIVE, $type = PNTHEME_TYPE_ALL)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetAllThemes()', 'ThemeUtil::getAllThemes()')), E_USER_DEPRECATED);

    return ThemeUtil::getAllThemes($filter, $state, $type);
}

/**
 * load the language file for a theme
 *
 * @author Patrick Kellum
 * @return void
 */
function pnThemeLangLoad($script = 'global', $theme = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeLangLoad()', 'ThemeUtil::loadLanguage()')), E_USER_DEPRECATED);

    ThemeUtil::loadLanguage($script, $theme);

    return;
}

/**
 * pnThemeGetIDFromName
 *
 * get themeID given its name
 *
 * @author Mark West
 * @link http://www.markwest.me.uk
 * @param 'theme' the name of the theme
 * @return int theme ID
 */
function pnThemeGetIDFromName($theme)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetIDFromName()', 'ThemeUtil::getIDFromName()')), E_USER_DEPRECATED);

    return ThemeUtil::getIDFromName($theme);
}

/**
 * pnThemeGetInfo
 *
 * Returns information about a theme.
 *
 * @author Mark West
 * @param string $themeid Id of the theme
 * @return array the theme information
 **/
function pnThemeGetInfo($themeid)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetInfo()', 'ThemeUtil::getInfo()')), E_USER_DEPRECATED);

    return ThemeUtil::getInfo($themeid);
}

/**
 * gets the themes table
 *
 * small wrapper function to avoid duplicate sql
 * @access private
 * @return array modules table
*/
function pnThemeGetThemesTable()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetThemesTable()', 'ThemeUtil::getThemesTable()')), E_USER_DEPRECATED);

    return ThemeUtil::getThemesTable();
}

function search_construct_where($args, $fields, $mlfield = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('search_construct_where()', 'Search_Api_User::construct_where()')), E_USER_DEPRECATED);

    return Search_Api_User::construct_where($args, $fields, $mlfield);

}

function search_split_query($q, $dbwildcard = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('search_split_query()', 'Search_Api_User::split_query()')), E_USER_DEPRECATED);

    return Search_Api_User::split_query($q, $dbwildcard);
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * @param array   $data           The object to serialize.
 * @param boolean $functions      Whether to show function names for objects (default=false) (optional).
 * @param integer $recursionLevel The current recursion level.
 *
 * @deprecated since 1.3.0
 *
 * @return string A string containing serialized data.
 */
function _prayer($data, $functions = false, $recursionLevel = 0)
{
    LogUtil::log(__f('Warning! Function %s is deprecated.', array(__FUNCTION__), E_USER_DEPRECATED));
}

/**
 * A prayer shortcut.
 *
 * @param array   $data The object to serialize.
 * @param boolean $die  Whether to shutdown the process or not.
 *
 * @deprecated since 1.3.0
 *
 * @return void
 */
function z_prayer($data, $die = true)
{
    LogUtil::log(__f('Warning! Function %s is deprecated.', array(__FUNCTION__), E_USER_DEPRECATED));
}

/**
 * Serialize the given data in an easily human-readable way for debug purposes.
 *
 * Taken from http://dev.nexen.net/scripts/details.php?scripts=707.
 *
 * @param array   $data      The object to serialize.
 * @param boolean $functions Whether to show function names for objects (default=false) (optional).
 *
 * @deprecated since 1.3.0
 *
 * @return void
 */
function prayer($data, $functions = false)
{
    LogUtil::log(__f('Warning! Function %s is deprecated.', array(__FUNCTION__), E_USER_DEPRECATED));
}

/**
 * AjaxUtil.
 */
class AjaxUtil
{
    /**
     * Immediately stops execution and returns an error message.
     *
     * @param string  $message      Error text.
     * @param array   $other        Optional data to attach to the response.
     * @param boolean $createauthid Flag to create or not a new authkey.
     * @param boolean $displayalert Flag to display the error as an alert or not.
     * @param string  $code         Optional error code, default '400 Bad data'.
     *
     * @throws Zikula_Exception_Forbidden If there are errors in when legacymode is disabled.
     *
     * @deprecated since 1.3.0
     *
     * @return void
     */
    public static function error($message = '', $other = array(), $createauthid = false, $displayalert = true, $code = '400 Bad data')
    {
        if (!System::isLegacyMode()) {
            if (LogUtil::hasErrors()) {
                if (!$message) {
                    throw new Zikula_Exception_Forbidden();
                }
            }

            throw new Zikula_Exception_Forbidden($message);
        }
        // Below for reference - to be deleted.


        if (empty($message)) {
            $type = LogUtil::getErrorType();
            $code = $type ? $type : $code;
            $message = LogUtil::getErrorMessagesText("\n");
        }

        if (!empty($message)) {
            $data = array('errormessage' => $message);
            if (is_array($other)) {
                $data = array_merge($data, $other);
            }
        }

        $data['displayalert'] = ($displayalert === true ? '1' : '0');

        self::output($data, $createauthid, false, true, $code);
    }

    /**
     * Encode data in JSON and return.
     *
     * This functions can add a new authid if requested to do so (default).
     * If the supplied args is not an array, it will be converted to an
     * array with 'data' as key.
     * Authid field will always be named 'authid'. Any other field 'authid'
     * will be overwritten!
     * Script execution stops here
     *
     * @param mixed   $args         String or array of data.
     * @param boolean $createauthid Create a new authid and send it back to the calling javascript.
     * @param boolean $xjsonheader  Send result in X-JSON: header for prototype.js.
     * @param boolean $statusmsg    Include statusmsg in output.
     * @param string  $code         Optional error code, default '200 OK'.
     *
     * @deprecated since 1.3.0
     *
     * @return void
     */
    public static function output($args, $createauthid = false, $xjsonheader = false, $statusmsg = true, $code = '200 OK')
    {
        if (!System::isLegacyMode()) {
            $response = new Zikula_Response_Ajax($args);
            echo $response;
            System::shutDown();
        }
        // Below for reference - to be deleted.

        // check if an error message is set
        $msgs = LogUtil::getErrorMessagesText('<br />');

        if ($msgs != false && !empty($msgs)) {
            self::error($msgs);
        }

        $data = !is_array($args) ? array('data' => $args) : $args;

        if ($statusmsg === true) {
            // now check if a status message is set
            $msgs = LogUtil::getStatusMessagesText('<br />');
            $data['statusmsg'] = $msgs;
        }

        if ($createauthid === true) {
            $data['authid'] = SecurityUtil::generateAuthKey(ModUtil::getName());
        }

        // convert the data to UTF-8 if not already encoded as such
        // Note: this isn't strict test but relying on the site language pack encoding seems to be a good compromise
        if (ZLanguage::getEncoding() != 'utf-8') {
            $data = DataUtil::convertToUTF8($data);
        }

        $output = json_encode($data);

        header("HTTP/1.0 $code");
        header('Content-type: application/json');
        if ($xjsonheader == true) {
            header('X-JSON:(' . $output . ')');
        }
        echo $output;
        System::shutdown();
    }
}

interface FilterUtil_Replace extends FilterUtil_ReplaceInterface{}
interface FilterUtil_Build extends FilterUtil_BuildInterface{}
abstract class FilterUtil_Common extends FilterUtil_AbstractBase{}
abstract class FilterUtil_PluginCommon extends FilterUtil_AbstractPlugin{}
