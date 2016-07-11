<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * User interaction handler for Form system.
 *
 * This class is the main entry point for using the Form system. It is expected to be used in Zikula's
 * controllers, like this:
 * <code>
 * public function create($args)
 * {
 *   // Create instance of Zikula_Form_View
 *   $view = FormUtil::newForm('howtoforms');
 *
 *   // Execute form using supplied template and event handler
 *   return $view->execute('modname_user_create.tpl', new Modname_Zikula_Form_Handler_Create());
 * }
 * </code>
 * See tutorials elsewhere for general introduction to Form.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_View extends Zikula_View
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
     * Array of persistent state data through the form processing.
     *
     * @var array
     * @internal
     */
    public $data;

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
     * @var integer
     * @internal
     */
    public $idCount;

    /**
     * Reference to the main user code event handler.
     *
     * @var Zikula_Form_AbstractHandler
     * @internal
     */
    public $eventHandler;

    /**
     * EntityManager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Error message has been set.
     *
     * @var boolean
     * @internal
     */
    public $errorMsgSet;

    /**
     * Set to true if Zikula_Form_Vew::Redirect was called. Means no HTML output should be returned.
     *
     * @var boolean
     * @internal
     */
    public $redirected;

    /**
     * @var Zikula\Core\ModUrl
     */
    public $redirectTarget;

    /**
     * Unique form ID.
     *
     * @var string
     */
    protected $formId;

    /**
     * Constructor.
     *
     * Use FormUtil::newForm() instead of instantiating Zikula_Form_View directly.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param string                $module         Module name.
     * @param integer               $caching        Caching flag (not used - just for e_strict).
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $module, $caching = null)
    {
        // override behaviour of anonymous sessions
        SessionUtil::requireSession();

        // construct and use the available methods
        parent::__construct($serviceManager, $module, false);
        $this->addPluginDir('lib/legacy/viewplugins/formplugins', false);
        $this->setCaching(Zikula_View::CACHE_DISABLED);

        // custom Form setup
        $this->idCount = 1;
        $this->errorMsgSet = false;
        $this->plugins = [];
        $this->blockStack = [];
        $this->redirected = false;

        $this->validators = [];
        $this->validationChecked = false;
        $this->_isValid = null;

        $this->initializeState();
        $this->initializeStateData();
        $this->initializeIncludes();
    }

    /**
     * Get form id.
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set the form Id.
     *
     * @param string $formId Form ID.
     *
     * @return void
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * Return entitymanager.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Set entitymanager.
     *
     * @param object $entityManager Entity manager to set.
     *
     * @return void
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Main event loop handler.
     *
     * This is the function to call instead of the normal $view->fetch(...).
     *
     * @param boolean                     $template     Name of template file.
     * @param Zikula_Form_AbstractHandler $eventHandler Instance of object that inherits from Zikula_Form_AbstractHandler.
     *
     * @return mixed False on errors, true on redirects, and otherwise it returns the HTML output for the page.
     */
    public function execute($template, Zikula_Form_AbstractHandler $eventHandler)
    {
        if (!$eventHandler instanceof Zikula_Form_AbstractHandler) {
            throw new Zikula_Exception_Fatal('Form handlers must inherit from Zikula_Form_AbstractHandler.');
        }

        // Save handler for later use
        $this->eventHandler = $eventHandler;
        $this->eventHandler->setView($this);
        $this->eventHandler->setEntityManager($this->entityManager);
        $this->eventHandler->setRequest($this->request);
        $this->eventHandler->setDomain($this->domain);
        $this->eventHandler->setName($this->getModuleName());
        $this->eventHandler->setup();
        $this->eventHandler->preInitialize();

        if ($this->isPostBack()) {
            if (!SecurityUtil::validateCsrfToken($this->request->request->filter('csrftoken', '', FILTER_SANITIZE_STRING), $this->serviceManager)) {
                return LogUtil::registerAuthidError();
            }

            // retrieve form id
            $formId = $this->request->request->filter("__formid", '', FILTER_SANITIZE_STRING);
            $this->setFormId($formId);

            $this->decodeIncludes();
            $this->decodeStateData();
            $this->decodeState();

            if ($this->eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }

            // if we get this far, the form processed correctly and we can GC the session
            unset($_SESSION['__formid'][$this->formId]);

            $this->eventHandler->postInitialize();

            // (no create event)
            $this->initializePlugins(); // initialize event
            $this->decodePlugins(); // decode event
            $this->decodePostBackEvent(); // Execute optional postback after plugins have read their values
        } else {
            $this->setFormId(uniqid('f'));
            if ($this->eventHandler->initialize($this) === false) {
                return $this->getErrorMsg();
            }
            $this->eventHandler->postInitialize();
        }

        // render event (calls registerPlugin)
        $this->assign('__formid', $this->formId);
        $output = $this->fetch($template);

        if ($this->hasError()) {
            return $this->getErrorMsg();
        }

        // Check redirection at this point, ignore any generated HTML if redirected is required.
        // We cannot skip HTML generation entirely in case of System::redirect since there might be
        // some relevant code to execute in the plugins.
        if ($this->redirected) {
            // only reach this point if redirectTarget is a Zikula\Core\ModUrl
            return new RedirectResponse(System::normalizeUrl($this->redirectTarget->getUrl()));
        }

        return $output;
    }

    /**
     * Register a plugin.
     *
     * This method registers a plugin used in a template. Plugins must beregistered to be used in Zikula_Form_View
     * (unlike Smarty plugins). The register call must be done inside the Smarty plugin function in a
     * Smarty plugin file. Use like this:
     * <code>
     * // In file "function.myplugin.php"
     *
     * // Form plugin class
     * class MyPlugin extends Zikula_Form_AbstractPlugin
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
     * Use Zikula_Form_View::registerBlock() instead.
     *
     * See also all the function.formXXX.php plugins for examples.
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array   &$params    Parameters passed from the Smarty plugin function.
     * @param boolean $isBlock Indicates whether the plugin is a Smarty block or a Smarty function (internal).
     *
     * @return string Returns what the render() method of the plugin returns.
     *
     * @throws InvalidArgumentException Thrown if the plugin is not an instance of Zikula_Form_AbstractPlugin.
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
            if (!$plugin instanceof Zikula_Form_AbstractPlugin) {
                throw new InvalidArgumentException(__f('Plugin %s must be an instance of Zikula_Form_AbstractPlugin', $pluginName));
            }
            $plugin->setDomain($this->getDomain());
            $plugin->setup();

            // Make sure to store ID and render reference in plugin
            $plugin->id = $id;

            if ($stackCount > 0) {
                $plugin->parentPlugin = $this->blockStack[$stackCount - 1];
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
     * Use this like {@link Zikula_Form_View::registerPlugin} but for Smarty blocks instead of Smarty plugins.
     * <code>
     * // In file "block.myblock.php"
     *
     * // Form plugin class (also used for blocks)
     * class MyBlock extends Zikula_Form_AbstractPlugin
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
        $plugin = $this->blockStack[count($this->blockStack) - 1];
        $plugin->blockBeginOutput = $output;
    }

    /**
     * RegisterBlockEnd.
     *
     * @param string $pluginName Full class name of the plugin to register.
     * @param array  &$params    Parameters passed from the block function.
     * @param string $content The block content.
     *
     * @internal
     * @return string The rendered content.
     */
    public function registerBlockEnd($pluginName, &$params, $content)
    {
        $plugin = $this->blockStack[count($this->blockStack) - 1];
        array_pop($this->blockStack);

        $output = null;

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
     * @param integer $id Plugin ID.
     *
     * @return Zikula_Form_AbstractPlugin|null
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
     * @param object  $plugin Plugin.
     * @param integer $id     Plugin ID.
     *
     * @return Zikula_Form_AbstractPlugin|null
     */
    public function getPluginById_rec($plugin, $id)
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
        echo $msg;
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
     * @param object $validator Validator to add.
     *
     * @return void
     */
    public function addValidator($validator)
    {
        $this->validators[] = $validator;
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

        return $this->_isValid;
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
        $this->_isValid = true;

        $lim = count($this->validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->validators[$i]->validate($this);
            $this->_isValid = $this->_isValid && $this->validators[$i]->isValid;
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
        $this->_isValid = true;

        $lim = count($this->validators);
        for ($i = 0; $i < $lim; ++$i) {
            $this->validators[$i]->clearValidation($this);
        }
    }

    // --- Public state management ---

    /**
     * Sets a state.
     *
     * @param string $region  State region.
     * @param string $varName State variable name.
     * @param mixed  &$varValue State variable value.
     *
     * @return void
     */
    public function setState($region, $varName, &$varValue)
    {
        if (!isset($this->state[$region])) {
            $this->state[$region] = [];
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
     * Register a plugin error.
     *
     * Example:
     * <code>
     * function handleCommand(...)
     * {
     *   if (... plugin value error ...)
     *     return $view->setPluginErrorMsg('title', 'Value of title not valid');
     * }
     * </code>
     *
     * @param string $id        Plugin identifier.
     * @param string $msg       Error message.
     * @param array  $newvalues New values to set on the plugin object (optional).
     *
     * @return false
     */
    public function setPluginErrorMsg($id, $msg, $newvalues = [])
    {
        $plugin = $this->getPluginById($id);
        $plugin->setError($msg);

        $objInfo = get_class_vars(get_class($plugin));

        // iterate through the newvalues: place known params in member variables
        foreach ($newvalues as $name => $value) {
            if (array_key_exists($name, $objInfo)) {
                $plugin->$name = $value;
            }
        }

        return false;
    }

    /**
     * Register an error.
     *
     * Example:
     * <code>
     * function handleCommand(...)
     * {
     *   if (... it did not work ...)
     *     return $view->setErrorMsg('Operation X failed due to Y');
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
            include_once 'lib/legacy/viewplugins/insert.getstatusmsg.php';
            $args = [];

            return smarty_insert_getstatusmsg($args, $this);
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
     * function initialize(Zikula_Form_View $view)
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
     * @param Zikula\Core\ModUrl|string $url Url.
     *
     * @return boolean True if redirected successfully, otherwise false.
     */
    public function redirect($url)
    {
        $this->redirected = true;

        if ($url instanceof Zikula\Core\ModUrl) {
            $this->redirectTarget = $url;
            // return and complete lifecycle events. redirect will complete in the `execute` method
            return true;
        } else {
            // for BC: if only url is provided, send redirect immediately, discarding all future lifecycle changes
            $response =  new RedirectResponse(System::normalizeUrl($url));
            $response->send();
            exit;
        }
    }

    /**
     * Redirect status check.
     *
     * @return boolean True if redirected, otherwise false.
     */
    public function isRedirected()
    {
        return $this->redirected;
    }

    // --- Event handling ---

    /**
     * Get postback reference.
     *
     * Call this method to get a piece of code that will generate a postback event. The returned JavaScript code can
     * be called at any time to generate the postback. The plugin that receives the postback must implement
     * a function "raisePostBackEvent(Zikula_Form_View $view, $eventArgument)" that will handle the event.
     *
     * Example (taken from the {@link Zikula_Form_Plugin_ContextMenu_Item} plugin):
     * <code>
     * function render(Zikula_Form_View $view)
     * {
     *   $click = $view->getPostBackEventReference($this, $this->commandName);
     *   $url = 'javascript:' . $click;
     *   $title = $view->translateForDisplay($this->title);
     *   $html = "<li><a href=\"$url\">$title</a></li>";
     *
     *   return $html;
     * }
     *
     * function raisePostBackEvent(Zikula_Form_View $view, $eventArgument)
     * {
     *   $args = ['commandName' => $eventArgument, 'commandArgument' => null];
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
        $handlerClass = &$this->eventHandler;

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
        $this->includes = [];
    }

    /**
     * Get the includes as text.
     *
     * @return string Encoded includes.
     */
    public function getIncludesText()
    {
        return $this->includes;
    }

    /**
     * Save includes to session.
     *
     * @return string Empty string.
     */
    public function getIncludesHTML()
    {
        $_SESSION['__forms'][$this->formId]['includes'] = $this->getIncludesText();

        return '';
    }

    /**
     * Decode includes from session.
     *
     * @return void
     */
    public function decodeIncludes()
    {
        if (!isset($_SESSION['__forms'][$this->formId]['includes'])) {
            throw new Exception('Failed to decode form includes - this should not have happened');
        }
        $this->includes = $_SESSION['__forms'][$this->formId]['includes'];

        // Load the third party plugins only
        foreach ($this->includes as $includeFilename => $dummy) {
            if (strpos($includeFilename, 'config' . DIRECTORY_SEPARATOR)
             || strpos($includeFilename, 'modules' . DIRECTORY_SEPARATOR)) {
                require_once $includeFilename;
            }
        }
    }

    // --- Add nonce ---

    /**
     * CSRF protection
     *
     * @return string HTML input field.
     */
    public function getCsrfTokenHtml()
    {
        $key = SecurityUtil::generateCsrfToken($this->serviceManager);
        $html = "<input type=\"hidden\" name=\"csrftoken\" value=\"{$key}\" id=\"FormCsrfToken_{$this->formId}\" />";

        return $html;
    }

    // --- Persistent state data ---

    /**
     * Initializes the data memory.
     *
     * @return void
     */
    public function initializeStateData()
    {
        $this->data = [];
    }

    /**
     * Get a data field or all the persistent data.
     *
     * @param string $key Key field to be retrieved.
     *
     * @return mixed One or all the persistent data.
     */
    public function getStateData($key = null)
    {
        if ($key) {
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }

        return $this->data;
    }

    /**
     * Set a persistent data field.
     *
     * @param string $key   ID of the value to set in the state data array.
     * @param mixed  $value Data to set.
     *
     * @return $this
     */
    public function setStateData($key, $value)
    {
        if ($key) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Save data to session.
     *
     * @return string Empty string.
     */
    public function getStateDataHTML()
    {
        $_SESSION['__forms'][$this->formId]['data'] = $this->getStateData();

        return '';
    }

    /**
     * Decode data from session.
     *
     * @return void
     */
    public function decodeStateData()
    {
        if (!isset($_SESSION['__forms'][$this->formId]['data'])) {
            throw new Exception('Failed to decode form includes - this should not have happened');
        }
        $this->data = $_SESSION['__forms'][$this->formId]['data'];
    }

    // --- State management ---

    /**
     * Initialize state memory.
     *
     * @return void
     */
    public function initializeState()
    {
        $this->state = [];
    }

    /**
     * Get saved states as text.
     *
     * @return text Encoded states.
     */
    public function getStateText()
    {
        $pluginState = $this->getPluginState();
        $this->setState('Zikula_Form_View', 'plugins', $pluginState);

        return $this->state;
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
        $state = [];

        $lim = count($plugins);
        for ($i = 0; $i < $lim; ++$i) {
            $plugin = &$plugins[$i];

            // Handle sub-plugins special and clear stuff not to be serialized
            $plugin->parentPlugin = null;
            $subPlugins = $plugin->plugins;
            $plugin->plugins = null;

            $varInfo = get_class_vars(get_class($plugin));

            $pluginState = [];
            foreach ($varInfo as $name => $value) {
                $pluginState[] = $plugin->$name;
            }

            $state[] = [get_class($plugin), $pluginState, $this->getPluginState_rec($subPlugins)];

            $plugin->plugins = &$subPlugins;
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
        $_SESSION['__forms'][$this->formId]['state'] = $this->getStateText();

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
        if (!isset($_SESSION['__forms'][$this->formId]['state'])) {
            throw new Exception('Failed to decode form state - this should not have happened.');
        }

        $this->state = $_SESSION['__forms'][$this->formId]['state'];

        // I don't know why, but the formid array doesnt GC unless this is here - drak
        unset($_SESSION['__forms'][$this->formId]);
        $this->plugins = &$this->decodePluginState();

        //$this->dumpPlugins("Decoded state", $this->plugins);
    }

    /**
     * Decode plugin state.
     *
     * @return array decoded states.
     */
    public function &decodePluginState()
    {
        $state = $this->getState('Zikula_Form_View', 'plugins');
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
        $plugins = [];

        foreach ($state as $pluginInfo) {
            $pluginType = $pluginInfo[0];
            $pluginState = $pluginInfo[1];
            $subState = $pluginInfo[2];

            $dummy = [];
            $plugin = new $pluginType($this, $dummy);

            $vars = array_keys(get_class_vars(get_class($plugin)));

            $varCount = count($vars);
            if ($varCount != count($pluginState)) {
                return LogUtil::registerError(__f("Cannot restore Zikula_Form_View plugin of type '%s' since stored and actual number of member vars differ", $pluginType));
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
        $eventTarget = $this->request->request->get('FormEventTarget', 1);
        $eventArgument = $this->request->request->get('FormEventArgument', 1);

        if ($eventTarget != '') {
            $targetPlugin = $this->getPluginById($eventTarget);
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
     * [
     *     'title'    => 'The posted title',
     *     'text'     => 'The posted text',
     *     'servings' => 4
     * ]
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
     * [
     *     'A' => ['title'    => 'The posted title',
     *             'text'     => 'The posted text'],
     *     'servings' => 4
     * ]
     * </code>
     *
     * @return mixed
     */
    public function getValues()
    {
        $result = [];

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
     * @param string $group Group name.
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
    public function setValues2(&$values, $group = null, $plugins = null)
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
