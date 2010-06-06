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

// backwards compatibility references
$GLOBALS['PNConfig'] = & $GLOBALS['ZConfig'];
$GLOBALS['PNRuntime'] = & $GLOBALS['ZRuntime'];

// start BC classes licensed as LGPv2.1

define('_MARKER_NONE',                '&nbsp;&nbsp;');
define('_REQUIRED_MARKER',            '<span style="font-size:larger;color:blue"><b>*</b></span>');
define('_VALIDATION_MARKER',          '<span style="font-size:larger;color:red"><b>!</b></span>');

/**
 * Alias to the Renderer class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see Renderer::
 */
class pnRender extends Renderer
{
    /**
     * Constructs a new instance of pnRender.
     *
     * @param string $module  Name of the module.
     * @param bool   $caching If true, then caching is enabled.
     */
    public function __construct($module = '', $caching = null)
    {
        parent::__construct($module, $caching);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Renderer')), 'STRICT');
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
    /**
     * Constructor, init everything to sane defaults and handle parameters.
     *
     * @param object|string $init   Initialization value (see {@link DBObject::_init()} for details).
     * @param mixed         $key    The DB key to use to retrieve the object (optional) (default=null)
     * @param string        $field  The field containing the key value (optional) (default=null)
     */
    public function PNObject($init = null, $key = null, $field = null)
    {
        $this->DBObject($init, $key, $field);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'DBObject')), 'STRICT');
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
    /**
     * Constructor, init everything to sane defaults and handle parameters.
     *
     * @param object|string $init   Initialization value (see _init() for details)
     * @param string        $where  The where clause to apply to the DB get/select (optional) (default='')
     */
    public function PNObjectArray($init = null, $where = '')
    {
        $this->DBObjectArray($init, $where);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'DBObjectArray')), 'STRICT');
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
 * Alias to the Form_Render class for backward compatibility to Zikula 1.2.x.
 *
 * @deprecated
 * @see Form_Render::
 */
class pnFormRender extends Form_Render
{
    /**
     * Alias to Form_Render::State for backward compatibility to Zikula 1.2.x.
     *
     * @internal
     * @deprecated
     * @see Form_Render::State
     */
    public $pnFormState;

    /**
     * List of included files required to recreate plugins (Smarty function.xxx.php files).
     *
     * @internal
     * @deprecated
     * @see Form_Render::Includes
     */
    public $pnFormIncludes;

    /**
     * List of instantiated plugins.
     *
     * @internal
     * @deprecated
     * @see Form_Render::Plugins
     */
    public $pnFormPlugins;

    /**
     * Stack with all instantiated blocks (push when starting block, pop when ending block).
     *
     * @internal
     * @deprecated
     * @see Form_Render::BlockStack
     */
    public $pnFormBlockStack;

    /**
     * List of validators on page.
     *
     * @internal
     * @deprecated
     * @see Form_Render::Validators
     */
    public $pnFormValidators;

    /**
     * Flag indicating if validation has been done or not.
     *
     * @internal
     * @deprecated
     * @see Form_Render::ValidationChecked
     */
    public $pnFormValidationChecked;

    /**
     * Indicates whether page is valid or not.
     *
     * @internal
     * @deprecated
     * @see Form_Render::_IsValid
     */
    public $_pnFormIsValid;

    /**
     * Current ID count - used to assign automatic ID's to all items.
     *
     * @internal
     * @deprecated
     * @see Form_Render::IdCount
     */
    public $pnFormIdCount;

    /**
     * Reference to the main user code event handler.
     *
     * @internal
     * @deprecated
     * @see Form_Render::EventHandler
     */
    public $pnFormEventHandler;

    /**
     * Error message has been set.
     *
     * @internal
     * @deprecated
     * @see Form_Render::ErrorMsgSet
     */
    public $pnFormErrorMsgSet;

    /**
     * Set to true if pnFormRedirect was called. Means no HTML output should be returned.
     *
     * @internal
     * @deprecated
     * @see Form_Render::Redirected
     */
    public $pnFormRedirected;

    /**
     * Constructs a new instance of pnFormRender.
     *
     * @deprecated
     * @see Form_Render::__construct()
     */
    public function __construct($module)
    {
        parent::__construct($module);

        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ , 'Form_Render')), 'STRICT');

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
     * Alias to Form_Render::execute for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::execute
     *
     * @param boolean       $template     Name of template file.
     * @param pnFormHandler $eventHandler Instance of object that inherits from pnFormHandler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function pnFormExecute($template, &$eventHandler)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::execute')), 'STRICT');
        return $this->execute($template, $eventHandler);
    }

    /**
     * Alias to Form_Render::registerPlugin for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::registerPlugin
     *
     * @param string  $pluginName Full class name of the plugin to register.
     * @param array   &$params    Parameters passed from the Smarty plugin function.
     * @param boolean $isBlock    Indicates whether the plugin is a Smarty block or a Smarty function (internal).
     *
     * @return string Returns what the render() method of the plugin returns.
     */
    public function pnFormRegisterPlugin($pluginName, &$params, $isBlock = false)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::registerPlugin')), 'STRICT');
        return $this->registerPlugin($pluginName, $params, $isBlock = false);
    }

    /**
     * Alias to Form_Render::registerBlock for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::registerBlock
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     */
    public function pnFormRegisterBlock($pluginName, &$params, &$content)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::registerBlock')), 'STRICT');
        return $this->registerBlock($pluginName, $params, $content);
    }

    /**
     * Alias to Form_Render::registerBlockBegin for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Form_Render::registerBlockBegin
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     */
    public function pnFormRegisterBlockBegin($pluginName, &$params)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::registerBlockBegin')), 'STRICT');
        $this->registerBlockBegin($pluginName, $params);
    }

    /**
     * Alias to Form_Render::registerBlockEnd for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Form_Render::registerBlockEnd
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     *
     * @return string Rendered output.
     */
    public function pnFormRegisterBlockEnd($pluginName, &$params, $content)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::registerBlockEnd')), 'STRICT');
        return $this->registerBlockEnd($pluginName, $params, $content);
    }

    /**
     * Alias to Form_Render::getPluginId for backward compatibility.
     *
     * @internal
     * @deprecated
     * @see Form_Render::getPluginId
     *
     * @param array  &$params    Parameters passed from the Smarty block function.
     *
     * @return mixed The contents of $params['id'] if set, else the value 'plg#' where # is the IdCount incremented by one
     */
    public function pnFormGetPluginId(&$params)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getPluginId')), 'STRICT');
        return $this->getPluginId($params);
    }

    /**
     * Alias to Form_Render::isPostBack for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::isPostBack
     *
     * @return bool True if $_POST['__pnFormSTATE'] is set; otherwise false.
     */
    public function pnFormIsPostBack()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::isPostBack')), 'STRICT');
        return $this->isPostBack();
    }

    /**
     * Alias to Form_Render::formDie for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::formDie
     *
     * @param string $msg The message to display.
     */
    public function pnFormDie($msg)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::formDie')), 'STRICT');
        $this->formDie($msg);
    }

    /**
     * Alias to Form_Render::translateForDisplay for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::translateForDisplay
     *
     * @param string  $txt      Text to translate for display.
     * @param boolean $doEncode True to formatForDisplay.
     *
     * @return string Text.
     */
    public function pnFormTranslateForDisplay($txt, $doEncode = true)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::translateForDisplay')), 'STRICT');
        return $this->translateForDisplay($txt, $doEncode = true);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::addValidator
     *
     * @param validator $validator Validator to add.
     */
    public function pnFormAddValidator(&$validator)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::addValidator')), 'STRICT');
        $this->addValidator($validator);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::isValid
     *
     * @return boolean True if all validators are valid.
     */
    public function pnFormIsValid()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::isValid')), 'STRICT');
        return $this->isValid();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::validate
     */
    public function pnFormValidate()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::validate')), 'STRICT');
        $this->validate();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::clearValidation
     */
    public function pnFormClearValidation()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::clearValidation')), 'STRICT');
        $this->clearValidation();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::setState
     */
    public function pnFormSetState($region, $varName, &$varValue)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::setState')), 'STRICT');
        $this->setState($region, $varName, $varValue);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::setErrorMsg
     */
    public function pnFormSetErrorMsg($msg)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::setErrorMsg')), 'STRICT');
        return $this->setErrorMsg($msg);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getErrorMsg
     */
    public function pnFormGetErrorMsg()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getErrorMsg')), 'STRICT');
        return $this->getErrorMsg();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::hasError
     */
    public function pnFormHasError()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::hasError')), 'STRICT');
        return $this->hasError();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::registerError
     */
    public function pnFormRegisterError($dummy)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::registerError')), 'STRICT');
        return $this->registerError($dummy);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::redirect
     */
    public function pnFormRedirect($url)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::redirect')), 'STRICT');
        $this->redirect($url);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getPostBackEventReference
     */
    public function pnFormGetPostBackEventReference($plugin, $commandName)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getPostBackEventReference')), 'STRICT');
        return $this->getPostBackEventReference($plugin, $commandName);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::raiseEvent
     */
    public function pnFormRaiseEvent($eventHandlerName, $args)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::raiseEvent')), 'STRICT');
        return $this->raiseEvent($eventHandlerName, $args);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::initializeIncludes
     */
    public function pnFormInitializeIncludes()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::initializeIncludes')), 'STRICT');
        $this->initializeIncludes();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getIncludesText
     */
    public function pnFormGetIncludesText()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getIncludesText')), 'STRICT');
        return $this->getIncludesText();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getIncludesHTML
     */
    public function pnFormGetIncludesHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getIncludesHTML')), 'STRICT');
        return $this->getIncludesHTML();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodeIncludes
     */
    public function pnFormDecodeIncludes()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodeIncludes')), 'STRICT');
        return $this->decodeIncludes();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getAuthKeyHTML
     */
    public function pnFormGetAuthKeyHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getAuthKeyHTML')), 'STRICT');
        return $this->getAuthKeyHTML();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::initializeState
     */
    public function pnFormInitializeState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::initializeState')), 'STRICT');
        $this->initializeState();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getStateText
     */
    public function pnFormGetStateText()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getStateText')), 'STRICT');
        $this->getStateText();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getPluginState
     */
    public function pnFormGetPluginState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getPluginState')), 'STRICT');
        return $this->getPluginState();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getPluginById
     */
    function &pnFormGetPluginById($id)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getPluginById')), 'STRICT');
        return $this->getPluginById($id);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getPluginState_rec
     */
    public function pnFormGetPluginState_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getPluginState_rec')), 'STRICT');
        return $this->getPluginState_rec($plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getStateHTML
     */
    public function pnFormGetStateHTML()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getStateHTML')), 'STRICT');
        return $this->getStateHTML();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodeState
     */
    public function pnFormDecodeState()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodeState')), 'STRICT');
        $this->decodeState();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodeEventHandler
     */
    public function pnFormDecodeEventHandler()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodeEventHandler')), 'STRICT');
        $this->decodeEventHandler();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::initializePlugins
     */
    public function pnFormInitializePlugins()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::initializePlugins')), 'STRICT');
        return $this->initializePlugins();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::initializePlugins_rec
     */
    public function pnFormInitializePlugins_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::initializePlugins_rec')), 'STRICT');
        $this->initializePlugins_rec($plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodePlugins
     */
    public function pnFormDecodePlugins()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodePlugins')), 'STRICT');
        return $this->decodePlugins();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodePlugins_rec
     */
    public function pnFormDecodePlugins_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodePlugins_rec')), 'STRICT');
        $this->decodePlugins_rec($plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodePostBackEvent
     */
    public function pnFormDecodePostBackEvent()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodePostBackEvent')), 'STRICT');
        $this->decodePostBackEvent();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::decodePostBackEvent_rec
     */
    public function pnFormDecodePostBackEvent_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::decodePostBackEvent_rec')), 'STRICT');
        return $this->decodePostBackEvent_rec($plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::postRender
     */
    public function pnFormPostRender()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::postRender')), 'STRICT');
        return $this->postRender();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::postRender_rec
     */
    public function pnFormPostRender_rec($plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::postRender_rec')), 'STRICT');
        $this->postRender_rec($plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getValues
     */
    public function pnFormGetValues()
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getValues')), 'STRICT');
        return $this->getValues();
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::getValues_rec
     */
    public function pnFormGetValues_rec($plugins, &$result)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::getValues_rec')), 'STRICT');
        $this->getValues_rec($plugins, $result);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::setValues
     */
    public function pnFormSetValues(&$values, $group = null)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::setValues')), 'STRICT');
        return $this->setValues($values, $group);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::setValues2
     */
    public function pnFormSetValues2(&$values, $group = null, $plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::setValues2')), 'STRICT');
        return $this->setValues2($values, $group, $plugins);
    }

    /**
     * Alias to equivalent function in Form_Render for backward compatibility.
     *
     * @deprecated
     * @see Form_Render::setValues_rec
     */
    public function pnFormSetValues_rec(&$values, $group, $plugins)
    {
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'Form_Render::setValues_rec')), 'STRICT');
        $this->setValues_rec($values, $group, $plugins);
    }
}

/**
 * Alias to the Form_Plugin class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin::
 */
class pnFormPlugin extends Form_Plugin
{
    /**
     * Alias to Form_Plugin constructor.
     * 
     * @deprecated
     * @see Form_Plugin::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin')), 'STRICT');
    }
}

/**
 * Alias to the Form_StyledPlugin class for backward compatibility.
 *
 * @deprecated
 * @see Form_StyledPlugin::
 */
class pnFormStyledPlugin extends Form_StyledPlugin
{
    /**
     * Alias to Form_StyledPlugin constructor.
     *
     * @deprecated
     * @see Form_StyledPlugin::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_StyledPlugin')), 'STRICT');
    }
}

/**
 * Alias to the Form_Handler class for backward compatibility.
 *
 * @deprecated
 * @see Form_Handler::
 */
class pnFormHandler extends Form_Handler
{
    /**
     * Alias to Form_Handler constructor.
     *
     * @deprecated
     * @see Form_Handler::__construct()
     */
    public function __construct()
    {
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Handler')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_BaseListSelector class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_BaseListSelector::
 */
class pnFormBaseListSelector extends Form_Plugin_BaseListSelector
{
    /**
     * Alias to Form_Plugin_BaseListSelector constructor.
     *
     * @deprecated
     * @see Form_Plugin_BaseListSelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_BaseListSelector')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_Button class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_Button::
 */
class pnFormButton extends Form_Plugin_Button
{
    /**
     * Alias to Form_Plugin_Button constructor.
     *
     * @deprecated
     * @see Form_Plugin_Button::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_Button')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_CategoryCheckboxList class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_CategoryCheckboxList::
 */
class pnFormCategoryCheckboxList extends Form_Plugin_CategoryCheckboxList
{
    /**
     * Alias to Form_Plugin_CategoryCheckboxList constructor.
     *
     * @deprecated
     * @see Form_Plugin_CategoryCheckboxList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_CategoryCheckboxList')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_CategorySelector class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_CategorySelector::
 */
class pnFormCategorySelector extends Form_Plugin_CategorySelector
{
    /**
     * Alias to Form_Plugin_CategorySelector constructor.
     *
     * @deprecated
     * @see Form_Plugin_CategorySelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_CategorySelector')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_Checkbox class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_Checkbox::
 */
class pnFormCheckbox extends Form_Plugin_Checkbox
{
    /**
     * Alias to Form_Plugin_Checkbox constructor.
     *
     * @deprecated
     * @see Form_Plugin_Checkbox::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_Checkbox')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_CheckboxList class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_CheckboxList::
 */
class pnFormCheckboxList extends Form_Plugin_CheckboxList
{
    /**
     * Alias to Form_Plugin_CheckboxList constructor.
     *
     * @deprecated
     * @see Form_Plugin_CheckboxList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_CheckboxList')), 'STRICT');
    }
}

/**
 * Alias to the Form_Block_ContextMenu class for backward compatibility.
 *
 * @deprecated
 * @see Form_Block_ContextMenu::
 */
class pnFormContextMenu extends Form_Block_ContextMenu
{
    /**
     * Alias to Form_Block_ContextMenu constructor.
     *
     * @deprecated
     * @see Form_Block_ContextMenu::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Block_ContextMenu')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ContextMenu_Item class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ContextMenu_Item::
 */
class pnFormContextMenuItem extends Form_Plugin_ContextMenu_Item
{
    /**
     * Alias to Form_Plugin_ContextMenu_Item constructor.
     *
     * @deprecated
     * @see Form_Plugin_ContextMenu_Item::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ContextMenu_Item')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ContextMenu_Reference class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ContextMenu_Reference::
 */
class pnFormContextMenuReference extends Form_Plugin_ContextMenu_Reference
{
    /**
     * Alias to Form_Plugin_ContextMenu_Reference constructor.
     *
     * @deprecated
     * @see Form_Plugin_ContextMenu_Reference::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ContextMenu_Reference')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ContextMenu_Separator class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ContextMenu_Separator::
 */
class pnFormContextMenuSeparator extends Form_Plugin_ContextMenu_Separator
{
    /**
     * Alias to Form_Plugin_ContextMenu_Separator constructor.
     *
     * @deprecated
     * @see Form_Plugin_ContextMenu_Separator::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ContextMenu_Separator')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_DateInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_DateInput::
 */
class pnFormDateInput extends Form_Plugin_DateInput
{
    /**
     * Alias to Form_Plugin_DateInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_DateInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_DateInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_DropdownRelationList class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_DropdownRelationList::
 */
class pnFormDropDownRelationlist extends Form_Plugin_DropdownRelationList
{
    /**
     * Alias to Form_Plugin_DropdownRelationList constructor.
     *
     * @deprecated
     * @see Form_Plugin_DropdownRelationList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_DropdownRelationList')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_DropdownList class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_DropdownList::
 */
class pnFormDropdownList extends Form_Plugin_DropdownList
{
    /**
     * Alias to Form_Plugin_DropdownList constructor.
     *
     * @deprecated
     * @see Form_Plugin_DropdownList::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_DropdownList')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_EmailInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_EmailInput::
 */
class pnFormEMailInput extends Form_Plugin_EmailInput
{
    /**
     * Alias to Form_Plugin_EmailInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_EmailInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_EmailInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ErrorMessage class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ErrorMessage::
 */
class pnFormErrorMessage extends Form_Plugin_ErrorMessage
{
    /**
     * Alias to Form_Plugin constructor.
     *
     * @deprecated
     * @see Form_Plugin_ErrorMessage::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ErrorMessage')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_FloatInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_FloatInput::
 */
class pnFormFloatInput extends Form_Plugin_FloatInput
{
    /**
     * Alias to Form_Plugin_FloatInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_FloatInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_FloatInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ImageButton class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ImageButton::
 */
class pnFormImageButton extends Form_Plugin_ImageButton
{
    /**
     * Alias to Form_Plugin_ImageButton constructor.
     *
     * @deprecated
     * @see Form_Plugin_ImageButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ImageButton')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_IntInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_IntInput::
 */
class pnFormIntInput extends Form_Plugin_IntInput
{
    /**
     * Alias to Form_Plugin_IntInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_IntInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_IntInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_Label class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_Label::
 */
class pnFormLabel extends Form_Plugin_Label
{
    /**
     * Alias to Form_Plugin_Label constructor.
     *
     * @deprecated
     * @see Form_Plugin_Label::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_Label')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_LanguageSelector class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_LanguageSelector::
 */
class pnFormLanguageSelector extends Form_Plugin_LanguageSelector
{
    /**
     * Alias to Form_Plugin_LanguageSelector constructor.
     *
     * @deprecated
     * @see Form_Plugin_LanguageSelector::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_LanguageSelector')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_LinkButton class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_LinkButton::
 */
class pnFormLinkButton extends Form_Plugin_LinkButton
{
    /**
     * Alias to Form_Plugin_LinkButton constructor.
     *
     * @deprecated
     * @see Form_Plugin_LinkButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_LinkButton')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_PostbackFunction class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_PostbackFunction::
 */
class pnFormPostBackFunction extends Form_Plugin_PostbackFunction
{
    /**
     * Alias to Form_Plugin_PostbackFunction constructor.
     *
     * @deprecated
     * @see Form_Plugin_PostbackFunction::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_PostbackFunction')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_RadioButton class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_RadioButton::
 */
class pnFormRadioButton extends Form_Plugin_RadioButton
{
    /**
     * Alias to Form_Plugin_RadioButton constructor.
     *
     * @deprecated
     * @see Form_Plugin_RadioButton::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_RadioButton')), 'STRICT');
    }
}

/**
 * Alias to the Form_Block_TabbedPanel class for backward compatibility.
 *
 * @deprecated
 * @see Form_Block_TabbedPanel::
 */
class pnFormTabbedPanel extends Form_Block_TabbedPanel
{
    /**
     * Alias to Form_Block_TabbedPanel constructor.
     *
     * @deprecated
     * @see Form_Block_TabbedPanel::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Block_TabbedPanel')), 'STRICT');
    }
}

/**
 * Alias to the Form_Block_TabbedPanelSet class for backward compatibility.
 *
 * @deprecated
 * @see Form_Block_TabbedPanelSet::
 */
class pnFormTabbedPanelSet extends Form_Block_TabbedPanelSet
{
    /**
     * Alias to Form_Block_TabbedPanelSet constructor.
     *
     * @deprecated
     * @see Form_Block_TabbedPanelSet::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Block_TabbedPanelSet')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_TextInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_TextInput::
 */
class pnFormTextInput extends Form_Plugin_TextInput
{
    /**
     * Alias to Form_Plugin_TextInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_TextInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_TextInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_UrlInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_UrlInput::
 */
class pnFormURLInput extends Form_Plugin_UrlInput
{
    /**
     * Alias to Form_Plugin_UrlInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_UrlInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_UrlInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_UploadInput class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_UploadInput::
 */
class pnFormUploadInput extends Form_Plugin_UploadInput
{
    /**
     * Alias to Form_Plugin_UploadInput constructor.
     *
     * @deprecated
     * @see Form_Plugin_UploadInput::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_UploadInput')), 'STRICT');
    }
}

/**
 * Alias to the Form_Plugin_ValidationSummary class for backward compatibility.
 *
 * @deprecated
 * @see Form_Plugin_ValidationSummary::
 */
class pnFormValidationSummary extends Form_Plugin_ValidationSummary
{
    /**
     * Alias to Form_Plugin_ValidationSummary constructor.
     *
     * @deprecated
     * @see Form_Plugin_ValidationSummary::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Plugin_ValidationSummary')), 'STRICT');
    }
}

/**
 * Alias to the Form_Block_Volatile class for backward compatibility.
 *
 * @deprecated
 * @see Form_Block_Volatile::
 */
class pnFormVolatile extends Form_Block_Volatile
{
    /**
     * Alias to Form_Block_Volatile constructor.
     *
     * @deprecated
     * @see Form_Block_Volatile::__construct()
     */
    public function __construct(&$render, &$params)
    {
        parent::__construct($render, $params);
        LogUtil::log(__f('Warning! Class %1$s is deprecated. Please use %2$s instead.', array(__CLASS__, 'Form_Block_Volatile')), 'STRICT');
    }
}

// end BC classes


/**
 * @deprecated since 1.2
 * we now directly analyse the 2-digit language and country codes
 * Language list for auto detection of browser language
 */
function cnvlanguagelist()
{
    // sprintf() is deliberate here, do not change - drak.
    LogUtil::log(sprintf('Warning! Function %1$s is deprecated.', array(__FUNCTION__)), 'STRICT');

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
        'FormUtil::getPassedValue()')), 'STRICT');

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
        'version_compare()')), 'STRICT');
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
        'SecurityUtil::checkPermission()')), 'STRICT');

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
        'SecurityUtil::getAuthInfo()')), 'STRICT');

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
        'SecurityUtil::getSecurityLevel()')), 'STRICT');

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
        'SecurityUtil::generateAuthKey()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'SecurityUtil::confirmAuthKey()')), 'STRICT');

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
        'SecurityUtil::checkPermission()')), 'STRICT');
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
        'SecurityUtil::registerPermissionSchema()')), 'STRICT');

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
        'SecurityUtil::registerPermissionSchema()')), 'STRICT');
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
        'SecurityUtil::accesslevelname()')), 'STRICT');
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
        'SecurityUtil::accesslevelnames()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'GetUserTime'), 'STRICT');
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
        'LogUtil::getStatusMessages()')), 'STRICT');
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
        'DataUtil::formatForOS()')), 'STRICT');

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
        'DataUtil::formatForDisplay()')), 'STRICT');

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
        'DataUtil::formatForDisplayHTML()')), 'STRICT');

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
        'DataUtil::formatForStore()')), 'STRICT');

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
            'z_exit()')), 'STRICT');
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
        LogUtil::log(__f('Warning! Function %1$s is deprecated.', 'pn_assert_callback_function()', 'STRICT'));
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
        'SessionUtil::getVar()')), 'STRICT');
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
        'SessionUtil::setVar()')), 'STRICT');
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
        'SessionUtil::delVar()')), 'STRICT');
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

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_compiled', 'Theme::clear_compiled()')), 'STRICT');
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

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_clear_cache', 'Theme::clear_all_cache()')), 'STRICT');
    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_all_cache();
    return $res;
}

/**
 * Clear render compiled templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Renderer::clear_compiled()
 */
function theme_userapi_render_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_compiled', 'Renderer::clear_compiled()')), 'STRICT');
    $Renderer = Renderer::getInstance();
    $res      = $Renderer->clear_compiled();
    return $res;
}

/**
 * Clear render cached templates
 *
 * removed since version 1.3.0 of Zikula
 * @deprecated
 * @see Renderer::clear_cache()
 * @param module the module where to clear the cache, emptys = clear all caches
 * @return true or false
 */
function theme_userapi_render_clear_cache($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('theme_userapi_render_clear_cache', 'Renderer::clear_cache()')), 'STRICT');
    if(isset($args['module']) && !empty($args['module']) && pnModAvailable($args['module'])) {
        $Renderer = Renderer::getInstance($args['module']);
        $res      = $Renderer->clear_cache();
    } else {
        $Renderer = Renderer::getInstance();
        $res      = $Renderer->clear_all_cache();
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::hasVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::setVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::setVars()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::delVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getIdFromName()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getInfo()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getUserMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getProfileMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getMessageMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getAdminMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getTypeMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getAllMods()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::dbInfoLoad()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::load()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::loadApi()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::loadGeneric()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::func()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::apiFunc()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::exec()')), 'STRICT');
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
 * @param boolean      $forcelang    Forcelang.
 *
 * @return sting Absolute URL for call
 */
function pnModURL($modname, $type = 'user', $func = 'main', $args = array(), $ssl = null, $fragment = null, $fqurl = null, $forcelongurl = false, $forcelang=false)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::url()')), 'STRICT');
    return ModUtil::url($modname, $type, $func, $args, $ssl, $fragment, $fqurl, $forcelang, $forcelang);
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::available()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getName()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::registerHook()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::unregisterHook()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::callHooks()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::isHooked()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getBaseDir()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'ModUtil::getModsTable()')), 'STRICT');
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
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'ModUtil::getModules()')), 'STRICT');
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
        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__CLASS__ . '#' . __FUNCTION__, 'ModUtil::getModulesByState()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::displayPosition()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::show()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::themeBlock()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::load()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::loadAll()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::varsFromContent()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::varsToContent()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::checkUserBlock()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::getBlocksInfo()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::getBlockInfo()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::displayPosition()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'BlockUtil::themesideblock()')), 'STRICT');
    return BlockUtil::themesideblock($row);
}

// user

/**
 * Log the user in
 *
 * @deprecated
 * @see UserUtil::login()
 *
 * @param uname $ the name of the user logging in
 * @param pass $ the password of the user logging in
 * @param rememberme whether $ or not to remember this login
 * @param checkPassword bool true whether or not to check the password
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogIn($uname, $pass, $rememberme = false, $checkPassword = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::login()')), 'STRICT');
    return UserUtil::login($uname, $pass, $rememberme, $checkPassword);
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::loginHttp()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::logout()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::isLoggedIn()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getVars()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::setVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::setPassword()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::delVar()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getTheme()')), 'STRICT');
    return UserUtil::getTheme($force);
}

/**
 * get the user's language
 *
 * This function returns the deprecated 3 digit language codes, you need to switch APIs
 *
 * @deprecated
 * @see UserUtil::getLang()
 *
 * @return string the name of the user's language
 */
function pnUserGetLang()
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getLang()')), 'STRICT');
    return UserUtil::getLang();
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getAll()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getIdFromName()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::getIdFromEmail()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array(__FUNCTION__, 'UserUtil::fieldAlias()')), 'STRICT');
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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeLoad()', 'ThemeUtil::load()')), 'STRICT');

    return ThemeUtil::load($theme);
}

/**
 * return a theme variable
 *
 * @return mixed theme variable value
 */
function pnThemeGetVar($name = null, $default = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetVar()', 'ThemeUtil::getVar()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetAllThemes()', 'ThemeUtil::getAllThemes()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeLangLoad()', 'ThemeUtil::loadLanguage()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetIDFromName()', 'ThemeUtil::getIDFromName()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetInfo()', 'ThemeUtil::getInfo()')), 'STRICT');

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
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('pnThemeGetThemesTable()', 'ThemeUtil::getThemesTable()')), 'STRICT');

    return ThemeUtil::getThemesTable();
}

function search_construct_where($args, $fields, $mlfield = null)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('search_construct_where()', 'Search_Api_User::construct_where()')), 'STRICT');
    return Search_Api_User::construct_where($args, $fields, $mlfield);

}

function search_split_query($q, $dbwildcard = true)
{
    LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', array('search_split_query()', 'Search_Api_User::split_query()')), 'STRICT');
    return Search_Api_User::split_query($q, $dbwildcard);
}