<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * User interaction handler for Form system.
 *
 * This class is the main entry point for using the Form system. It is expected to be used in Zikula's
 * controllers, like this:
 * <code>
 * public function create($args)
 * {
 *   // Create instance of Form_View
 *   $view = FormUtil::newForm('howtoforms');
 *
 *   // Execute form using supplied template and event handler
 *   return $view->execute('modname_user_create.tpl', new Modname_Form_Handler_Create());
 * }
 * </code>
 * See tutorials elsewhere for general introduction to Form.
 */
class Form_View extends Zikula_View
{
    /**
     * Variable saving all required state information.
     *
     * @var array
     * @internal
     */
    public $state;

    /**
     * List of included files required to recreate plugins (Smarty function.xxx.php files).
     *
     * @var array
     * @internal
     */
    public $includes;

    /**
     * List of instantiated plugins.
     *
     * @var array
     * @internal
     */
    public $plugins;

    /**
     * Stack with all instantiated blocks (push when starting block, pop when ending block).
     *
     * @var array
     * @internal
     */
    public $blockStack;

    /**
     * List of validators on page.
     *
     * @var array
     * @internal
     */
    public $validators;

    /**
     * Flag indicating if validation has been done or not.
     *
     * @var boolean
     * @internal
     */
    public $validationChecked;

    /**
     * Indicates whether page is valid or not.
     *
     * @var boolean
     * @internal
     */
    public $_isValid;

    /**
     * Current ID count - used to assign automatic ID's to all items.
     *
     * @var intiger
     * @internal
     */
    public $idCount;

    /**
     * Reference to the main user code event handler.
     *
     * @var Form_Handler
     * @internal
     */
    public $eventHandler;

    /**
     * Error message has been set.
     *
     * @var boolean
     * @internal
     */
    public $errorMsgSet;

    /**
     * Set to true if Form_Vew::Redirect was called. Means no HTML output should be returned.
     *
     * @var boolean
     * @internal
     */
    public $redirected;

    /**
     * Constructor.
     *
     * Use FormUtil::newForm() instead of instantiating Form_View directly.
     *
     * @param string  $module  Module name.
     * @param boolean $caching Caching flag (not used - just for e_strict).
     */
    public function __construct($module, $caching=null)
    {
        // override behaviour of anonymous sessions
        SessionUtil::requireSession();

        // Construct and make normal Smarty use possible
        parent::__construct($module, false);
        $this->addPluginDir('lib/Form/viewplugins');

        // Setup
        $this->idCount = 1;
        $this->errorMsgSet = false;
        $this->plugins = array();
        $this->blockStack = array();
        $this->redirected = false;

        $this->validators = array();
        $this->validationChecked = false;
        $this->_isValid = null;

        $this->initializeState();
        $this->initializeIncludes();
    }

    /**
     * Main event loop handler.
     *
     * This is the function to call instead of the normal $view->fetch(...).
     *
     * @param boolean      $template     Name of template file.
     * @param Form_Handler $eventHandler Instance of object that inherits from Form_Handler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function execute($template, Form_Handler $eventHandler)
    {
        // Save handler for later use
        $this->eventHandler = $eventHandler;
        $this->eventHandler->setView($this);
        $this->eventHandler->setDomain($this->domain);
        $this->eventHandler->setup();
        $this->eventHandler->preInitialize();

        if ($this->isPostBack()) {
            if (!SecurityUtil::confirmAuthKey()) {
                return LogUtil::registerAuthidError();
            }

            $this->decodeIncludes();
            $this->decodeState();

            if ($eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }

            $this->eventHandler->postInitialize();

            // (no create event)
            $this->initializePlugins(); // initialize event
            $this->decodePlugins(); // decode event
            $this->decodePostBackEvent(); // Execute optional postback after plugins have read their values
        } else {
            if ($eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }
            $this->eventHandler->postInitialize();
        }

        // render event (calls registerPlugin)
        $output = $this->fetch($template);

        if ($this->hasError()) {
            return $this->getErrorMsg();
        }

        // Check redirection at this point, ignore any generated HTML if redirected is required.
        // We cannot skip HTML generation entirely in case of System::redirect since there might be
        // some relevant code to execute in the plugins.
        if ($this->redirected) {
            return true;
        }

        return $output;
    }

    /**
     * Register a plugin.
     *
     * This method registers a plugin used in a template. Plugins must beregistered to be used in Form_View
     * (unlike Smarty plugins). The register call must be done inside the Smarty plugin function in a
     * Smarty plugin file. Use like this:
     * <code>
     * // In file "function.myplugin.php"
     *
     * // Form plugin class
     * class MyPlugin extends Form_Plugin
     * { ... }
     *
     * // Smarty plugin function
     * function smarty_function_myplugin($params, $view)
     * {
     *   return $view->registerPlugin('MyPlugin', $params);
     * }
     * </code>
     * Registering a plugin ensures it is included in the plugin hierarchy of the current page, so that it's
     * various event handlers can be called by the framework.
     *
     * Do not use this function for registering Smarty blocks (the $isBlock parameter is for internal use).
     * Use Form_View::registerBlock() instead.
     *
     * See also all the function.formXXX.php plugins for examples.
     *
     * @param string  $pluginName Full class name of the plugin to register.
     * @param array   &$params    Parameters passed from the Smarty plugin function.
     * @param boolean $isBlock    Indicates whether the plugin is a Smarty block or a Smarty function (internal).
     *
     * @return string Returns what the render() method of the plugin returns.
     *
     * @throws InvalidArgumentException Thrown if the plugin is not an instance of Form_Plugin.
     */
    public function registerPlugin($pluginName, &$params, $isBlock = false)
    {
        // Make sure we have a suitable ID for the plugin
        $id = $this->getPluginId($params);

        $stackCount = count($this->blockStack);

        // A volatile block is a block that cannot be restored through view state
        // This is the case for Form plugins inside {if} and {foreach} tags.
        // So create new plugins for these blocks instead of relying on the existing plugins.

        if (!$this->isPostBack() || $stackCount > 0 && $this->blockStack[$stackCount - 1]->volatile) {
            $plugin = new $pluginName($this, $params);
            if (!$plugin instanceof Form_Plugin) {
                throw new InvalidArgumentException(__f('Plugin %s must be an instance of Form_Plugin', $pluginName));
            }
            $plugin->setup();

            // Make sure to store ID and render reference in plugin
            $plugin->id = $id;

            if ($stackCount > 0) {
                $plugin->parentPlugin = &$this->blockStack[$stackCount - 1];
                $this->blockStack[$stackCount - 1]->registerPlugin($this, $plugin);
            } else {
                // Store plugin for later reference
                $this->plugins[] = $plugin;
            }

            // Copy parameters to member variables and attribute set
            $plugin->readParameters($this, $params);
            $plugin->create($this, $params);
            $plugin->load($this, $params);

            // Remember which file this plugin came from in order to be able to restore it.
            $pluginPath = str_replace(realpath(dirname(__FILE__) . '/..') . DIRECTORY_SEPARATOR, '', $plugin->getFilename());
            $this->includes[$pluginPath] = 1;

        } else {
            // Fetch plugin instance by ID
            // It has already got it's initialize and decode event at this point
            $plugin = $this->getPluginById($id);

            // Kill existing plugins beneath a volatile block
            if (isset($plugin->volatile) && $plugin->volatile) {
                $plugin->plugins = null;
            }
        }

        $plugin->dataBound($this, $params);

        if ($isBlock) {
            $this->blockStack[] = $plugin;
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
     * Use this like {@link Form_View::registerPlugin} but for Smarty blocks instead of Smarty plugins.
     * <code>
     * // In file "block.myblock.php"
     *
     * // Form plugin class (also used for blocks)
     * class MyBlock extends Form_Plugin
     * { ... }
     *
     * // Smarty block function
     * function smarty_block_myblock($params, $content, $view)
     * {
     *   return $view->registerBlock('MyBlock', $params, $content);
     * }
     * </code>
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the Smarty block function.
     * @param string &$content   Content passed from the Smarty block function.
     *
     * @return string The rendered content.
     */
    public function registerBlock($pluginName, &$params, &$content)
    {
        if (!$content) {
            return $this->registerBlockBegin($pluginName, $params);
        } else {
            return $this->registerBlockEnd($pluginName, $params, $content);
        }
    }

    /**
     * RegisterBlockBegin.
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the block function.
     *
     * @internal
     * @return void
     */
    public function registerBlockBegin($pluginName, &$params)
    {
        $output = $this->registerPlugin($pluginName, $params, true);
        $plugin = &$this->blockStack[count($this->blockStack) - 1];
        $plugin->blockBeginOutput = $output;
    }

    /**
     * RegisterBlockEnd.
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the block function.
     * @param string $content    The block content.
     *
     * @internal
     * @return string The rendered content.
     */
    public function registerBlockEnd($pluginName, &$params, $content)
    {
        $plugin = &$this->blockStack[count($this->blockStack) - 1];
        array_pop($this->blockStack);

        if ($plugin->visible) {
            $output = $plugin->blockBeginOutput . $plugin->renderContent($this, $content) . $plugin->renderEnd($this);
        }

        $plugin->blockBeginOutput = null;

        return $output;
    }

    /**
     * GetPluginId.
     *
     * @param array &$params Parameters passed from the block function.
     *
     * @internal
     * @return string The plugin ID.
     */
    public function getPluginId(&$params)
    {
        if (!isset($params['id'])) {
            return 'plg' . ($this->idCount++);
        }

        return $params['id'];
    }

    /**
     * Get Plugin by id.
     *
     * @param intiger $id Plugin ID.
     *
     * @return Form_Plugin|null
     */
    public function getPluginById($id)
    {
        $lim = count($this->plugins);

        for ($i = 0; $i < $lim; ++$i) {
            $plugin = $this->getPluginById_rec($this->plugins[$i], $id);
            if ($plugin != null) {
                return $plugin;
            }
        }

        $null = null;
        return $null;
    }

    /**
     * Get Plugin By Id_rec.
     *
     * @param object  &$plugin Plugin.
     * @param intiger $id      Plugin ID.
     *
     * @return Form_Plugin|null
     */
    public function getPluginById_rec(&$plugin, $id)
    {
        if ($plugin->id == $id) {
            return $plugin;
        }

        $lim = count($plugin->plugins);

        for ($i = 0; $i < $lim; ++$i) {
            $subPlugin = $this->getPluginById_rec($plugin->plugins[$i], $id);
            if ($subPlugin != null) {
                return $subPlugin;
            }
        }

        $null = null;
        return $null;
    }

    /**
     * Is PostBack.
     *
     * @return boolean
     */
    public function isPostBack()
    {
        return isset($_POST['__FormSTATE']);
    }

    /**
     * Form Die.
     *
     * @param string $msg Message to echo.
     *
     * @return void
     */
    public function formDie($msg)
    {
        echo ($msg);
        System::shutdown(0);
    }

    /**
     * Translate For Display.
     *
     * @param string  $txt      Text to translate for display.
     * @param boolean $doEncode True to formatForDisplay.
     *
     * @return string Text.
     */
    public function translateForDisplay($txt, $doEncode = true)
    {
        if ($doEncode) {
            $txt = DataUtil::formatForDisplay($txt);
        }

        return $txt;
    }

    // --- Validation ---

    /**
     * Add Validator.
     *
     * @param validator &$validator Validator to add.
     *
     * @return void
     */
    public function addValidator(&$validator)
    {
        $this->validators[] = &$validator;
    }

    /**
     * Is valid calls validate() if validation not yet checked.
     *
     * Then returns true if all validators pass.
     *
     * @return boolean True if all validators are valid.
     */
    public function isValid()
    {
        if (!$this->validationChecked) {
            $this->validate();
        }

        return $this->_IsValid;
    }

    /**
     * Get validators.
     *
     * @return array Array of all Validators.
     */
    public function &getValidators()
    {
        return $this->validators;
    }

    /**
     * Validate all validators and set ValidationChecked to true.
     *
     * @return void
     */
    public function validate()
    {
        $this->_IsValid = true;

        $lim = count($this->validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->validators[$i]->validate($this);
            $this->_IsValid = $this->_IsValid && $this->validators[$i]->isValid;
        }

        $this->validationChecked = true;
    }

    /**
     * Clears the validation for all validators.
     *
     * @return void
     */
    public function clearValidation()
    {
        $this->_IsValid = true;

        $lim = count($this->validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->validators[$i]->clearValidation($this);
        }
    }

    // --- Public state management ---

    /**
     * Sets a state.
     *
     * @param string $region    State region.
     * @param string $varName   State variable name.
     * @param mixed  &$varValue State variable value.
     *
     * @return void
     */
    public function setState($region, $varName, &$varValue)
    {
        if (!isset($this->state[$region])) {
            $this->state[$region] = array();
        }

        $this->state[$region][$varName] = &$varValue;
    }

    /**
     * Get a state.
     *
     * @param string $region  State region.
     * @param string $varName State variable name.
     *
     * @return mixed State variable value.
     */
    public function &getState($region, $varName)
    {
        return $this->state[$region][$varName];
    }

    // --- Error handling ---

    /**
     * Register an error.
     *
     * Example:
     * <code>
     * function handleCommand(...)
     * {
     *   if (... it did not work ...)
     *     return $view->registerError('Operation X failed due to Y');
     * }
     * </code>
     *
     * @param string $msg Error message.
     *
     * @return false
     */
    public function setErrorMsg($msg)
    {
        $this->errorMsgSet = true;

        return LogUtil::registerError($msg);
    }

    /**
     * Returns registered error.
     *
     * @return string Error string.
     */
    public function getErrorMsg()
    {
        if ($this->errorMsgSet) {
            include_once ('lib/view/plugins/function.getstatusmsg.php');
            $args = array();
            return smarty_function_getstatusmsg($args, $this);
        } else {
            return '';
        }
    }

    /**
     * Checks whether or not there is an error.
     *
     * @return boolean
     */
    public function hasError()
    {
        return $this->errorMsgSet;
    }

    /**
     * Register that we have used LogUtil::registerError() to set an error.
     *
     * Example:
     * <code>
     * function initialize($view)
     * {
     *   if (... not has access ...)
     *     return $view->registerError(LogUtil::registerPermissionError());
     * }
     * </code>
     *
     * @param mixed $dummy Just a dummy variable.
     *
     * @return false
     */
    public function registerError($dummy)
    {
        $this->errorMsgSet = true;

        return false;
    }

    // --- Redirection ---

    /**
     * Redirect.
     *
     * @param string $url Url.
     *
     * @return boolean True if redirected successfully, otherwise false.
     */
    public function redirect($url)
    {
        $this->redirected = true;

        return System::redirect($url);
    }

    // --- Event handling ---

    /**
     * Get postback reference.
     *
     * Call this method to get a piece of code that will generate a postback event. The returned JavaScript code can
     * be called at any time to generate the postback. The plugin that receives the postback must implement
     * a function "raisePostBackEvent($view, $eventArgument)" that will handle the event.
     *
     * Example (taken from the {@link Form_Plugin_ContextMenu_Item} plugin):
     * <code>
     * function render($view)
     * {
     *   $click = $view->getPostBackEventReference($this, $this->commandName);
     *   $url = 'javascript:' . $click;
     *   $title = $view->translateForDisplay($this->title);
     *   $html = "<li><a href=\"$url\">$title</a></li>";
     *
     *   return $html;
     * }
     *
     * function raisePostBackEvent($view, $eventArgument)
     * {
     *   $args = array('commandName' => $eventArgument, 'commandArgument' => null);
     *   $view->raiseEvent($this->onCommand == null ? 'handleCommand' : $this->onCommand, $args);
     * }
     * </code>
     *
     * @param object $plugin      Reference to the plugin that should receive the postback event.
     * @param string $commandName Command name to pass to the event handler.
     *
     * @return string
     */
    public function getPostBackEventReference($plugin, $commandName)
    {
        return "FormDoPostBack('$plugin->id', '$commandName');";
    }

    /**
     * Raise event in the main user event handler.
     *
     * This method raises an event in the main user event handler.
     * It is usually called from a plugin to signal that something in that
     * plugin needs attention.
     *
     * @param string $eventHandlerName The event handler method name.
     * @param mixed  $args             The event arguments.
     *
     * @return boolean
     */
    public function raiseEvent($eventHandlerName, $args)
    {
        $handlerClass = & $this->eventHandler;

        if (method_exists($handlerClass, $eventHandlerName)) {
            if ($handlerClass->$eventHandlerName($this, $args) === false) {
                return false;
            }
        }

        return true;
    }

    // --- Private ---

    // --- Include list ---

    /**
     * Initializes the include memory.
     *
     * @return void
     */
    public function initializeIncludes()
    {
        $this->includes = array();
    }

    /**
     * Get the includes as text.
     *
     * @return string Encoded includes.
     */
    public function getIncludesText()
    {
        $bytes = serialize($this->includes);
        $bytes = SecurityUtil::signData($bytes);
        $base64 = base64_encode($bytes);

        return $base64;
    }

    /**
     * Save includes to session.
     *
     * @return string Empty string.
     */
    public function getIncludesHTML()
    {
        $base64 = $this->getIncludesText();

        // TODO - this is a quick hack to move __FormINCLUDES into a session variable.
        // A better way needs to be found rather than relying on a call to getIncludesHTML.
        SessionUtil::setVar('__FormINCLUDES', $base64);
        return '';
    }

    /**
     * Decode includes from session.
     *
     * @return void
     */
    public function decodeIncludes()
    {
        // TODO - See getIncludesHTML()
        $base64 = SessionUtil::getVar('__FormINCLUDES');
        $bytes = base64_decode($base64);
        $bytes = SecurityUtil::checkSignedData($bytes);
        if (!$bytes) {
            return; // error handler required - drak
        }

        $this->includes = unserialize($bytes);

        // Load the third party plugins only
        foreach ($this->includes as $includeFilename => $dummy) {
            if (strpos($includeFilename, 'config' . DIRECTORY_SEPARATOR)
             || strpos($includeFilename, 'modules' . DIRECTORY_SEPARATOR)) {
                require_once $includeFilename;
            }
        }
    }

    // --- Authentication key ---

    /**
     * Get the auth key input field.
     *
     * @return string HTML input field.
     */
    public function getAuthKeyHTML()
    {
        $key = SecurityUtil::generateAuthKey();
        $html = "<input type=\"hidden\" name=\"authid\" value=\"{$key}\" id=\"FormAuthid\" />";
        return $html;
    }

    // --- State management ---

    /**
     * Initialize state memory.
     *
     * @return void
     */
    public function initializeState()
    {
        $this->state = array();
    }

    /**
     * Get saved states as text.
     *
     * @return text Encoded states.
     */
    public function getStateText()
    {
        $pluginState = $this->getPluginState();
        $this->setState('Form_View', 'plugins', $pluginState);

        $bytes = serialize($this->state);
        $bytes = SecurityUtil::signData($bytes);
        $base64 = base64_encode($bytes);

        return $base64;
    }

    /**
     * Get plugin states.
     *
     * @return array States.
     */
    public function getPluginState()
    {
        //$this->dumpPlugins("Encode state", $this->plugins);
        $state = $this->getPluginState_rec($this->plugins);
        return $state;
    }

    /**
     * Recursive helper method for self::getPluginState.
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return array Plugin states.
     */
    public function getPluginState_rec($plugins)
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

            $state[] = array(get_class($plugin), $pluginState, $this->getPluginState_rec($subPlugins));

            $plugin->plugins = & $subPlugins;
        }

        return $state;
    }

    /**
     * Get states as HTML
     *
     * @return text State HTML input field.
     */
    public function getStateHTML()
    {
        $base64 = $this->getStateText();

        // TODO - this is a quick hack to move __FormSTATE into a session variable.
        // This is meant to solve issue #2013
        // A better way needs to be found rather than relying on a call to getStateHTML.
        SessionUtil::setVar('__FormSTATE', $base64);
        // TODO - __FormSTATE still needs to be on the form, to ensure that isPostBack() returns properly
        return '<input type="hidden" name="__FormSTATE" value="true" />';
    }

    /**
     * Decode state memory from session.
     *
     * @return void
     */
    public function decodeState()
    {
        // TODO - see getStateHTML()
        $base64 = SessionUtil::getVar('__FormSTATE');
        $bytes = base64_decode($base64);
        $bytes = SecurityUtil::checkSignedData($bytes);
        if (!$bytes) {
            return; // FIXME: error handler required - drak
        }

        $this->state = unserialize($bytes);
        $this->plugins = & $this->decodePluginState();

        //$this->dumpPlugins("Decoded state", $this->plugins);
    }

    /**
     * Decode plugin state.
     *
     * @return array decoded states.
     */
    public function &decodePluginState()
    {
        $state = $this->getState('Form_View', 'plugins');
        $decodedState = $this->decodePluginState_rec($state);

        return $decodedState;
    }

    /**
     * Recursive helper method for self::decodePluginState.
     *
     * @param array &$state Plugin states.
     *
     * @return array Decoded plugin states.
     */
    public function &decodePluginState_rec(&$state)
    {
        $plugins = array();

        foreach ($state as $pluginInfo) {
            $pluginType = $pluginInfo[0];
            $pluginState = $pluginInfo[1];
            $subState = $pluginInfo[2];

            $dummy = array();
            $plugin = new $pluginType($this, $dummy);

            $vars = array_keys(get_class_vars(get_class($plugin)));

            $varCount = count($vars);
            if ($varCount != count($pluginState)) {
                return LogUtil::registerError(__f("Cannot restore Form_View plugin of type '%s' since stored and actual number of member vars differ", $pluginType));
            }

            for ($i = 0; $i < $varCount; ++$i) {
                $var = $vars[$i];
                $plugin->$var = $pluginState[$i];
            }

            $plugin->plugins = $this->decodePluginState_rec($subState);
            $plugins[] = $plugin;

            $lim = count($plugin->plugins);
            for ($i = 0; $i < $lim; ++$i) {
                $plugin->plugins[$i]->parentPlugin = $plugins[count($plugins) - 1];
            }
        }

        return $plugins;
    }

    // --- plugin event generators ---

    /**
     * Initialize Plugins
     *
     * @return boolean
     */
    public function initializePlugins()
    {
        $this->initializePlugins_rec($this->plugins);

        return true;
    }

    /**
     * Recursive helper method for self::initializePlugins().
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return void
     */
    public function initializePlugins_rec($plugins)
    {
        $lim = count($plugins);

        for ($i = 0; $i < $lim; ++$i) {
            $this->initializePlugins_rec($plugins[$i]->plugins);
            $plugins[$i]->preInitialize();
            $plugins[$i]->initialize($this);
            $plugins[$i]->postInitialize();
        }
    }

    /**
     * Decode plugins.
     *
     * @return boolean
     */
    public function decodePlugins()
    {
        $this->decodePlugins_rec($this->plugins);

        return true;
    }

    /**
     * Recursive helper method for self::decodePlugins.
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return void
     */
    public function decodePlugins_rec($plugins)
    {
        for ($i = 0, $lim = count($plugins); $i < $lim; ++$i) {
            $this->decodePlugins_rec($plugins[$i]->plugins);
            $plugins[$i]->decode($this);
        }
    }

    /**
     * Decode post back event.
     *
     * @return void
     */
    public function decodePostBackEvent()
    {
        $eventTarget = FormUtil::getPassedValue('FormEventTarget', null, 'POST');
        $eventArgument = FormUtil::getPassedValue('FormEventArgument', null, 'POST');

        if ($eventTarget != '') {
            $targetPlugin = & $this->getPluginById($eventTarget);
            if ($targetPlugin != null) {
                $targetPlugin->raisePostBackEvent($this, $eventArgument);
            }
        }

        $this->decodePostBackEvent_rec($this->plugins);
    }

    /**
     * Recursive helper method for self::decodePostBackEvent().
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return void
     */
    public function decodePostBackEvent_rec($plugins)
    {
        for ($i = 0, $lim = count($plugins); $i < $lim; ++$i) {
            $this->decodePostBackEvent_rec($plugins[$i]->plugins);
            $plugins[$i]->decodePostBackEvent($this);
        }
    }

    /**
     * Post render event.
     *
     * @return boolean
     */
    public function postRender()
    {
        $this->postRender_rec($this->plugins);

        return true;
    }

    /**
     * Recursive helper method for self::postRender().
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return void
     */
    public function postRender_rec($plugins)
    {
        $lim = count($plugins);

        for ($i = 0; $i < $lim; ++$i) {
            $this->postRender_rec($plugins[$i]->plugins);
            $plugins[$i]->postRender($this);
        }
    }

    // --- Reading and settings submitted values ---

    /**
     * Read all values from form.
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
     * write something similar to what you do on the formtextinput plugin:
     *
     * <code>
     *   {formtextinput id="title" group="A"}<br />
     *   {formtextinput id="text" textMode=multiline group="A"}
     *   {formintinput id="servings"}<br />
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
     * @return mixed
     */
    public function getValues()
    {
        $result = array();

        $this->getValues_rec($this->plugins, $result);

        return $result;
    }

    /**
     * Recursive helper method for self::getValues().
     *
     * @param array $plugins Array of Form plugins.
     * @param array &$result Result array.
     *
     * @return void
     */
    public function getValues_rec($plugins, &$result)
    {
        $lim = count($plugins);

        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $plugin = $plugins[$i];

            $this->getValues_rec($plugin->plugins, $result);

            if (method_exists($plugin, 'saveValue')) {
                $plugin->saveValue($this, $result);
            }
        }
    }

    /**
     * Sets values.
     *
     * @param array  &$values Values to set.
     * @param string $group   Group name.
     *
     * @return boolean
     */
    public function setValues(&$values, $group = null)
    {
        $empty = null;
        return $this->setValues2($values, $group, $empty);
    }

    /**
     * Helper method for self::setValues().
     *
     * @param array  &$values Values to set.
     * @param string $group   Group name.
     * @param array  $plugins Array of Form plugins.
     *
     * @return boolean
     */
    public function setValues2(&$values, $group = null, $plugins)
    {
        if ($plugins == null) {
            $this->setValues_rec($values, $group, $this->plugins);
        } else {
            $this->setValues_rec($values, $group, $plugins);
        }

        return true;
    }

    /**
     * Recursive helper method for self::setValues().
     *
     * @param array  &$values Values to set.
     * @param string $group   Group name.
     * @param array  $plugins Array of Form plugins.
     *
     * @return void
     */
    public function setValues_rec(&$values, $group, $plugins)
    {
        $lim = count($plugins);

        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $plugin = $plugins[$i];

            $this->setValues_rec($values, $group, $plugin->plugins);

            if (method_exists($plugin, 'loadValue')) {
                $plugin->loadValue($this, $values);
            }
        }
    }

    /**
     * Dump plugins.
     *
     * @param string $msg     Message.
     * @param array  $plugins Not in use.
     *
     * @return void
     */
    public function dumpPlugins($msg, $plugins)
    {
        echo "<pre style=\"background-color: #CFC; text-align: left;\">\n";
        echo "** {$msg} **\n";
        $this->dumpPlugins_rec($this->plugins);
        echo "</pre>";
    }

    /**
     * Recursive helper method for self::dumpPlugins().
     *
     * @param array $plugins Array of Form plugins.
     *
     * @return void
     */
    public function dumpPlugins_rec($plugins)
    {
        $lim = count($plugins);

        for ($i = 0, $cou = $lim; $i < $cou; ++$i) {
            $p = $plugins[$i];
            echo "\n(\n{$p->id}: {$p->parentPlugin}";
            $this->dumpPlugins_rec($p->plugins);
            echo "\n)\n";
        }
    }
}
