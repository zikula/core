<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula\Core\FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil;

use Doctrine\ORM\Query\Expr\Func;
use Zikula\Core\FilterUtil\Plugin\Compare;

/**
 * Plugin manager class.
 */
class PluginManager
{
    /**
     * Loaded plugins.
     *
     * @var array
     */
    private $plugin = array();

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
     * Specified restrictions.
     *
     * @var array
     */
    private $restrictions = array();

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config FilterUtil Configuration object.
     * @param        $plugins
     * @param        $restrictions
     *
     * @internal param array $args Plugins to load in form "plugin name => Plugin Object".
     */
    public function __construct(Config $config, array $plugins = array(), array $restrictions = array())
    {
        $this->config = $config;
        $this->loadPlugins($plugins);
        $this->loadRestrictions($restrictions);
    }

    /**
     * Get configuration.
     *
     * @return Config Configuration object.
     */
    public function getConfig()
    {
        return $this->config;
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
        $default = false;

        foreach ($plgs as $v) {
            $default |= $this->loadPlugin($v);
        }
        if (!$default) {
            $this->loadPlugin(new Compare(null));
        }
    }

    /**
     * Loads a single plugin.
     *
     * @param AbstractPlugin $plugin
     *
     * @internal param string $name Plugin's name.
     * @internal param array $config Plugin's config.
     *
     * @return boolean true if the plugin is the default plugin.
     */
    public function loadPlugin(AbstractPlugin $plugin)
    {
        $this->plugin[] = $plugin;
        end($this->plugin);
        $key = key($this->plugin);
        $plugin->setID($key);
        $plugin->initPlugin($this->config);
        $this->registerPlugin($key);

        return $plugin->getDefault();
    }

    /**
     * Register a plugin.
     *
     * Check what type the plugin is from and register it.
     *
     * @param int $k The Plugin's ID -> Key in the $this->plugin array.
     *
     * @return void
     */
    private function registerPlugin($k)
    {
        $plugin = & $this->plugin[$k];
        if ($plugin instanceof JoinInterface) {
            $plugin->addJoinsToQuery();
        }

        if ($plugin instanceof BuildInterface) {
            $ops = $plugin->getOperators();
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

        if ($plugin instanceof ReplaceInterface) {
            $this->replaces[] = $k;
        }
    }

    /**
     * Loads restrictions.
     *
     * @param array $rest Array of allowed operators per field in the form "field's name => operator
     *                    array".
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
            $ops = array_filter(array_intersect((array) $ops, array_keys($this->ops)));
            if (!empty($ops)) {
                $this->restrictions[$field] = $ops;
            }
        }
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
                $plugin = & $this->plugin[$k];
                list ($field, $op, $value) = $plugin->replace($field, $op, $value);
            }
        }

        return array(
            'field' => $field,
            'op' => $op,
            'value' => $value
        );
    }

    /**
     * Get the Doctrine2 expression object
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return Func Doctrine2 expression
     */
    public function getExprObj($field, $op, $value)
    {
        if (!isset($this->ops[$op]) || !is_array($this->ops[$op])) {
            throw new \Exception('Unknown Operator.');
        }
        if (isset($this->restrictions[$field]) && !in_array($op, $this->restrictions[$field])) {
            throw new \Exception('This Operation is not allowd on this Field.');
        }
        if (isset($this->ops[$op][$field])) {
            return $this->plugin[$this->ops[$op][$field]]->getExprObj($field, $op, $value);
        }
        if (isset($this->ops[$op]['-'])) {
            return $this->plugin[$this->ops[$op]['-']]->getExprObj($field, $op, $value);
        }

        return '';
    }
}
