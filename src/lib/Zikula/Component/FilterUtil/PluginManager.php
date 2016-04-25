<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Component\FilterUtil;

use Doctrine\ORM\Query\Expr\Base as BaseExpr;
use Zikula\Component\FilterUtil\Plugin\ComparePlugin;

/**
 * Plugin manager class.
 */
class PluginManager
{
    /**
     * Loaded plugins.
     *
     * @var AbstractPlugin[]
     */
    private $plugins = array();

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
     * @param array  $plugins
     * @param array  $restrictions
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
     * @param array $plugins Array of plugin information in form "plugin name => config array".
     */
    public function loadPlugins(array $plugins)
    {
        $default = false;

        foreach ($plugins as $v) {
            $default |= $this->loadPlugin($v);
        }

        if (!$default) {
            $this->loadPlugin(new ComparePlugin(null, array(), true));
        }
    }

    /**
     * Loads a single plugin.
     *
     * @param AbstractPlugin $plugin
     *
     * @internal param string $name Plugin name.
     * @internal param array $config Plugin config.
     *
     * @return boolean true if the plugin is the default plugin.
     */
    public function loadPlugin(AbstractPlugin $plugin)
    {
        $this->plugins[] = $plugin;
        end($this->plugins);
        $key = key($this->plugins);
        $plugin->setID($key);
        $plugin->setConfig($this->config);
        $this->registerPlugin($key);

        return $plugin->isDefault();
    }

    /**
     * Register a plugin.
     *
     * Check what type the plugin is from and register it.
     *
     * @param int $k The Plugin ID -> Key in the $this->plugin array.
     */
    private function registerPlugin($k)
    {
        $plugin = &$this->plugins[$k];
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
     * @param array $restrictions Array of allowed operators per field in the form
     *                            field's name => operator array.
     */
    public function loadRestrictions(array $restrictions)
    {
        foreach ($restrictions as $field => $ops) {
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
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @return array condition set.
     */
    public function replace($field, $op, $value)
    {
        if (is_array($this->replaces)) {
            foreach ($this->replaces as $k) {
                $plugin = $this->plugins[$k];
                list($field, $op, $value) = $plugin->replace($field, $op, $value);
            }
        }

        return array(
            'field' => $field,
            'op' => $op,
            'value' => $value
        );
    }

    /**
     * Get the Doctrine expression object
     *
     * @param string $field Field name.
     * @param string $op    Operator.
     * @param string $value Value.
     *
     * @throws \InvalidArgumentException
     *
     * @return string|BaseExpr Doctrine expression or empty string
     */
    public function getExprObj($field, $op, $value)
    {
        if (!isset($this->ops[$op]) || !is_array($this->ops[$op])) {
            throw new \InvalidArgumentException('Unknown Operator.');
        }

        if (isset($this->restrictions[$field]) && !in_array($op, $this->restrictions[$field])) {
            throw new \InvalidArgumentException('This Operation is not allowed on this Field.');
        }

        if (isset($this->ops[$op][$field])) {
            return $this->plugins[$this->ops[$op][$field]]->getExprObj($field, $op, $value);
        }

        if (isset($this->ops[$op]['-'])) {
            return $this->plugins[$this->ops[$op]['-']]->getExprObj($field, $op, $value);
        }

        // @todo should this throw an exception if we get to here?
        return '';
    }
}
