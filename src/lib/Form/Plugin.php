<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Base plugin class.
 *
 * This is the base class to inherit from when creating new plugins for the pnForm framework.
 * Every pnForm plugin is normally created in a Smarty plugin function file and registered in
 * the pnForm framewith with the use of {@link pnFormRender::pnFormRegisterPlugin()}.
 *
 * Member variables in a plugin object is persisted accross different page requests. This means
 * a member variable $this->x can be set on one request and on the next request it will still contain
 * the same value. This probably removes 99% of the use of hidden HTML variables in your web forms.
 * A member variable <i>must</i> be declared in order to be persisted:
 * <code>
 * class MyPlugin inherits pnFormPlugin
 * {
 *    var $x;
 * }
 * </code>
 *
 * Member variables in a plugin will be assigned automatically from Smarty parameters when the variable
 * and parameter names match. So assume you have a plugin like this:
 * <code>
 * class MyPlugin inherits pnFormPlugin
 * {
 *    var $y;
 * }
 * </code>
 *
 * Then X will be set to 1234 through this template declaration:
 *
 * <code>
 * <!--[MyPlugin X="1234"]-->
 * </code>
 *
 * A registered plugin will be notified of various events that happens during it's life-cycle.
 * When a specific event occurs then the corresponding event handler (class method) will be executed. Handlers
 * are named exactly like their events - this is how the framework knows which methods to call.
 *
 * The list of events is:
 * - <b>create</b>: Similar to a constructor since it is called directly after the plugin has been created.
 *   In this event handler you should set the various member variables your plugin requires. You can access
 *   Smarty parameters through the $params object. The automatic setting of member variables from Smarty
 *   parameters happens <i>after</i> the create event.
 *   This event is only fired the first time the plugin is instantiated,
 *   but not when restored from saved state.
 *
 * - <b>load</b>: Called immediately after member variables has been set from their Smarty parameters. So
 *   the plugin is assumed to be fully initialized when the load event is fired. During the load event the plugin
 *   is expected to load values from the render object.
 *
 *   A typical load event handler will just call the loadValue
 *   handler and pass it the values of the render object (to improve reuse). The loadValue method will then take care of the rest.
 *   This is also the place where validators should be added to the list of validators.
 *   Example:
 *   <code>
 *   function load(&$render, &$params)
 *   {
 *     $this->loadValue($render, $render->get_template_vars());
 *     $render->addValidator($this);
 *   }
 *   </code>
 *   This event is only fired the first time the plugin is instantiated,
 *   but not when restored from saved state.
 *
 * - <b>initialize</b>: this event is the opposite of the create/load event pair. It fires when a plugin
 *   has been restored from a postback (and before then decode event).
 *
 * - <b>decode</b>: this event is fired on postback in order to let the plugin decode the HTTP POST values
 *   sent by the browser. It is left to the plugin to decide where to store the decode data.
 *
 * - <b>dataBound</b>: this event is fired when plugin is loaded and ready - both on postback and the
 *   initial page display.
 *
 * - <b>render</b>: this event is fired when the plugin is required to render itself based on the data
 *   it got through the previous events. This function is only called on Smarty function plugins.
 *   The event handler is supposed to return the rendered output.
 *
 * - <b>renderBegin</b>: this event is for Smarty block plugins only. It is fired in order to allow
 *   the plugin to render something before the plugins contained within it.
 *
 * - <b>renderContent</b>: this event is for Smarty block plugins only. It is fired in order to allow
 *   the plugin to modify content renderes by the plugins contained within it.
 *
 * - <b>renderEnd</b>: this event is for Smarty block plugins only. It is fired in order to allow
 *   the plugin to render something after the plugins contained within it.
 *
 * - <b>postRender</b>: this event is fired after all rendering is done <i>for all plugins on the page</i>.
 *   In this event handler you can use {@link pnFormRender::pnFormGetPluginById()} to fetch other plugins
 *   and read/modify their data.
 *
 * Most events on one plugin happens before the next plugin is loaded (except the postRender event). So for two
 * plugins A and B you would get the event sequence (assuming B is placed after A in the Smarty template):
 * - A::create
 * - A::load
 * - ...
 * - A:render
 * - B::create
 * - B::load
 * - ...
 * - B:render
 * - A::postRender
 * - B::postRender
 */
class Form_Plugin
{
    /**
     * Plugin identifier.
     *
     * This contains the identifier for the plugin. You can use this ID in {@link pnFormRender::pnFormGetPluginById()}
     * as well as in JavaScript where it should be set on the HTML elements (this does although depend on the plugin
     * implementation).
     *
     * Do <i>not</i> change this variable!
     *
     * @var string
     */
    public $id;

    /**
     * Specifies whether or not a plugin should be rendered.
     *
     * @var boolean
     */
    public $visible;

    /**
     * Reference to parent plugin if used inside a block.
     *
     * @var &FormHandler
     */
    public $parentPlugin;

    /**
     * HTML attributes.
     *
     * Associative array of attributes to add to the plugin. For instance:
     * array('title' => 'A tooltip title', onclick => 'doSomething()')
     *
     * @var array
     */
    public $attributes;

    /**
     * Name of function to call in form event handler when plugin is loaded.
     *
     * If you need to notify the form event handler when the plugin has been loaded then
     * specify the name of this handler here. The prototype of the function must be:
     * function MyOnLoadHandler(&$render, $plugin, $params) where $render is the form render,
     * $plugin is this plugin, and $params are the Smarty parameters passed to the plugin.
     *
     * The data bound handler is called both on postback and first page render.
     *
     * Example:
     * <code>
     * class MyPlugin extends FormPlugin
     * {
     *   function MyPlugin()
     *   {
     *      $this->onDataBound = 'MyLoadHandler';
     *   }
     * }
     *
     * class MyFormHandler extends Form_Handler
     * {
     *   function MyLoadHandler(&$render, $plugin, $params)
     *   {
     *     // Do stuff here
     *   }
     * }
     * </code>
     *
     * The name "dataBound" was chosen to avoid clashes with the "load" event.
     * 
     * @var string
     */
    public $onDataBound;

    /**
     * Reference to sub-plugins.
     *
     * This variable contains an array of references to sub-plugins when this plugin is
     * a block plugin containing other plugins.
     *
     * @var array
     */
    public $plugins;

    /**
     * Temporary storage of the output from renderBegin in blocks.
     * 
     * @var string
     * @internal
     */
    public $blockBeginOutput;

    /**
     * Volatile indicator (disables state management in sub-plugins).
     * 
     * @var boolean
     * @internal
     */
    public $volatile;

    /**
     * Constructor.
     * 
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     */
    public function __construct(&$render, &$params)
    {
        $this->plugins = array();
        $this->attributes = array();
        $this->visible = true;
    }

    /**
     * Read Smarty plugin parameters.
     *
     * This is the function that takes care of reading smarty parameters and storing them in the member variables
     * or attributes (all unknown parameters go into the "attribues" array).
     * You can override this for special situations.
     * 
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     * 
     * @return void
     */
    public function readParameters(&$render, &$params)
    {
        $varInfo = get_class_vars(get_class($this));

        // Iterate through all params: place known params in member variables and the rest in the attributes set
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $varInfo)) {
                $this->$name = $value;
            } else {
                $this->attributes[$name] = $value;
            }
        }
    }

    /**
     * Create event handler.
     *
     * Default action is to do nothing.
     *
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     * 
     * @return void
     */
    public function create(&$render, &$params)
    {
    }

    /**
     * Load event handler.
     *
     * Default action is to do nothing.
     *
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function load(&$render, &$params)
    {
    }

    /**
     * Initialize event handler.
     *
     * Default action is to do nothing. Typically used to add self as validator.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return void
     */
    public function initialize(&$render)
    {
    }

    /**
     * Decode event handler.
     *
     * Default action is to do nothing.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return void
     */
    public function decode(&$render)
    {
    }

    /**
     * Decode event handler for actions that generate a postback event.
     *
     * Default action is to do nothing. Usefull for buttons that should generate events
     * after the plugins have decoded their normal values.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return void
     */
    public function decodePostBackEvent(&$render)
    {
    }

    /**
     * DataBound event handler.
     *
     * Default action is to call onDataBound handler in form event handler.
     *
     * @param Form_Render &$render Reference to Form render object.
     * @param array       &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function dataBound(&$render, &$params)
    {
        if ($this->onDataBound != null) {
            $dataBoundHandlerName = $this->onDataBound;
            $render->eventHandler->$dataBoundHandlerName($render, $this, $params);
        }
    }

    /**
     * RenderAttribut event handler.
     * 
     * Default action is to do render all attributes in form name="value".
     * 
     * @param Form_Render &$render Reference to Form render object.
     * 
     * @return string The rendered output.
     */
    public function renderAttributes(&$render)
    {
        $attr = '';
        foreach ($this->attributes as $name => $value) {
            $attr .= " $name=\"$value\"";
        }

        return $attr;
    }

    /**
     * Render event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return string The rendered output.
     */
    public function render(&$render)
    {
        return '';
    }

    /**
     * RenderBegin event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return string The rendered output.
     */
    public function renderBegin(&$render)
    {
        return '';
    }

    /**
     * RenderContent event handler.
     *
     * Default action is to return the content unmodified.
     *
     * @param Form_Render &$render Reference to Form render object.
     * @param string      $content The content to handle.
     *
     * @return string The (optionally) modified content.
     */
    public function renderContent(&$render, $content)
    {
        return $content;
    }

    /**
     * RenderEnd event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return string The rendered output.
     */
    public function renderEnd(&$render)
    {
        return '';
    }

    /**
     * postRender event handler.
     *
     * Default action is to do nothing.
     *
     * @param Form_Render &$render Reference to Form render object.
     *
     * @return void
     */
    public function postRender(&$render)
    {
    }

    /**
     * RegisterPlugin event handler.
     * 
     * Default action is to add the plugin to $this->plugins.
     * 
     * @param Form_Render &$render Reference to Form render object.
     * @param Form_Plugin $plugin  A Form plugin to add.
     * 
     * @return void
     */
    public function registerPlugin(&$render, $plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * Utility function to generate HTML for ID attribute.
     *
     * Generate id="..." for use in the plugin's render methods.
     *
     * This function ignores automatically created IDs (those named "plgNNN") and will
     * return an empty string for these.
     *
     * @param string $id The ID of the item.
     * 
     * @return string The generated HTML.
     */
    public function getIdHtml($id = null)
    {
        if ($id == null) {
            $id = $this->id;
        }

        if (preg_match('/^plg[0-9]+$/', $id)) {
            return '';
        }

        return " id=\"$id\"";
    }
}
