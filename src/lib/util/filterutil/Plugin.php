<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


class FilterUtil_Plugin extends FilterUtil_Common
{
    /**
     * Loaded plugins
     */
    private $plg;

    /**
     * Loaded operators
     */
    private $ops;

    /**
     * Loaded replaces
     */
    private $replaces;

    /**
     * Constructor
     *
     * @access public
     * @param array $config Configuration array
     * @param array $plgs Plugins to load in form "plugin name => config array"
     * @return object FilterUtil_Plugin object (optional) (default: null)
     */
    public function __construct($config = array(), $plgs = null)
    {
        parent::__construct($config);
        if ($plgs !== null && is_array($plgs) && count($plgs) > 0) {
            $ok = $this->loadPlugins($plgs);
        }
        return ($ok === false ? false : $this);
    }

    /**
     * Load plugins
     *
     * @access public
     * @param array $plgs Array of plugin informations in form "plugin's name => config array"
     * @return bool true on success, false otherwise
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
     * Load a single plugin
     *
     * @access public
     * @param string $name Plugin's name
     * @param array $config Plugin's config
     * @return bool True on success, false otherwise
     */
    public function loadPlugin($name, $config = array())
    {
        // TODO A: rewrite this loader
        if ($this->isLoaded($name)) {
            return true;
        }
        $module = $this->module;
        if (strpos($name, '@')) {
            list ($module, $name) = explode('@', $name, 2);
        }
        $class = 'FilterUtil_Filter_' . $name;
        $file = 'filter.' . $name . '.class.php';
        //Load hierarchy
        $dests = array();
        if ($module != 'core' && pnModAvailable($module)) {
            $modinfo = pnModGetInfo(pnModGetIDFromName($module));
            $directory = $modinfo['directory'];
            $dest[] = "config/filter/$directory/$file";
            $dest[] = "system/$directory/filter/$file";
            $dest[] = "modules/$directory/filter/$file";
        }

        $dest[] = "config/filter/$file";
        $dest[] = FILTERUTIL_CLASS_PATH . "/filter/$file";

        Loader::loadOneFile($dest);
        $this->addCommon($config);
        $obj = new $class($config);

        $this->plg[] = $obj;
        $obj = & end($this->plg);
        $obj->setID(key($this->plg));
        $this->registerPlugin(key($this->plg));

        return key(end($this->plg));
    }

    /**
     * Register a plugin
     *
     * Check what type the plugin is from and register it
     *
     * @param int $k The Plugin's ID -> Key in the $this->plg array
     * @return void
     * @access private
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
     * Get plugin's configuration object
     *
     * FIXME What's about this function? $name is not unique!
     *
     * @access public
     * @param string $name Plugin's name
     * @return object Plugin's configuration object
     */
    public function getConfig($name)
    {
        if (!$this->PluginIsLoaded($name)) {
            return false;
        }
        return $this->plg[$name]->getConfig();
    }

    /**
     * Check if a plugin is loaded
     *
     * FIXME What's about this function? $name is not unique!
     *
     * @access public
     * @param string $name Plugin's name
     * @return bool true if the plugin is loaded, false otherwise
     */
    public function isLoaded($name)
    {
        if (isset($this->plg[$name]) && is_a($this->plg[$name], 'FilterUtil_Filter_' . $name)) {
            return true;
        }
        return false;
    }

    /**
     * run replace plugins and return condition set
     *
     * @access public
     * @param string $field Fieldname
     * @param string $op Operator
     * @param string $value Value
     * @return array condition set
     */
    public function replace($field, $op, $value)
    {
        if (is_array($this->replaces)) {
            foreach ($this->replaces as $k) {
                $obj = & $this->plg[$k];
                list ($field, $op, $value) = $obj->replace($field, $op, $value);
            }
        }

        return array(
                        'field' => $field,
                        'op' => $op,
                        'value' => $value);
    }

    /**
     * return SQL code
     *
     * @access public
     * @param string $field Field name
     * @param string $op Operator
     * @param string $value Test value
     * @return array sql set
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