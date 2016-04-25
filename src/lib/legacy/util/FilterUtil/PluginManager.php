<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Plugin manager class.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\FilterUtil
 */
class FilterUtil_PluginManager extends FilterUtil_AbstractBase
{
    /**
     * Loaded plugins.
     *
     * @var array
     */
    private $_plg = array();

    /**
     * Loaded plugins list.
     *
     * @var array;
     */
    private $_loaded = array();

    /**
     * Loaded operators.
     *
     * @var array
     */
    private $_ops;

    /**
     * Loaded replaces.
     *
     * @var array
     */
    private $_replaces;

    /**
     * Specified restrictions.
     *
     * @var array
     */
    private $_restrictions;

    /**
     * Constructor.
     *
     * @param FilterUtil_Config $config FilterUtil Configuration object.
     * @param array             $plgs   Plugins to load in form "plugin name => config array".
     */
    public function __construct(FilterUtil_Config $config, $plgs = null)
    {
        parent::__construct($config);

        if ($plgs !== null && is_array($plgs) && count($plgs) > 0) {
            $ok = $this->loadPlugins($plgs);
        }

        if ($ok == false) {
            return false;
        }
    }

    /**
     * Loads plugins.
     *
     * @param array $plgs Array of plugin informations in form "plugin's name => config array".
     *
     * @return bool true on success, false otherwise.
     */
    public function loadPlugins($plgs)
    {
        $error = false;

        foreach ($plgs as $k => $v) {
            $error = ($this->loadPlugin($k, $v) ? $error : true);
        }

        return $error;
    }

    /**
     * Loads restrictions.
     *
     * @param array $rest Array of allowed operators per field in the form "field's name => operator array".
     *
     * @return void
     */
    public function loadRestrictions($rest)
    {
        if (empty($rest) || !is_array($rest)) {
            return;
        }

        foreach ($rest as $field => $ops) {
            // accept registered operators only
            $ops = array_filter(array_intersect((array)$ops, array_keys($this->_ops)));
            if (!empty($ops)) {
                $this->_restrictions[$field] = $ops;
            }
        }
    }

    /**
     * Available plugins list.
     *
     * @return array List of the available plugins.
     */
    public static function getPluginsAvailable()
    {
        $classNames = array();
        $classNames['category']    = 'FilterUtil_Filter_Category';
        $classNames['default']     = 'FilterUtil_Filter_Default';
        $classNames['date']        = 'FilterUtil_Filter_Date';
        $classNames['mnlist']      = 'FilterUtil_Filter_Mnlist';
        $classNames['pmlist']      = 'FilterUtil_Filter_Pmlist';
        $classNames['replaceName'] = 'FilterUtil_Filter_ReplaceName';

        // collect classes from other providers also allows for override
        // TODO A [This is only allowed for the module which owns this object.]

        $event = new \Zikula\Core\Event\GenericEvent();
        $event->setData($classNames);
        $classNames = EventUtil::getManager()->dispatch('zikula.filterutil.get_plugin_classes', $event)->getData();

        return $classNames;
    }

    /**
     * Loads a single plugin.
     *
     * @param string $name   Plugin's name.
     * @param array  $config Plugin's config.
     *
     * @return integer The plugin's id.
     */
    public function loadPlugin($name, $config = array())
    {
        if ($this->isLoaded($name)) {
            return $this->_loaded[$name];
        }

        $plugins = $this->getPluginsAvailable();
        if (isset($plugins[$name]) && !empty($plugins[$name]) && class_exists($plugins[$name])) {
            $class = $plugins[$name];

            $this->addCommon($config);
            $obj = new $class($config);

            $this->_plg[] = $obj;
            end($this->_plg);
            $key = key($this->_plg);
            $obj = $this->_plg[$key];

            $obj->setID($key);
            $this->_registerPlugin($key);
            $this->_loaded[$name] = $key;

            return key(end($this->_plg));
        } elseif (System::isLegacyMode()) {
            return $this->loadPluginLegacy();
        }

        return false;
    }

    /**
     * Loads a single plugin.
     *
     * @param string $name   Plugin's name.
     * @param array  $config Plugin's config.
     *
     * @return integer The plugin's id.
     */
    public function loadPluginLegacy($name, $config = array())
    {
        $module = $this->getConfig()->getModule();
        if (strpos($name, '@')) {
            list($module, $name) = explode('@', $name, 2);
        }

        if ($this->isLoaded("$module@$name")) {
            return true;
        }

        $class = 'FilterUtil_Filter_' . $name;
        $file  = 'filter.' . $name . '.class.php';

        // Load hierarchy
        $dest = array();
        if ($module != 'core' && ModUtil::available($module)) {
            $modinfo = ModUtil::getInfoFromName($module);
            $modpath = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
            $directory = $modinfo['directory'];
            $dest[] = "config/filter/$directory/$file";
            $dest[] = "$modpath/$directory/filter/$file";
        }
        $dest[] = "config/filter/$file";
        foreach ($dest as $file) {
            if (is_readable($file)) {
                include_once $file;
                break;
            }
        }

        $config = array();
        $this->addCommon($config);
        $obj = new $class($config);

        $this->_plg[] = $obj;
        end($this->_plg);
        $key = key($this->_plg);
        $obj = &$this->_plg[$key];

        $obj->setID($key);
        $this->_registerPlugin($key);
        $this->_loaded["$module@$name"] = $key;

        return key(end($this->_plg));
    }

    /**
     * Register a plugin.
     *
     * Check what type the plugin is from and register it.
     *
     * @param int $k The Plugin's ID -> Key in the $this->_plg array.
     *
     * @return void
     */
    private function _registerPlugin($k)
    {
        $obj = &$this->_plg[$k];

        if ($obj instanceof FilterUtil_BuildInterface) {
            $ops = $obj->getOperators();

            if (isset($ops) && is_array($ops)) {
                foreach ($ops as $op => $fields) {
                    $flds = array();
                    foreach ($fields as $field) {
                        $flds[$field] = $k;
                    }
                    if (isset($this->_ops[$op]) && is_array($this->_ops[$op])) {
                        $this->_ops[$op] = array_merge($this->_ops[$op], $flds);
                    } else {
                        $this->_ops[$op] = $flds;
                    }
                }
            }
        }

        if ($obj instanceof FilterUtil_ReplaceInterface) {
            $this->_replaces[] = $k;
        }
    }

    /**
     * Get plugin's configuration object.
     *
     * FIXME What's about this function? $name is not unique!
     *
     * @param string $name Plugin's name.
     *
     * @return object Plugin's configuration object.
     */
    public function getPluginConfig($name)
    {
        if (!$this->PluginIsLoaded($name)) {
            return false;
        }

        return $this->_plg[$name]->getConfig();
    }

    /**
     * Checks if a plugin is loaded.
     *
     * FIXME What's about this function? $name is not unique!
     *
     * @param string $name Plugin's name.
     *
     * @return bool true if the plugin is loaded, false otherwise.
     */
    public function isLoaded($name)
    {
        if (isset($this->_loaded[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Runs replace plugins and return condition set.
     *
     * @param string $field Fieldname.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array condition set.
     */
    public function replace($field, $op, $value)
    {
        if (is_array($this->_replaces)) {
            foreach ($this->_replaces as $k) {
                $obj = &$this->_plg[$k];
                list($field, $op, $value) = $obj->replace($field, $op, $value);
            }
        }

        return array(
                     'field' => $field,
                     'op'    => $op,
                     'value' => $value
                    );
    }

    /**
     * Returns SQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Sql set.
     */
    public function getSQL($field, $op, $value)
    {
        if (!isset($this->_ops[$op]) || !is_array($this->_ops[$op])) {
            return '';
        } elseif (isset($this->_ops[$op][$field])) {
            return $this->_plg[$this->_ops[$op][$field]]->getSQL($field, $op, $value);
        } elseif (isset($this->_ops[$op]['-'])) {
            return $this->_plg[$this->_ops[$op]['-']]->getSQL($field, $op, $value);
        } else {
            return '';
        }
    }

    /**
     * Returns DQL code.
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Test value.
     *
     * @return array Doctrine Query where clause and parameters.
     */
    public function getDql($field, $op, $value)
    {
        if (!isset($this->_ops[$op]) || !is_array($this->_ops[$op])) {
            return '';
        } elseif (isset($this->_restrictions[$field]) && !in_array($op, $this->_restrictions[$field])) {
            return '';
        } elseif (isset($this->_ops[$op][$field])) {
            return $this->_plg[$this->_ops[$op][$field]]->getDql($field, $op, $value);
        } elseif (isset($this->_ops[$op]['-'])) {
            return $this->_plg[$this->_ops[$op]['-']]->getDql($field, $op, $value);
        } else {
            return '';
        }
    }
}
