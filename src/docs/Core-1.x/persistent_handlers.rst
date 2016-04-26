PERSISTENT EVENTHANDLERS
------------------------
Zikula normally only loads modules which need to be accessed, so modules would
not generally register their event listeners  However, there are times when a module
or module's plugin needs to listen for an event that occured somewhere else in the
system.  For example, 'user.delete' so a module can handle any reference to a
delete users ID.  Modules can register persistent event handlers with the
EventUtil::register*() API:

    EventUtil::registerPersistentModuleHandler($moduleName, $eventName, $callable)
    EventUtil::unregisterPersistentModuleHandler($moduleName, $eventName, $callable)
    EventUtil::registerPersistentPluginHandler($moduleName, $pluginName, $eventName, $callable)
    EventUtil::unregisterPersistentPluginHandler($moduleName, $pluginName, $eventName, $callable)

Callables should look like this `['ClassName', 'methodName']` which represents ClassName::MethodName($event)

The only restriction is the handlers must be PHP callables of static class methods.

This methodology can also be used as a better way of providing the legacy
'API plugins' once seen, like Multihook needles.  Imagine the following alternative
with MultiHook (this example is fictitous):

Multihook normally scans the entire file system looking for modules that implement
multihookapis.  Instead of this, Multihook can just issue a 'multihook.get_providers'
event.  Listeners would simply return an array of class names or callables all of
which should implement the Mutlihook Needle Interface/Abstract.  Multihook can then
instanciate these (checking each instance through reflection to ensure it is an
instanceof Multihook_NeedleApi).

abstract class Multihook_NeedleApi
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }

    abstract public function filter($input);
}

MyModule should now register a persistent listener for 'module.multihook.get_providers'
using the following code in Installer.php

    EventUtil::registerPersistentModuleHandler('MyModule', 'module.multihook.get_providers', ['MyModule_Listeners', 'getProvider']);

In a separate file lib/MyModule/Listeners.php you place the following code (this is what receives the $events).

    class MyModule_Listeners
    {
        public static function getProvider(Zikula_Event $event)
        {
            $event->data = array_merge($event->data, ['MyModule_Needles_Foo']);
        }
    }

Back to the Multihook module.  It's implementation could look something like this:

    // create and dispatch the event
    $event = \Zikula\Core\Event\GenericEvent();
    $classes = $eventManager->dispatch('module.multihook.get_providers', $event)->getData();

    // now we got back any results we can process them like this
    foreach ($classes as $class) {
        $needle = new $class();
        if (!$needle instanceof MultiHook_NeedleApi) {
            //.. error
        }

        $output = $needle->filter($output);
    }


