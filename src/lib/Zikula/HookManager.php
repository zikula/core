<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_HookManager class.
 */
class Zikula_HookManager
{
    /**
     * Zikula_ServiceManager instance.
     *
     * @var object
     */
    protected $serviceManager;

    /**
     * Zikula_EventManager instance.
     *
     * @var object
     */
    protected $eventManager;

    /**
     * Zikula_HookManager_StorageInterface.
     *
     * @var object
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager               $serviceManager Instance of ServiceManager.
     * @param Zikula_EventManager                 $eventManager   Instance of EventManager.
     * @param Zikula_HookManager_StorageInterface $storage        Instance of StorageInterface.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, Zikula_EventManager $eventManager, Zikula_HookManager_StorageInterface $storage)
    {
        $this->serviceManager = $serviceManager;
        $this->eventManager = $eventManager;
        $this->storage = $storage;
    }

    /**
     * Notify hooks event handlers.
     *
     * Expects key 'hooktype' to exists in the event arguments which will be used to
     * redispatch the event to handlers registered against the value contained in
     * 'hooktype'.
     *
     * @param Zikula_Event $event Event object.
     *
     * @return object Event object.
     */
    public function notify(Zikula_Event $event)
    {
        $hooks = $this->notifySetup($event);
        $value = $event->getData();
        foreach ($hooks as $hook) {
            $hookEvent = new Zikula_Event($hook['hookname'], $event->getSubject(), $event->getArgs(), $value);
            $value = $this->eventManager->notify($hookEvent)->getData();
        }
        
        $event->setData($value);
        return $event;
    }

    /**
     * NotifyUntil hooks event handlers.
     *
     * Expects key 'hooktype' to exists in the event arguments which will be used to
     * redispatch the event to handlers registered against the value contained in
     * 'hooktype'.
     *
     * @param Event $event Event object.
     *
     * @return object Event object.
     */
    public function notifyUntil(Zikula_Event $event)
    {
        $hooks = $this->notifySetup($event);
        $value = $event->getData();
        foreach ($hooks as $hook) {
            $hookEvent = new Zikula_Event($hook['hookname'], $event->getSubject(), $event->getArgs(), $value);
            $value = $this->eventManager->notify($hookEvent)->getData();
            if ($event->hasNotified()) {
                break;
            }
        }

        $event->setData($value);
        return $event;
    }

    /**
     * Setup before notification of hook.
     *
     * Performs validation and returns the hooks associated for this event.
     *
     * @return array
     */
    protected function notifySetup($event)
    {
        if (!$event->getSubject() instanceof Zikula_HookSubject) {
            throw new InvalidArgumentException('Event subject must be an instance of Zikula_HookSubject.');
        }

        return $this->getHookBindings($event->getSubject()->getType(), $event->getSubject()->getWho());
    }

    /**
     * Load up all the Hook events registered in the database.
     *
     * @return void
     */
    public function registerHooksRuntime()
    {
        foreach ($this->getHooks() as $hook) {
            // if the service doesn't exist, register it with service manager.
            if (!$this->serviceManager->hasService($hook['servicename'])) {
                $this->serviceManager->registerService(new Zikula_ServiceManager_Service($hook['servicename'], new Zikula_ServiceManager_Definition($hook['handlerclass'], array($this->serviceManager))));
            }

            // setup lazy loading of eventhandler class.
            $this->eventManager->attach($hook['hookname'], new Zikula_ServiceHandler($hook['servicename'], $hook['handlermethod']));
        }
    }

    /**
     * Getter for the hooks property.
     *
     * @return array Of Hooks.
     */
    public function getHooks()
    {
        return $this->storage->getHooks();
    }

    /**
     * Unregister a hook by event name and service name.
     *
     * @param string $hookName   Name of the hook event.
     * @param string $serviceName Name of the service that hosts the event handler.
     */
    public function unregisterHook($hookName, $serviceName)
    {
        $this->storage->unregisterHook($hookName, $serviceName);
    }

    /**
     * Register hook with the persistence layer.
     *
     * @param string $hookName      The name of the hook event.
     * @param string $serviceName   The service name (ID).
     * @param string $hookClass     The name of the class that hosts the event handler.
     * @param string $handlerMethod Name of the method in the hookclass that hosts the event handler.
     */
    public function registerHook($hookName, $serviceName, $hookClass, $handlerMethod)
    {
        $this->storage->registerHook($hookName, $serviceName, $hookClass, $handlerMethod);
    }

    /**
     * Find hooks associated with a given object 'who'.
     *
     * @param string $type The hook type (valid only if $who is set).
     * @param string $who  Name of the object to fine hookable events for.
     *
     * @return array
     */
    public function getHookBindings($type = null, $who = null)
    {
        return $this->storage->getHookBindings($type, $who);
    }


    /**
     * Bind hook with object.
     *
     * @param string $hookName Hook name.
     * @param string $who      Object name.
     *
     * @return void
     */
    public function bindHook($hookName, $who)
    {
        $this->storage->bindHook($hookName, $who);
    }

    /**
     * Remove association.
     *
     * @param string $hookName
     */
    public function unbindHook($hookName)
    {
        $this->storage->bindHook($hookName);
    }

}
