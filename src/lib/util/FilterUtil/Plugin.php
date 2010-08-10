<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Plugin manager class.
 */
class FilterUtil_Plugin extends FilterUtil_Common
{
    /**
     * Loaded plugins.
     *
     * @var array
     */
    private $plg = array();

    /**
     * Loaded plugins list.
     *
     * @var array;
     */
    private $loaded = array();

    /**
     * Loaded operators.
     *
     * @var array
     */
    private $ops;

    /**
     * Loaded replaces.
     *
     * @var array
     */
    private $replaces;

    /**
     * Constructor.
     *
     * @param array $config Configuration array.
     * @param array $plgs   Plugins to load in form "plugin name => config array".
     */
    public function __construct($config = array(), $plgs = null)
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
     * Loads a single plugin.
     *
     * @param string $name   Plugin's name.
     * @param array  $config Plugin's config.
     *
     * @return bool True on success, false otherwise.
     * @TODO Rewrite this loader
     */
    public function loadPlugin($name, $config = array())
    {
        $module = $this->module;
        if (strpos($name, '@')) {
            list ($module, $name) = explode('@', $name, 2);
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
        Loader::loadOneFile($dest);

        $config = array();
        $this->addCommon($config);
        $obj = new $class($config);

        $this->plg[] = $obj;
        end($this->plg);
        $key = key($this->plg);
        $obj = & $this->plg[$key];

        $obj->setID($key);
        $this->registerPlugin($key);
        $this->loaded["$module@$name"] = $key;

        return key(end($this->plg));
    }

    /**
     * Register a plugin.
     *
     * Check what type the plugin is from and register it.
     *
     * @param int $k The Plugin's ID -> Key in the $this->plg array.
     *
     * @return void
     */
    private function registerPlugin($k)
    {
        $obj = & $this->plg[$k];

        if ($obj instanceof FilterUtil_Build) {
            $ops = $obj->getOperators();
            if (isset($ops) && is_array($ops)) {
                foreach ($ops as $op => $fields) {
                    $flds = array();
                    foreach ($fields as $field) {
                        $flds[$field] = $k;
                    }
                    if (isset($this->ops[$op]) && is_array($this->ops[$op])) {
                        $this->ops[$op] = array_merge($this->ops[$op], $flds);
                    } else {
                        $this->ops[$op] = $flds;
                    }
                }
            }
        }

        if ($obj instanceof FilterUtil_Replace) {
            $this->replaces[] = $k;
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
    public function getConfig($name)
    {
        if (!$this->PluginIsLoaded($name)) {
            return false;
        }

        return $this->plg[$name]->getConfig();
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
        if (isset($this->loaded[$name])) {
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
        if (is_array($this->replaces)) {
            foreach ($this->replaces as $k) {
                $obj = & $this->plg[$k];
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
        if (!isset($this->ops[$op]) || !is_array($this->ops[$op])) {
            return '';
        } elseif (isset($this->ops[$op][$field])) {
            return $this->plg[$this->ops[$op][$field]]->getSQL($field, $op, $value);
        } elseif (isset($this->ops[$op]['-'])) {
            return $this->plg[$this->ops[$op]['-']]->getSQL($field, $op, $value);
        } else {
            return '';
        }
    }
}
