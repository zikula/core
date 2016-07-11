<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base plugin class.
 *
 * This is the base class to inherit from when creating new plugins for the Zikula_Form_View framework.
 * Every Form plugin is normally created in a Smarty plugin function file and registered in
 * the Zikula_Form_View framewith with the use of {@link Zikula_Form_View::registerPlugin()}.
 *
 * Member variables in a plugin object is persisted accross different page requests. This means
 * a member variable $this->x can be set on one request and on the next request it will still contain
 * the same value. This probably removes 99% of the use of hidden HTML variables in your web forms.
 * A member variable <i>must</i> be declared in order to be persisted:
 * <code>
 * class MyPlugin inherits Zikula_Form_Plugin
 * {
 *    public $x;
 * }
 * </code>
 *
 * Member variables in a plugin will be assigned automatically from Smarty parameters when the variable
 * and parameter names match. So assume you have a plugin like this:
 * <code>
 * class MyPlugin inherits Zikula_Form_Plugin
 * {
 *    public $y;
 * }
 * </code>
 *
 * Then X will be set to 1234 through this template declaration:
 *
 * <code>
 * {MyPlugin X='1234'}
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
 *   parameters happens <i>before</i> the create event.
 *   This event is only fired the first time the plugin is instantiated,
 *   but not when restored from saved state.
 *
 * - <b>load</b>: Called immediately after the create event. So the plugin is assumed to be fully initialized when the load event
 *   is fired. During the load event the plugin is expected to load values from the render object.
 *
 *   A typical load event handler will just call the loadValue
 *   handler and pass it the values of the render object (to improve reuse). The loadValue method will then take care of the rest.
 *   This is also the place where validators should be added to the list of validators.
 *   Example:
 *   <code>
 *   function load(Zikula_Form_View $view, &$params)
 *   {
 *     $this->loadValue($view, $view->get_template_vars());
 *     $view->addValidator($this);
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
 *   In this event handler you can use {@link Zikula_Form_View::getPluginById()} to fetch other plugins
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
 *
 * @deprecated for Symfony2 Forms
 */
abstract class Zikula_Form_AbstractPlugin implements Zikula_TranslatableInterface
{
    /**
     * Plugin identifier.
     *
     * This contains the identifier for the plugin. You can use this ID in {@link Zikula_Form_View::getPluginById()}
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
    public $visible = true;

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
     * ['title' => 'A tooltip title', onclick => 'doSomething()']
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Zikula_Form_View property.
     *
     * @var Zikula_Form_View
     */
    protected $view;

    /**
     * Name of function to call in form event handler when plugin is loaded.
     *
     * If you need to notify the form event handler when the plugin has been loaded then
     * specify the name of this handler here. The prototype of the function must be:
     * function MyOnLoadHandler(Zikula_Form_View $view, $plugin, $params) where $view is the form render,
     * $plugin is this plugin, and $params are the Smarty parameters passed to the plugin.
     *
     * The data bound handler is called both on postback and first page render.
     *
     * Example:
     * <code>
     * class MyPlugin extends Zikula_Form_Plugin
     * {
     *   function MyPlugin()
     *   {
     *      $this->onDataBound = 'MyLoadHandler';
     *   }
     * }
     *
     * class MyFormHandler extends Zikula_Form_Handler
     * {
     *   function MyLoadHandler(Zikula_Form_View $view, $plugin, $params)
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
    public $plugins = [];

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
     * Translation domain.
     *
     * @var string
     */
    protected $domain;

    /**
     * Request object.
     *
     * @var Zikula_Request_Http
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     */
    public function __construct(Zikula_Form_View $view, &$params)
    {
        $this->view = $view;
        $this->request = $view->getRequest();
    }

    /**
     * Post construction hook
     *
     * @return void
     */
    public function setup()
    {
    }

    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set translation domain.
     *
     * @param string $domain The translation domain.
     *
     * @return void
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Retrieve the Zikula_Form_View property.
     *
     * @return Zikula_Form_View The Zikula_Form_View property.
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Retrieve the plugin identifier (see {@link $id}).
     *
     * @return string The id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Indicates whether or not this plugin should be rendered.
     *
     * @return boolean True if this plugin should be rendered, otherwise false.
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Retrieve the reference to the parent plugin if this is a sub-plugin used inside a block.
     *
     * @return FormHandler The parent plugin.
     */
    public function getParentPlugin()
    {
        return $this->parentPlugin;
    }

    /**
     * Retrieve the HTML attributes.
     *
     * @return array An associative array of attributes to add to the plugin.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve the name of function to call in form event handler when plugin is loaded (see {@link $onDataBound}).
     *
     * @return string The name of the onDataBound function.
     */
    public function getOnDataBound()
    {
        return $this->onDataBound;
    }

    /**
     * Retrieve the reference to sub-plugins.
     *
     * This variable contains an array of references to sub-plugins when this plugin is
     * a block plugin containing other plugins.
     *
     * @return array The reference to sub-plugins.
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Retrieves the temporary storage of the output from renderBegin in blocks.
     *
     * @return string The temporary output storage contents.
     */
    public function getBlockBeginOutput()
    {
        return $this->blockBeginOutput;
    }

    /**
     * Retrieves the volatile indicator (if true, state management in sub-plugins is disabled).
     *
     * @return boolean The volatile indicator.
     */
    public function getVolatile()
    {
        return $this->volatile;
    }

    /**
     * Read Smarty plugin parameters.
     *
     * This is the function that takes care of reading smarty parameters and storing them in the member variables
     * or attributes (all unknown parameters go into the "attribues" array).
     * You can override this for special situations.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function readParameters(Zikula_Form_View $view, &$params)
    {
        $varInfo = get_class_vars(get_class($this));

        // adds the zparameters to the $params if exists
        if (array_key_exists('zparameters', $params)) {
            if (is_array($params['zparameters'])) {
                $params = array_merge($params['zparameters'], $params);
            }
            unset($params['zparameters']);
        }

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
     * This fires once, immediately <i>after</i> member variables have been populated from Smarty parameters
     * (in {@link readParameters()}). Default action is to do nothing.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_View::registerPlugin()
     *
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
    }

    /**
     * Load event handler.
     *
     * This fires once, immediately <i>after</i> the create event. Default action is to do nothing.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_View::registerPlugin()
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
    }

    /**
     * Initialize event handler.
     *
     * Default action is to do nothing. Typically used to add self as validator.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function initialize(Zikula_Form_View $view)
    {
    }

    /**
     * Pre-initialise hook.
     *
     * Fires immediately before {@link initialize()}.
     *
     * @return void
     */
    public function preInitialize()
    {
    }

    /**
     * Post-initialise hook.
     *
     * Fires immediately after {@link initialize()}.
     *
     * @return void
     */
    public function postInitialize()
    {
    }

    /**
     * Decode event handler.
     *
     * Default action is to do nothing.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decode(Zikula_Form_View $view)
    {
    }

    /**
     * Decode event handler for actions that generate a postback event.
     *
     * Default action is to do nothing. Usefull for buttons that should generate events
     * after the plugins have decoded their normal values.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function decodePostBackEvent(Zikula_Form_View $view)
    {
    }

    /**
     * DataBound event handler.
     *
     * Default action is to call onDataBound handler in form event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function dataBound(Zikula_Form_View $view, &$params)
    {
        if ($this->onDataBound != null) {
            $dataBoundHandlerName = $this->onDataBound;
            $view->eventHandler->$dataBoundHandlerName($view, $this, $params);
        }
    }

    /**
     * RenderAttribut event handler.
     *
     * Default action is to do render all attributes in form name="value".
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function renderAttributes(Zikula_Form_View $view)
    {
        $attr = '';
        foreach ($this->attributes as $name => $value) {
            $attr .= " {$name}=\"{$value}\"";
        }

        return $attr;
    }

    /**
     * Render event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function render(Zikula_Form_View $view)
    {
        return '';
    }

    /**
     * RenderBegin event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function renderBegin(Zikula_Form_View $view)
    {
        return '';
    }

    /**
     * RenderContent event handler.
     *
     * Default action is to return the content unmodified.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param string           $content The content to handle.
     *
     * @return string The (optionally) modified content.
     */
    public function renderContent(Zikula_Form_View $view, $content)
    {
        return $content;
    }

    /**
     * RenderEnd event handler.
     *
     * Default action is to return an empty string.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output.
     */
    public function renderEnd(Zikula_Form_View $view)
    {
        return '';
    }

    /**
     * postRender event handler.
     *
     * Default action is to do nothing.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return void
     */
    public function postRender(Zikula_Form_View $view)
    {
    }

    /**
     * RegisterPlugin event handler.
     *
     * Default action is to add the plugin to $this->plugins.
     *
     * @param Zikula_Form_View   $view   Reference to Zikula_Form_View object.
     * @param Zikula_Form_Plugin $plugin A Form plugin to add.
     *
     * @return void
     */
    public function registerPlugin(Zikula_Form_View $view, $plugin)
    {
        $plugin->setDomain($this->domain);

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

        return " id=\"{$id}\"";
    }

    /**
     * Translate.
     *
     * @param string $msgid String to be translated.
     *
     * @return string
     */
    public function __($msgid)
    {
        return __($msgid, $this->domain);
    }

    /**
     * Translate with sprintf().
     *
     * @param string       $msgid  String to be translated.
     * @param string|array $params Args for sprintf().
     *
     * @return string
     */
    public function __f($msgid, $params)
    {
        return __f($msgid, $params, $this->domain);
    }

    /**
     * Translate plural string.
     *
     * @param string $singular Singular instance.
     * @param string $plural   Plural instance.
     * @param string $count    Object count.
     *
     * @return string Translated string.
     */
    public function _n($singular, $plural, $count)
    {
        return _n($singular, $plural, $count, $this->domain);
    }

    /**
     * Translate plural string with sprintf().
     *
     * @param string       $sin    Singular instance.
     * @param string       $plu    Plural instance.
     * @param string       $n      Object count.
     * @param string|array $params Sprintf() arguments.
     *
     * @return string
     */
    public function _fn($sin, $plu, $n, $params)
    {
        return _fn($sin, $plu, $n, $params, $this->domain);
    }
}
