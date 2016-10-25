Zikula Plugin System
--------------------

### Please note that Plugins are deprecated for Core-2.0.

The Zikula plugins system is based on the Zikula_EventManager system.

Plugins can be used for a diverse number of purposes.  For example they could be
as simple as to register a class namespace, or add some plugin search paths for
Smarty.  They can be used to add features to existing modules.  They can be used
to make your modules pluggable, like adding shipping methods to a shopping cart.

There are two kinds of plugins:

1.  System Plugins (don't confuse with system/ modules).
    These are located in plugins/
    Classes are named SystemPlugin_$PluginName_Plugin
    e.g. plugins/Example/Plugin.php (contains
    class SystemPlugin_Example_Plugin extends Zikula_AbstractPlugin

    System plugins are invoked post System::init() before system hooks are
    initialised.

    System plugins might be used for adding template plugins or js libraries
    to be available for then entire Zikula installation.

2.  Module Plugins
    These can be located in system/$modname/plugins or modules/$modame/plugins
    Classes are named ModulePlugin_$Modname_$PluginName_Plugin
    e.g. modules/News/plugins/Example/Plugin.php (contains
    class ModulePlugin_News_Example_Plugin extends Zikula_AbstractPlugin

    Module plugins are only loaded when the ModUtil::loadGeneric is invoked for
    that module.

    Module plugins are there to allow 3rd parties to extend a module or add
    expected functionality.  E.g. a shopping cart could add additional
    shipping methods, or payment gateways using plugins.  Module plugins provide
    local functionality to the module, and (in general) are not aimed at the
    whole Zikula system because they are only initialised when the module, or
    module api is invoked.

Plugin classes are just like event handlers, but inherit from Zikula_AbstractPlugin,
although ultimately they inherit from the same Zikula_AbstractEventHandler abstract.
However the difference is the plugin is a discrete file heirachy where you may
store related files and libraries.

Plugins differ from pure event handlers (instances of Zikula_AbstractEventHandler) in
because Zikula_AbstractEventHandlers cannot be enabled or disabled and consist of just on
file, they are always on and are more low level.

Zikula_AbstractPlugin instances can change state and have a discrete folder space.  The
Zikula_AbstractPlugin abstract class provides many useful conveniences like translation,
automatic domain binding etc.

Uses for plugins
----------------
Providing php, js, css or image libraries.
Extending functionality of the zikula core system wide or of modules individually.

Architecture
------------
Plugins provide a rather free system.  You only need what you need.  The only
compulsory aspect is the Plugin.php which is the heart of the plugin.

Plugins provide the following methods:

initialize()

get*() methods.

install()
upgrade()
uninstall()
enable()
disable()

The Plugin class provides the following hooks
preInitialize()
postInitialize()

preInstall()
postInstall()

preUpgrade()
postUpgrade()

preInstall()
postInstall()

postEnable()

postDisable()

If your plugin requires localisation, the usual folder structure is required inside
the plugin.  Please note the required domain is:
systemplugin_$name or
moduleplugin_$modname_$pluginname

To access translation just use the shortcuts `$this->__()` etc.

So the POT file would be located in the plugin's main folder in
locale/$domain.pot

Plugins DO NOT support DBUtil or the legacy tables.php based DB modelling.  You MUST
use Doctrine models if you require persistence.

PLUGIN (modvar) VARIABLES
----------------
Please use $this->serviceId to get the correct name for modvars. It will
produce the following results automatically
    ModUtil::set/getVar("systemplugin.$pluginname", ...);
    ModUtil::set/getVar("moduleplugin.$modname.$pluginname", ...);

e.g.

ModUtil::set/getVar($this->serviceId, 'myvar');

RENDERING
---------
If you need to produce templated output, please create a templates/ folder
and store templates there.  You will need to specify the location directly:

e.g.
    // module plugin
    $view = Zikula_View_Plugin::getModulePluginInstance($this->moduleName, $this->pluginName);
    $view->fetch('myview.tpl');

    // system plugin
    $view = Zikula_View_Plugin::getSystemPluginInstance($this->pluginName);
    $view->fetch('myview.tpl');

    Please note this will set the correct domain, in templates you
    DO NOT NEED domain= inside gettext calls.

If you get a render instance, the plugin path will be automatically added as
templates/plugins inside the plugin directory.

Please note you can also use a Controller.php instance to get the view.  Controllers
(explained below) has access to all the nice Zikula_AbstractController conveniences.  The
controller method would return the renderer output which you can pass back.

instead of:
    $event->setData($view->fetch('anotherfunction.tpl'));
    $event->stopPropagation();

One could do:
    $controller = new SystemPlugin_Example_Controller($this->serviceManager);
    $event->setData($controller->someview());
    $event->stopPropagation();

This is nice because all renderer domains etc are preconfigured and you have access
to the same Zikula_AbstractController conveniences like $this->__().

PLUGIN ADMINISTRATIVE CONFIGURATION
-----------------------------------
Sometimes is it necessary to provide a configuration screen for plugins.  This admin
will be available when clicking the settings icon in the list of plugins available at
Admin -> Modules -> Module Plugins/System Plugins.

Firstly, in order to make your plugin configurable, you must implement the
Zikula_Plugin_ConfigurableInterface interface which will requires one method called
getConfigurationController() which should return an instance of the controller
there is an example in the DocBlock of the interface.

Next create a controller an place in the plugins lib/$PluginName/Controller.php

    class SystemPlugin_Example_Controller extends Zikula_Controller_AbstractPlugin

or for module plugins something like

    class ModulePlugin_ExampleMod_ExamplePlugin_Controller extends Zikula_Controller_AbstractPlugin

The method that will be is 'configure()':

    class SystemPlugin_SwiftMailer_Controller extends Zikula_Controller_AbstractPlugin
    {
        public function configure()
        {
            return $this->view->fetch('configure.tpl');
        }
    }

There is no need for a security check here because this is only accessible from inside the
administration interface in the first place.

If you need multiple administration screens this can be accomplished by creating links:
For System Plugins:
 ?module=Modules&type=adminplugin&func=dispatch&_plugin=<PLUGINNAME>&_action=<ACTIONNAME>
For Module Plugins:
 ?module=Modules&type=adminplugin&func=dispatch&_module=<MODULENAME>&_plugin=<PLUGINNAME>&_action=<ACTIONNAME>

