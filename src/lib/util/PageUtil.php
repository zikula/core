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
 * Zikula page variables functions
 *
 * A <em>page variable</em> is an entity identified by a name that stores a value for the currently
 * rendered page. They are used to set for example the title of the page, the stylesheets used etc.
 * from the module.
 *
 * Page variables can be <em>single valued</em> or <em>multi valued</em>. In the first case, only
 * one single value can be set; each new setting will overwrite the old one. The title is an example
 * for a single values page variable (each page can have exactly one title). Multi valued variables
 * can contain more than one value, and new values can be added to the variable. An example of a multi
 * valued variable is stylesheet (a page can use more than one style sheet).
 *
 * Zikula offers a set of API functions to manipulate page variables.
 *
 * A module can register a new page variable by providing its metadata using the pnPageRegisterVar
 * function.
 *
 * Zikula doesn't impose any restriction on the page variabl's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>description</li>
 * <li>keywords</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>body</li>
 * <li>rawtext</li>
 * <li>footer</li>
 * </ul>
 */
class PageUtil
{
    /**
     * Register Var.
     *
     * Registers a new page variable.
     * Zikula doesn't impose any restriction on the page variabl's name except for duplicate
     * and reserved names. As of this writing, the list of reserved names consists of
     * <ul>
     * <li>title</li>
     * <li>keywords</li>
     * <li>stylesheet</li>
     * <li>javascript</li>
     * <li>body</li>
     * </ul>
     *
     * @param string  $varname    The name of the new page variable.
     * @param boolean $multivalue To define a single or a multi valued variable.
     * @param string  $default    To set the default value. This value is assigned to the variable at registration time.
     *
     * @return boolean success or not
     */
    public static function registerVar($varname, $multivalue = false, $default = null)
    {
        global $_pnPageVars;

        // check for $_pnPageVars sanity
        if (!isset($_pnPageVars)) {
            $_pnPageVars = array();
        } elseif (!is_array($_pnPageVars)) {
            return false;
        }

        // if already registered, stop
        if (isset($_pnPageVars[$varname])) {
            return false;
        }

        // define the page variable and it's default value
        $_pnPageVars[$varname] = compact('multivalue', 'default');

        // always make the default value the contents (even if it's null - that will be filtered away)
        self::resetVar($varname);

        return true;
    }

    /**
     * Reset Var.
     *
     * Resets the pge variable back to its default value.
     * All values assigned by addVar() or setVar()
     * will get lost.
     *
     * @param string $varname The name of the page variable.
     *
     * @return boolean true On success, false of the page variable is not registered.
     */
    public static function resetVar($varname)
    {
        global $_pnPageVars;

        // check for $_pnPageVars sanity
        if (!isset($_pnPageVars)) {
            $_pnPageVars = array();
        } elseif (!is_array($_pnPageVars)) {
            return false;
        }

        if (!isset($_pnPageVars[$varname])) {
            return false;
        }

        if ($_pnPageVars[$varname]['multivalue']) {
            if (empty($_pnPageVars[$varname]['default'])) {
                $_pnPageVars[$varname]['contents'] = array();
            } else {
                $_pnPageVars[$varname]['contents'] = array($_pnPageVars[$varname]['default']);
            }
        } else {
            if (empty($_pnPageVars[$varname]['default'])) {
                $_pnPageVars[$varname]['contents'] = null;
            } else {
                $_pnPageVars[$varname]['contents'] = $_pnPageVars[$varname]['default'];
            }
        }

        return true;
    }

    /**
     * GetVar.
     *
     * Returns the value(s) of a page variable. In the case of
     * a mulit valued variable, this is an array containing all assigned
     * values.
     *
     * @param string $varname The name of the page variable.
     * @param mixed  $default Default return value.
     *
     * @return mixed Contents of the variable
     */
    public static function getVar($varname, $default = null)
    {
        global $_pnPageVars;

        // check for $_pnPageVars sanity
        if (!isset($_pnPageVars)) {
            $_pnPageVars = array();
        } elseif (!is_array($_pnPageVars)) {
            return false;
        }

        if (isset($_pnPageVars[$varname]) && isset($_pnPageVars[$varname]['contents'])) {
            return $_pnPageVars[$varname]['contents'];
        } elseif (isset($_pnPageVars[$varname]['default'])) {
            return $_pnPageVars[$varname]['default'];
        }

        return $default;
    }

    /**
     * Set var.
     *
     * Sets the page variable to a new value. In the case of
     * a multi valued page variable, all previously added values
     * will get lost. If you want to add a value to a multi valued
     * page variable, use PageUtil::addVar.
     *
     * @param string $varname The name of the page variable.
     * @param mixed  $value   The new value.
     *
     * @see    PageUtil::addVar
     * @return boolean true On success, false of the page variable is not registered.
     *
     */
    public static function setVar($varname, $value)
    {
        global $_pnPageVars;

        // check for $_pnPageVars sanity
        if (!isset($_pnPageVars)) {
            $_pnPageVars = array();
        } elseif (!is_array($_pnPageVars)) {
            return false;
        }

        if (!isset($_pnPageVars[$varname])) {
            return false;
        }

        if ($_pnPageVars[$varname]['multivalue']) {
            $_pnPageVars[$varname]['contents'] = array($value);
        } else {
            $_pnPageVars[$varname]['contents'] = $value;
        }

        return true;
    }

    /**
     * Add var.
     *
     * Adds a new vaule to a page variable. In the case of a single
     * page variable, this functions acts exactly like PageUtil::setVar.
     *
     * @param string $varname The name of the page variable.
     * @param mixed  $value   The new value.
     *
     * @see    PageUtil::setVar
     * @return boolean true On success, false of the page variable is not registered.
     */
    public static function addVar($varname, $value)
    {
        global $_pnPageVars;

        // check for $_pnPageVars sanity
        if (!isset($_pnPageVars)) {
            $_pnPageVars = array();
        } elseif (!is_array($_pnPageVars)) {
            return false;
        }

        if ($varname == 'javascript') {
            // shorthand syntax for some common JS libraries
            $shortJsVars = array('behaviour', 'prototype', 'scriptaculous', 'validation');
            if (is_array($value)) {
                foreach (array_keys($value) as $k) {
                    if (in_array($value[$k], $shortJsVars)) {
                        $value[$k] = 'javascript/ajax/' . DataUtil::formatForOS($value[$k]) . '.js';
                    }
                }
            } elseif (in_array($value, $shortJsVars)) {
                $value = 'javascript/ajax/' . DataUtil::formatForOS($value) . '.js';
            }

            // check for customized javascripts
            if (is_array($value)) {
                foreach (array_keys($value) as $k) {
                    if (strpos($value[$k], 'system/') !== false || strpos($value[$k], 'modules/') !== false) {
                        $custom = str_replace(array('javascript/', 'pnjavascript/'), '', DataUtil::formatForOS($value[$k]));
                        $custom = str_replace(array('modules', 'system'), 'config/javascript', $custom);
                        if (file_exists($custom)) {
                            $value[$k] = $custom;
                        }
                    }
                }
            } elseif (strpos($value, 'system/') !== false || strpos($value, 'modules/') !== false) {
                $custom = str_replace(array('javascript/', 'pnjavascript/'), '', DataUtil::formatForOS($value));
                $custom = str_replace(array('modules', 'system'), 'config/javascript', $custom);
                if (file_exists($custom)) {
                    $value[$k] = $custom;
                }
            }
        }

        if (!isset($_pnPageVars[$varname])) {
            return false;
        }

        if ($_pnPageVars[$varname]['multivalue']) {
            if (is_array($value)) {
                $_pnPageVars[$varname]['contents'] = array_merge($_pnPageVars[$varname]['contents'], $value);
            } else {
                $_pnPageVars[$varname]['contents'][] = $value;
            }
            // make values unique
            $_pnPageVars[$varname]['contents'] = array_unique($_pnPageVars[$varname]['contents']);
        } else {
            $_pnPageVars[$varname]['contents'] = $value;
        }

        return true;
    }
}
