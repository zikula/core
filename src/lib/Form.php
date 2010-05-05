<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * User interaction handler for pnForm system.
 *
 * This class is the main entry point for using the pnForm system. It is expected to be used in Zikula's
 * user files, such as "pnuser.php", like this:
 * <code>
 * function modname_user_new($args)
 * {
 *   // Create instance of pnFormRender class
 *   $render = FormUtil::newpnForm('howtopnforms');
 *
 *   // Execute form using supplied template and event handler
 *   return $render->Execute('modname_user_new.html', new modname_user_newHandler());
 * }
 * </code>
 * See tutorials elsewhere for general introduction to pnForm.
 */
class Form extends Renderer
{
    /**
     * Variable saving all required state information.
     * 
     * @var array
     * @internal
     */
    public $State;

    /**
     * List of included files required to recreate plugins (Smarty function.xxx.php files).
     * 
     * @var array
     * @internal
     */
    public $Includes;

    /**
     * List of instantiated plugins.
     * 
     * @var array
     * @internal
     */
    public $Plugins;

    /**
     * Stack with all instantiated blocks (push when starting block, pop when ending block).
     * 
     * @var array
     * @internal
     */
    public $BlockStack;

    /**
     * List of validators on page.
     * 
     * @var array
     * @internal
     */
    public $Validators;

    /**
     * Flag indicating if validation has been done or not.
     * 
     * @var boolean
     * @internal
     */
    public $ValidationChecked;

    /**
     * Indicates whether page is valid or not.
     * 
     * @var boolean
     * @internal
     */
    public $_IsValid;

    /**
     * Current ID count - used to assign automatic ID's to all items.
     * 
     * @var intiger
     * @internal
     */
    public $IdCount;

    /**
     * Reference to the main user code event handler.
     * 
     * @var pnFormHandler
     * @internal
     */
    public $EventHandler;

    /**
     * Error message has been set.
     * 
     * @var boolean
     * @internal
     */
    public $ErrorMsgSet;

    /**
     * Set to true if pnFormRedirect was called. Means no HTML output should be returned.
     * 
     * @var boolean
     * @internal
     */
    public $Redirected;

    /**
     * Constructor.
     * 
     * Use FormUtil::newpnForm() instead of instantiating pnFormRender directly.
     * 
     * @internal
     */
    public function __construct($module)
    {
        // Construct and make normal Smarty use possible
        parent::__construct($module, false);
        array_push($this->plugins_dir, "lib/Form/renderplugins");

        // Setup
        $this->IdCount = 1;
        $this->ErrorMsgSet = false;
        $this->Plugins = array();
        $this->BlockStack = array();
        $this->Redirected = false;

        $this->Validators = array();
        $this->ValidationChecked = false;
        $this->_IsValid = null;

        $this->InitializeState();
        $this->InitializeIncludes();
    }

    /** Main event loop handler.
     *
     * This is the function to call instead of the normal $render->fetch(...).
     * 
     * @param boolean       $template     Name of template file.
     * @param pnFormHandler $eventHandler Instance of object that inherits from pnFormHandler.
     * 
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function Execute($template, &$eventHandler)
    {
        // Save handler for later use
        $this->EventHandler = &$eventHandler;

        if ($this->IsPostBack()) {
            if (!SecurityUtil::confirmAuthKey())
                return LogUtil::registerAuthidError();
            {
            }

            $this->DecodeIncludes();
            $this->DecodeState();
            $this->DecodeEventHandler();

            if ($eventHandler->initialize($this) === false) {
                return $this->GetErrorMsg();
            }

            // (no create event)
            $this->InitializePlugins(); // initialize event
            $this->DecodePlugins(); // decode event
            $this->DecodePostBackEvent(); // Execute optional postback after plugins have read their values
        } else {
            if ($eventHandler->initialize($this) === false)
                return $this->GetErrorMsg();
        }

        // render event (calls registerPlugin)
        $output = $this->fetch($template);

        if ($this->HasError()) {
            return $this->GetErrorMsg();
        }

        // Check redirection at this point, ignore any generated HTML if redirected is required.
        // We cannot skip HTML generation entirely in case of pnRedirect since there might be
        // some relevant code to execute in the plugins.
        if ($this->Redirected) {
            return true;
        }

        return $output;
    }

    /**
     * Register a plugin.
     *
     * This method registers a plugin used in a template. Plugins must beregistered to be used in pnForm
     * (unlike Smarty plugins). The register call must be done inside the Smarty plugin function in a
     * Smarty plugin file. Use like this:
     * <code>
     * // In file "function.myplugin.php"
     *
     * // pnForm plugin class
     * class MyPlugin extends pnFormPlugin
     * { ... }
     *
     * // Smarty plugin function
     * function smarty_function_myplugin($params, &$render)
     * {
     *   return $render->RegisterPlugin('MyPlugin', $params);
     * }
     * </code>
     * Registering a plugin ensures it is included in the plugin hierarchy of the current page, so that it's
     * various event handlers can be called by the framework.
     *
     * Do not use this function for registering Smarty blocks (the $isBlock parameter is for internal use).
     * Use pnFormRegisterBlock instead.
     *
     * See also all the function.formXXX.php plugins for examples.
     *
     * @param string  $pluginName Full class name of the plugin to register.
     * @param array   &$params    Parameters passed from the Smarty plugin function.
     * @param boolean $isBlock    Indicates whether the plugin is a Smarty block or a Smarty function (internal).
     * 
     * @return string Returns what the render() method of the plugin returns.
     */
    public function RegisterPlugin($pluginName, &$params, $isBlock = false)
    {
        // Make sure we have a suitable ID for the plugin
        $id = $this->GetPluginId($params);

        $stackCount = count($this->BlockStack);

        // A volatile block is a block that cannot be restored through view state
        // This is the case for pnForm plugins inside <!--[if]--> and <!--[foreach]--> tags.
        // So create new plugins for these blocks instead of relying on the existing plugins.


        if (!$this->IsPostBack() || $stackCount > 0 && $this->BlockStack[$stackCount - 1]->volatile) {
            $plugin = new $pluginName($this, $params);

            // Make sure to store ID and render reference in plugin
            $plugin->id = $id;

            if ($stackCount > 0) {
                $plugin->parentPlugin = &$this->BlockStack[$stackCount - 1];
                $this->BlockStack[$stackCount - 1]->registerPlugin($this, $plugin);
            } else {
                // Store plugin for later reference
                $this->Plugins[] = &$plugin;
            }

            // Copy parameters to member variables and attribute set
            $plugin->readParameters($this, $params);
            $plugin->create($this, $params);
            $plugin->load($this, $params);

            // Remember which file this plugin came from in order to be able to restore it.
            $pluginPath = str_replace(realpath(dirname(__FILE__) . '/..') . DIRECTORY_SEPARATOR, '', $plugin->getFilename());
            $this->Includes[$pluginPath] = 1;

        } else {
            // Fetch plugin instance by ID
            // It has already got it's initialize and decode event at this point
            $plugin = & $this->GetPluginById($id);

            // Kill existing plugins beneath a volatile block
            if (isset($plugin->volatile) && $plugin->volatile) {
                $plugin->plugins = null;
            }
        }

        $plugin->dataBound($this, $params);

        if ($isBlock) {
            $this->BlockStack[] = &$plugin;
        }

        // Ask plugin to render itself
        $output = '';
        if ($isBlock) {
            if ($plugin->visible) {
                $output = $plugin->renderBegin($this);
            }
        } else {
            if ($plugin->visible) {
                $output = $plugin->render($this);
            }
        }

        return $output;
    }

    /**
     * Regiser a block plugin.
     *
     * Use this like {@link pnFormRegisterPlugin} but for Smarty blocks instead of Smarty plugins.
     * <code>
     * // In file "block.myblock.php"
     *
     * // pnForm plugin class (also used for blocks)
     * class MyBlock extends pnFormPlugin
     * { ... }
     *
     * // Smarty block function
     * function smarty_block_myblock($params, $content, &$render)
     * {
     *   return return $render->RegisterBlock('MyBlock', $params, $content);
     * }
     * </code>
     * 
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     */
    public function RegisterBlock($pluginName, &$params, &$content)
    {
        if (!$content) {
            return $this->RegisterBlockBegin($pluginName, $params);
        } else {
            return $this->RegisterBlockEnd($pluginName, $params, $content);
        }
    }

    /**
     * pnFormRegisterBlockBegin.
     * 
     * @internal
     */
    public function RegisterBlockBegin($pluginName, &$params)
    {
        $output = $this->RegisterPlugin($pluginName, $params, true);
        $plugin = &$this->BlockStack[count($this->BlockStack) - 1];
        $plugin->blockBeginOutput = $output;
    }

    /**
     * pnFormRegisterBlockEnd.
     * 
     * @internal
     */
    public function RegisterBlockEnd($pluginName, &$params, $content)
    {
        $plugin = &$this->BlockStack[count($this->BlockStack) - 1];
        array_pop($this->BlockStack);

        if ($plugin->visible) {
            $output = $plugin->blockBeginOutput . $plugin->renderContent($this, $content) . $plugin->renderEnd($this);
        }

        $plugin->blockBeginOutput = null;

        return $output;
    }

    /**
     * pnFormGetPluginId.
     * 
     * @internal
     */
    public function GetPluginId(&$params)
    {
        if (!isset($params['id'])) {
            return 'plg' . ($this->IdCount++);
        }

        return $params['id'];
    }

    /**
     * GetPluginById.
     *
     * @param intiger $id Plugin ID.
     */
    public function &GetPluginById($id)
    {
        $lim = count($this->Plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $plugin = & $this->GetPluginById_rec($this->Plugins[$i], $id);
            if ($plugin != null) {
                return $plugin;
            }
        }

        $null = null;
        return $null;
    }

    /**
     * GetPluginById_rec.
     *
     * @param plugin? $plugin Plugin
     * @param intiger $id     Plugin ID.
     */
    public function &GetPluginById_rec(&$plugin, $id)
    {
        if ($plugin->id == $id) {
            return $plugin;
        }

        $lim = count($plugin->plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $subPlugin = & $this->GetPluginById_rec($plugin->plugins[$i], $id);
            if ($subPlugin != null) {
                return $subPlugin;
            }
        }

        $null = null;
        return $null;
    }

    /**
     * IsPostBack.
     *
     * @return boolean
     */
    public function IsPostBack()
    {
        return isset($_POST['__pnFormSTATE']);
    }

    /**
     * FormDie.
     *
     * @param string $msg Message to echo.
     */
    public function FormDie($msg)
    {
        echo ($msg);
        pnShutDown(0);
    }

    /**
     * TranslateForDisplay.
     *
     * @param string  $txt      Text to translate for display.
     * @param boolean $doEncode True to formatForDisplay.
     * 
     * @return string Text.
     */
    public function TranslateForDisplay($txt, $doEncode = true)
    {
        $txt = (strlen($txt) > 0 && $txt[0] == '_' && defined($txt) ? constant($txt) : $txt);
        if ($doEncode) {
            $txt = DataUtil::formatForDisplay($txt);
        }
        return $txt;
    }

    /* --- Validation --- */

    /**
     * AddValidator.
     *
     * @param validator $validator Validator to add.
     */
    public function AddValidator(&$validator)
    {
        $this->Validators[] = &$validator;
    }

    /**
     * IsValid. calls Validate() if validation not yet checked.
     * Then returns true if all validators pass.
     *
     * @return boolean True if all validators are valid.
     */
    public function IsValid()
    {
        if (!$this->ValidationChecked) {
            $this->Validate();
        }

        return $this->_IsValid;
    }

    /**
     * GetValidators.
     * 
     * @return array Array of all Validators.
     */
    public function &GetValidators()
    {
        return $this->Validators;
    }

    /**
     * Validate all validators and set ValidationChecked to true.
     * 
     */
    public function Validate()
    {
        $this->_IsValid = true;

        $lim = count($this->Validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->Validators[$i]->validate($this);
            $this->_IsValid = $this->_pnFormIsValid && $this->Validators[$i]->isValid;
        }

        $this->ValidationChecked = true;
    }

    /**
     * Clears the validation for all validators.
     *
     */
    public function ClearValidation()
    {
        $this->_IsValid = true;

        $lim = count($this->Validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->Validators[$i]->clearValidation($this);
        }
    }

    /* --- Public state management --- */

    public function SetState($region, $varName, &$varValue)
    {
        if (!isset($this->State[$region])) {
            $this->State[$region] = array();
        }

        $this->State[$region][$varName] = &$varValue;
    }

    public function &GetState($region, $varName)
    {
        return $this->State[$region][$varName];
    }

    /* --- Error handling --- */

    /**
     * Register an error
     *
     * Example:
     * <code>
     * function handleCommand(...)
     * {
     *   if (... it did not work ...)
     *     return $render->RegisterError('Operation X failed due to Y');
     * }
     * </code>
     */
    public function SetErrorMsg($msg)
    {
        LogUtil::registerError($msg);
        $this->ErrorMsgSet = true;
        return false;
    }

    public function GetErrorMsg()
    {
        if ($this->ErrorMsgSet) {
            include_once ('lib/render/plugins/function.getstatusmsg.php');
            $args = array();
            return smarty_function_getstatusmsg($args, $this);
        } else {
            return '';
        }
    }

    public function HasError()
    {
        return $this->ErrorMsgSet;
    }

    /**
     * Register that we have used LogUtil::registerError() to set an error.
     *
     * Example:
     * <code>
     * function initialize(&$render)
     * {
     *   if (... not has access ...)
     *     return $render->RegisterError(LogUtil::registerPermissionError());
     * }
     * </code>
     */
    public function RegisterError($dummy)
    {
        $this->ErrorMsgSet = true;
        return false;
    }

    /* --- Redirection --- */

    public function Redirect($url)
    {
        pnRedirect($url);
        $this->Redirected = true;
    }

    /* --- Event handling --- */

    /**
     * Get postback reference
     *
     * Call this method to get a piece of code that will generate a postback event. The returned JavaScript code can
     * be called at any time to generate the postback. The plugin that receives the postback must implement
     * a function "raisePostBackEvent(&$render, $eventArgument)" that will handle the event.
     *
     * Example (taken from the {@link pnFormContextMenuItem} plugin):
     * <code>
     * function render(&$render)
     * {
     *   $click = $render->GetPostBackEventReference($this, $this->commandName);
     *   $url = 'javascript:' . $click;
     *   $title = $render->TranslateForDisplay($this->title);
     *   $html = "<li><a href=\"$url\">$title</a></li>";
     *
     *   return $html;
     * }
     *
     * function raisePostBackEvent(&$render, $eventArgument)
     * {
     *   $args = array('commandName' => $eventArgument, 'commandArgument' => null);
     *   $render->RaiseEvent($this->onCommand == null ? 'handleCommand' : $this->onCommand, $args);
     * }
     * </code>
     *
     * @param plugin object Reference to the plugin that should receive the postback event
     * @param commandName string Command name to pass to the event handler
     */
    public function GetPostBackEventReference(&$plugin, $commandName)
    {
        return "pnFormDoPostBack('$plugin->id', '$commandName');";
    }

    /// Raise event in the main user event handler
    /// This method raises an event in the main user event handler. It is usually called from a plugin to signal
    /// that something in that plugin needs attention.
    public function RaiseEvent($eventHandlerName, $args)
    {
        $handlerClass = & $this->EventHandler;

        if (method_exists($handlerClass, $eventHandlerName)) {
            if ($handlerClass->$eventHandlerName($this, $args) === false) {
                return false;
            }
        }

        return true;
    }

    /* --- Private --- */

    /* --- Include list --- */

    public function InitializeIncludes()
    {
        $this->Includes = array();
    }

    public function GetIncludesText()
    {
        $bytes = serialize($this->Includes);
        $bytes = SecurityUtil::signData($bytes);
        $base64 = base64_encode($bytes);

        return $base64;
    }

    public function GetIncludesHTML()
    {
        $base64 = $this->GetIncludesText();

        // TODO - this is a quick hack to move __pnFormINCLUDES into a session variable.
        // A better way needs to be found rather than relying on a call to GetIncludesHTML.
        SessionUtil::setVar('__pnFormINCLUDES', $base64);
        return '';
    }

    public function DecodeIncludes()
    {
        // TODO - See GetIncludesHTML()
        $base64 = SessionUtil::getVar('__pnFormINCLUDES');
        $bytes = base64_decode($base64);
        $bytes = SecurityUtil::checkSignedData($bytes);
        if (!$bytes) {
            return; // error handler required - drak
        }

        $this->Includes = unserialize($bytes);

        foreach ($this->Includes as $includeFilename => $dummy) {
            require_once $includeFilename;
        }
    }

    /* --- Authentication key --- */

    public function GetAuthKeyHTML()
    {
        $key = SecurityUtil::generateAuthKey();
        $html = "<input type=\"hidden\" name=\"authid\" value=\"$key\" id=\"pnFormAuthid\"/>";
        return $html;
    }

    /* --- State management --- */

    public function InitializeState()
    {
        $this->State = array();
    }

    public function GetStateText()
    {
        $this->SetState('pnFormRender', 'eventHandler', $this->EventHandler);

        $pluginState = $this->GetPluginState();
        $this->SetState('pnFormRender', 'plugins', $pluginState);

        $bytes = serialize($this->State);
        $bytes = SecurityUtil::signData($bytes);
        $base64 = base64_encode($bytes);

        return $base64;
    }

    public function GetPluginState()
    {
        //$this->dumpPlugins("Encode state", $this->Plugins);
        $state = $this->GetPluginState_rec($this->Plugins);
        return $state;
    }

    public function GetPluginState_rec(&$plugins)
    {
        $state = array();

        $lim = count($plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $plugin = & $plugins[$i];

            // Handle sub-plugins special and clear stuff not to be serialized
            $plugin->parentPlugin = null;
            $subPlugins = $plugin->plugins;
            $plugin->plugins = null;

            $varInfo = get_class_vars(get_class($plugin));

            $pluginState = array();
            foreach ($varInfo as $name => $value) {
                $pluginState[] = $plugin->$name;
            }

            $state[] = array(get_class($plugin), $pluginState, $this->GetPluginState_rec($subPlugins));

            $plugin->plugins = & $subPlugins;
        }

        return $state;
    }

    public function GetStateHTML()
    {
        $base64 = $this->GetStateText();

        // TODO - this is a quick hack to move __pnFormSTATE into a session variable.
        // This is meant to solve issue #2013
        // A better way needs to be found rather than relying on a call to GetStateHTML.
        SessionUtil::setVar('__pnFormSTATE', $base64);
        // TODO - __pnFormSTATE still needs to be on the form, to ensure that IsPostBack() returns properly
        return '<input type="hidden" name="__pnFormSTATE" value="true"/>';
    }

    public function DecodeState()
    {
        // TODO - see GetStateHTML()
        $base64 = SessionUtil::getVar('__pnFormSTATE');
        $bytes = base64_decode($base64);
        $bytes = SecurityUtil::checkSignedData($bytes);
        if (!$bytes) {
            return; // FIXME: error handler required - drak
        }

        $this->State = unserialize($bytes);
        $this->Plugins = & $this->DecodePluginState();

    //$this->dumpPlugins("Decoded state", $this->Plugins);
    }

    public function &DecodePluginState()
    {
        $state = $this->GetState('pnFormRender', 'plugins');
        $decodedState = $this->DecodePluginState_rec($state);
        return $decodedState;
    }

    public function &DecodePluginState_rec(&$state)
    {
        $plugins = array();

        foreach ($state as $pluginInfo) {
            $pluginType = $pluginInfo[0];
            $pluginState = &$pluginInfo[1];
            $subState = &$pluginInfo[2];

            $dummy = array();
            $plugin = new $pluginType($this, $dummy);

            $vars = array_keys(get_class_vars(get_class($plugin)));

            $varCount = count($vars);
            if ($varCount != count($pluginState)) {
                return pn_exit("Cannot restore pnForm plugin of type '$pluginType' since stored and actual number of member vars differ");
            }

            for ($i = 0; $i < $varCount; ++$i) {
                $var = $vars[$i];
                $plugin->$var = $pluginState[$i];
            }

            $plugin->plugins = $this->DecodePluginState_rec($subState);
            $plugins[] = &$plugin;

            $lim = count($plugin->plugins);
            for ($i = 0; $i < $lim; ++$i) {
                $plugin->plugins[$i]->parentPlugin = &$plugins[count($plugins) - 1];
            }
        }

        return $plugins;
    }

    public function DecodeEventHandler()
    {
        $storedHandler = & $this->GetState('pnFormRender', 'eventHandler');
        $currentHandler = & $this->EventHandler;

        // Copy saved data into event handler (this is where form handler variables are restored)
        $varInfo = get_class_vars(get_class($storedHandler));

        foreach ($varInfo as $name => $value) {
            $currentHandler->$name = $storedHandler->$name;
        }
    }

    /* --- plugin event generators --- */

    public function InitializePlugins()
    {
        $this->InitializePlugins_rec($this->Plugins);

        return true;
    }

    public function InitializePlugins_rec(&$plugins)
    {
        $lim = count($plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $this->InitializePlugins_rec($plugins[$i]->plugins);
            $plugins[$i]->initialize($this);
        }
    }

    public function DecodePlugins()
    {
        $this->DecodePlugins_rec($this->Plugins);

        return true;
    }

    public function DecodePlugins_rec(&$plugins)
    {
        for ($i = 0, $lim = count($plugins); $i < $lim; ++$i) {
            $this->DecodePlugins_rec($plugins[$i]->plugins);
            $plugins[$i]->decode($this);
        }
    }

    public function DecodePostBackEvent()
    {
        $eventTarget = FormUtil::getPassedValue('pnFormEventTarget', null, 'POST');
        $eventArgument = FormUtil::getPassedValue('pnFormEventArgument', null, 'POST');

        if ($eventTarget != '') {
            $targetPlugin = & $this->GetPluginById($eventTarget);
            if ($targetPlugin != null) {
                $targetPlugin->raisePostBackEvent($this, $eventArgument);
            }
        }

        $this->DecodePostBackEvent_rec($this->Plugins);
    }

    public function DecodePostBackEvent_rec(&$plugins)
    {
        for ($i = 0, $lim = count($plugins); $i < $lim; ++$i) {
            $this->DecodePostBackEvent_rec($plugins[$i]->plugins);
            $plugins[$i]->decodePostBackEvent($this);
        }
    }

    public function PostRender()
    {
        $this->PostRender_rec($this->Plugins);

        return true;
    }

    public function PostRender_rec(&$plugins)
    {
        $lim = count($plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $this->PostRender_rec($plugins[$i]->plugins);
            $plugins[$i]->postRender($this);
        }
    }

    /* --- Reading and settings submitted values --- */

    /**
     * Read all values from form
     *
     * Use this function to read the values send by the browser on postback. The return
     * value is an associative array of input names mapping to the posted values.
     * For instance the data:
     *
     * <code>
     * array('title'    => 'The posted title',
     *       'text'     => 'The posted text',
     *       'servings' => 4)
     * </code>
     *
     * Most input plugins supports grouping of posted data. These inputs allows you to
     * write something similar to what you do on the pnformtextinput plugin:
     *
     * <code>
     *   <!--[pnformtextinput id="title" group="A"]--><br/>
     *   <!--[pnformtextinput id="text" textMode=multiline group="A"]-->
     *   <!--[pnformintinput id="servings"]--><br/>
     * </code>
     *
     * Grouped data is combined into associative arrays with all the values in the group.
     * The above example would give the data set:
     *
     * <code>
     * array('A' => array('title'    => 'The posted title',
     *                    'text'     => 'The posted text'),
     *       'servings' => 4)
     * </code>
     *
     */
    public function GetValues()
    {
        $result = array();

        $this->GetValues_rec($this->Plugins, $result);

        return $result;
    }

    public function GetValues_rec(&$plugins, &$result)
    {
        $lim = count($plugins);
        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $plugin = &$plugins[$i];

            $this->GetValues_rec($plugin->plugins, $result);

            if (method_exists($plugin, 'saveValue')) {
                $plugin->saveValue($this, $result);
            }
        }
    }

    public function SetValues(&$values, $group = null)
    {
        $empty = null;
        return $this->SetValues2($values, $group, $empty);
    }

    public function SetValues2(&$values, $group = null, &$plugins)
    {
        if ($plugins == null) {
            $this->SetValues_rec($values, $group, $this->Plugins);
        } else {
            $this->SetValues_rec($values, $group, $plugins);
        }

        return true;
    }

    public function SetValues_rec(&$values, $group, &$plugins)
    {
        $lim = count($plugins);
        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $plugin = &$plugins[$i];

            $this->SetValues_rec($values, $group, $plugin->plugins);

            if (method_exists($plugin, 'loadValue')) {
                $plugin->loadValue($this, $values);
            }
        }
    }

    public function dumpPlugins($msg, &$plugins)
    {
        echo "<pre style=\"background-color: #CFC; text-align: left;\">\n";
        echo "** $msg **\n";
        $this->dumpPlugins_rec($this->Plugins);
        echo "</pre>";
    }

    public function dumpPlugins_rec(&$plugins)
    {
        $lim = count($plugins);
        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $p = &$plugins[$i];
            echo "\n(\n{$p->id}: {$p->parentPlugin}";
            $this->dumpPlugins_rec($p->plugins);
            echo "\n)\n";
        }
    }
}