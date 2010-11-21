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
 * HookUtil
 *
 * In the context of Zikula, unfortunately we need to maintain the HookManager
 * since it's not convenient to pass around using dependency injection.
 */
class HookUtil
{
    /**
     * Hook handlers key for persistence.
     */
    const HANDLERS = '/HookHandlers';

    /**
     * Hook handlers key for persistence.
     */
    const SORTS = '/DisplaySorts';

    /**
     * Provider capability key.
     */
    const PROVIDER_CAPABLE = 'hook_provider';

    /**
     * Subscriber capability key.
     */
    const SUBSCRIBER_CAPABLE = 'hook_subscriber';

    /**
     * Constructor.
     */
    private function __construct()
    {

    }

    /**
     * Register a provider hook handler.
     *
     * @param string  $name      Name of the hook handler.
     * @param string  $owner     Owner of the hook handler.
     * @param string  $area      Handler area.
     * @param string  $type      Hook type.
     * @param string  $className Class.
     * @param string  $method    Method name.
     * @param string  $serviceId Service ID if this is NOT a static class method.
     * @param integer $weight    Default weighting.
     *
     * @return void
     */
    public static function registerProvider($name, $owner, $area, $type, $className, $method, $serviceId=null, $weight=10)
    {
        $provider = new Zikula_Doctrine_Model_HookProviders();
        $provider->merge(array(
                'name' => $name,
                'owner' => $owner,
                'area' => $area,
                'type' => $type,
                'classname' => $className,
                'method' => $method,
                'serviceid' => $serviceId,
                'weight' => 10,
        ));
        $provider->save();
    }

    /**
     * Register a subscriber's availability.
     *
     * @param string $owner     Owner of the hook handler.
     * @area  string $area      Subscriber area.
     * @param string $type      Hook type.
     * @param string $eventName EventName called.
     *
     * @return void
     */
    public static function registerSubscriber($owner, $area, $type, $eventName)
    {
        $subscriber = new Zikula_Doctrine_Model_HookSubscribers();
        $subscriber->merge(array(
                'owner' => $owner,
                'area' => $area,
                'type' => $type,
                'eventname' => $eventName,
        ));
        $subscriber->save();
    }

    /**
     * Get all hook handlers for a given owner.
     *
     * @param string $owner Owner.
     *
     * @return array Nonassoc array of arrays or empty array if not found.
     */
    public static function getProvidersForOwner($owner)
    {
        return Doctrine_Query::create()->select()
                ->where('owner = ?', $owner)
                ->from('Zikula_Doctrine_Model_HookProviders')
                ->execute()
                ->toArray();
    }

    /**
     * Get all available subscribers for a given owner.
     *
     * @param string $owner Owner.
     *
     * @return array Nonassoc array of arrays or empty array if not found.
     */
    public static function getSubscribersForOwner($owner)
    {
        return Doctrine_Query::create()->select()
                ->where('owner = ?', $owner)
                ->from('Zikula_Doctrine_Model_HookSubscribers')
                ->execute()
                ->toArray();
    }

    /**
     * Get hook handler.
     *
     * @param string $name Name of hook handler.
     *
     * @return array Empty if not found
     */
    public static function getProvider($name)
    {
        return Doctrine_Core::getTable('Zikula_Doctrine_Model_HookProviders')->findOneBy('name', $name, Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * Get subscriber.
     *
     * @param string $eventName Name of subscriber.
     *
     * @return array Empty if not found
     */
    public static function getSubscriber($eventName)
    {
        return Doctrine_Core::getTable('Zikula_Doctrine_Model_HookSubscriber')->findOneBy('eventname', $eventName, Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * Get all hook handlers.
     *
     * @return array Nonassoc array of arrays. Empty if not found.
     */
    public static function getProviders()
    {
        return Doctrine_Core::getTable('Zikula_Doctrine_Model_HookProviders')->findAll(Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * Get all subscribers.
     *
     * @return array Nonassoc array of arrays. Empty if not found.
     */
    public static function getSubscribers()
    {
        return Doctrine_Core::getTable('Zikula_Doctrine_Model_HookSubscribers')->findAll(Doctrine_Core::HYDRATE_ARRAY);
    }

    /**
     * Has hook.
     *
     * @param string $name Name of hook handler.
     *
     * @return boolean
     */
    public static function hasProvider($name)
    {
        return (bool)self::getProvider($name);
    }

    /**
     * Has hook.
     *
     * @param string $name Name of hook handler.
     *
     * @return boolean
     */
    public static function hasSubscriber($name)
    {
        return (bool)self::getSubscriber($name);
    }

    /**
     * Unregister hook.
     *
     * @param string $name Name of hook handler.
     *
     * @return void
     */
    public static function unregisterProvider($name)
    {
        $hook = self::getHook($name);
        if (!$hook) {
            return;
        }

        // We have to remove any persistent event handlers from persistance and EventManager
        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers');
        foreach ($handlers as $key => $handler) {
            if ($handler['name'] == $name) {
                unset($handlers[$key]);
                EventUtil::getManager()->detach($handler['eventname'], self::resolveCallable($handler));
            }
        }
        $handlers = ModUtil::setVar(self::HANDLERS, 'handlers', $handlers);

        Doctrine_Query::create()->delete()
                ->where('name = ?', $name)
                ->from('Zikula_Doctrine_Model_HookProviders')
                ->execute();
    }

    /**
     * Unregister all hooks for a given owner
     *
     * @param string $owner Name of hook handler.
     *
     * @return void
     */
    public static function unregisterProvidersByOwner($owner)
    {
        $hooks = Doctrine_Query::create()->select()
                        ->where('owner = ?', $owner)
                        ->from('Zikula_Doctrine_Model_HookProviders')
                        ->execute()
                        ->toArray();

        if (!$hooks) {
            return;
        }

        foreach ($hooks as $hook) {
            self::unregisterProvider($hook['name']);
        }
    }

    /**
     * Register a persistent (runtime) hook handler (provider).
     *
     * These will be loaded into EventManager (and ServiceManager as required) at runtime.
     *
     * @param string  $eventName   Name of hookable event.
     * @param string  $handlerName Name of handling class.
     * @param integer $weight      The event handler weight.
     *
     * @throws InvalidArgumentException If attempting to register a hander for a non-existant hook.
     *
     * @return void
     */
    public static function registerHandler($eventName, $handlerName, $weight=null)
    {
        $hook = self::getProvider($handlerName);
        if (!$hook) {
            throw new InvalidArgumentException(sprintf('Hook handler %s does not exist', $handlerName));
        }

        $hook['weight'] = (is_null($weight)) ? (int)$hook['weight'] : (int)$weight;
        $hook['eventname'] = $eventName;
        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers', array());
        $handlers[] = $hook;
        ModUtil::setVar(self::HANDLERS, '/handlers', $handlers);
    }

    /**
     * Unregister a persistent (runtime) hook handler.
     *
     * @param string  $eventName   Name of hookable event.
     * @param string  $handlerName Name of handling class.
     * @param integer $weight      The event handler weight, default = 10.
     *
     * @return void
     */
    public static function unRegisterHandler($eventName, $handlerName, $weight=10)
    {
        $hook = self::getProvider($handlerName);
        if (!$hook) {
            return;
        }

        $hook['weight'] = (is_null($weight)) ? (int)$hook['weight'] : (int)$weight;
        $hook['eventname'] = $eventName;

        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers', false);
        if (!$handlers) {
            // nothing to do
            return;
        }

        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== $hook) {
                $filteredHandlers[] = $handler;
            }
        }

        ModUtil::setVar(self::HANDLERS, '/handlers', $filteredHandlers);
    }

    /**
     * Load all persisted hook handlers into EventManager (and ServiceManager as required).
     *
     * @return void
     */
    public static function loadHandlers()
    {
        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers', array());
        if (!$handlers) {
            return;
        }

        $serviceManager = ServiceUtil::getManager();
        $eventManager = EventUtil::getManager();
        foreach ($handlers as $key => $handler) {
            if ($handler['serviceid'] && !$serviceManager->hasService($handler['serviceid'])) {
                $callable = self::resolveCallable($handler);
            } else {
                $callable = self::resolveCallable($handler);
            }

            try {
                $eventManager->attach($handler['eventname'], $callable, $handler['weight']);
            } catch (InvalidArgumentException $e) {
                LogUtil::log(sprintf("Hook event handler could not be attached because %s", $e->getMessage()), Zikula_ErrorHandler::ERR);
            }
        }
    }

    /**
     * Resolve the correct callable for a handler.
     *
     * @param array $handler Handler.
     *
     * @return mixed Array or instance of Zikula_ServiceHandler
     */
    protected static function resolveCallable($handler)
    {
        $serviceManager = ServiceUtil::getManager();
        if ($handler['serviceid']) {
            $definition = new Zikula_ServiceManager_Definition($handler['classname'], array($serviceManager));
            $serviceManager->registerService(new Zikula_ServiceManager_Service($handler['serviceid'], $definition));
            $callable = new Zikula_ServiceHandler($handler['serviceid'], $handler['method']);
        } else {
            $callable = array($handler['classname'], $handler['method']);
        }

        return $callable;
    }

    /**
     * Sort out display hooks according to configuration.
     *
     * @param string $owner   Owner.
     * @param string $results Assoc-array of results.
     *
     * @return array
     */
    public static function sortDisplayHooks($owner, $results)
    {
        if (!$results) {
            return $results;
        }

        // Get correct order of event responses.
        $orderBy = self::getDisplaySortsByOwner($owner);
        if (!$orderBy) {
            return $orderBy;
        }

        // Perform the sort now.
        $sortedResults = array();
        foreach ($orderBy as $key) {
            if (array_key_exists($key, $results)) {
                $sortedResults[$key] = $results[$key];
            }
        }

        return $results;
    }

    /**
     * Set Display Hook sorting information.
     *
     * @param string $owner Owner.
     * @param array  $array Non-assoc array of owners in order, array('Comments', 'Ratings').
     *
     * @return void
     */
    public static function setDisplaySortsByOwner($owner, array $array)
    {
        ModUtil::setVar(self::SORTS, $owner, $array);
    }

    /**
     * Get Display Hook sorting information.
     *
     * @param string $owner Owner.
     *
     * @return array
     */
    public static function getDisplaySortsByOwner($owner)
    {
        return ModUtil::getVar(self::SORTS, $owner, array());
    }

    /**
     * Get all sorting information
     *
     * @return array
     */
    public static function getAllDisplaySorts()
    {
        return ModUtil::getVar(self::SORTS, '', array());
    }

    /**
     * Set all sorting information.
     *
     * @param array $array Associative array of sorts array('owner' => array('Comments', 'Ratings')).
     *
     * @return void
     */
    public static function setAllDisplaySorts(array $array)
    {
        if (!$array) {
            return;
        }

        foreach ($array as $key => $value) {
            ModUtil::setVar(self::SORTS, $key, $value);
        }
    }

    /**
     * Get list of modules who provide hooks.
     *
     * This means modules that provide hooks that can be attached to other modules.
     *
     * @return array
     */
    public static function getHookProviders()
    {
        return ModUtil::getModulesCapableOf('hook_provider');
    }

    /**
     * Get list of modules that subscribe to hooks.
     *
     * This means modules that can make use of another modules' hooks.
     *
     * @return array
     */
    public static function getHookSubscribers()
    {
        return ModUtil::getModulesCapableOf('hook_subscriber');
    }

    /**
     * Is a module is subscriber capable.
     *
     * @param string $module Module name.
     *
     * @return boolean
     */
    public static function isProviderCapable($module)
    {
        return ModUtil::isCapable($module, self::PROVIDER_CAPABLE);
    }

    /**
     * Is a module subscriber capable.
     *
     * @param string $module Module name.
     *
     * @return boolean
     */
    public static function isSubscriberCapable($module)
    {
        return ModUtil::isCapable($module, self::SUBSCRIBER_CAPABLE);
    }

    /**
     * Register Provider Hook handlers with persistence layer.
     * 
     * @param Zikula_Version $version Module's version object.
     *
     * @return void
     */
    public static function registerHookProviderBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookProviderBundles();
        $owner = $version->getName();
        foreach ($bundles as $bundle) {
            foreach ($bundle->getHooks() as $name => $hook) {
                self::registerProvider($name, $owner, $bundle->getArea(), $hook['type'], $hook['classname'], $hook['method'], $hook['serviceid'], $hook['weight']);
            }
        }
    }

    /**
     * Register Subscribers with persistence layer.
     *
     * @param Zikula_Version $version Module's version object.
     *
     * @return void
     */
    public static function registerHookSubscriberBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookSubscriberBundles();
        $owner = $version->getName();
        foreach ($bundles as $bundle) {
            foreach ($bundle->getHookTypes() as $type => $eventName) {
                self::registerSubscriber($owner, $bundle->getArea(), $type, $eventName);
            }
        }
    }

    /**
     * Bind subscribers to a provider.
     * 
     * @param string $subscriberArea Subscriber area name.
     * @param string $providerArea   Provider area name.
     *
     * @return boolean
     */
    public static function bindSubscribersToProvider($subscriberArea, $providerArea)
    {
        $subscribers = Doctrine_Query::create()->select()
                        ->where('area = ?', $subscriberArea)
                        ->from('Zikula_Doctrine_Model_HookSubscribers')
                        ->execute()
                        ->toArray();

        if (!$subscribers) {
            return false;
        }

        // Link all subscriber events types that match the selected provider
        $linked = false;
        foreach ($subscribers as $subscriber) {
            $provider = Doctrine_Query::create()->select()
                            ->where('area = ?', $providerArea)
                            ->andWhere('type = ?', $subscriber['type'])
                            ->from('Zikula_Doctrine_Model_HookProviders')
                            ->execute()
                            ->toArray();

            if ($provider) {
                $provider = $provider[0];
                $linked = true;
                $handlerName = $provider['name'];
                $weight = $provider['weight'];
                self::registerHandler($subscriber['eventname'], $handlerName, $weight);
            }
        }

        if ($linked) {
            $binding = new Zikula_Doctrine_Model_HookBindings();
            $binding->subowner = $provider['owner'];
            $binding->providerowner = $subscriber['owner'];
            $binding->subarea = $subscriberArea;
            $binding->providerarea = $providerArea;
            $binding->save();
        }
    }

    /**
     * Un-bind subscribers from a provider.
     *
     * @param string $subscriberArea Subscriber area name.
     * @param string $providerArea   Provider area name.
     *
     * @return boolean
     */
    public static function unBindSubscribersFromProvider($subscriberArea, $providerArea)
    {
        $binding = Doctrine_Query::create()->select()
                        ->where('subarea = ?', $subscriberArea)
                        ->andWhere('providerarea = ?', $providerArea)
                        ->from('Zikula_Doctrine_Model_HookBindings')
                        ->execute()
                        ->toArray();

        $subscribers = Doctrine_Query::create()->select()
                        ->where('area = ?', $subscriberArea)
                        ->from('Zikula_Doctrine_Model_HookSubscribers')
                        ->execute()
                        ->toArray();

        if (!$subscribers) {
            return false;
        }

        // Unlink all subscriber events types that match the selected provider
        foreach ($subscribers as $subscriber) {
            $provider = Doctrine_Query::create()->select()
                            ->where('area = ?', $providerArea)
                            ->andWhere('type = ?', $subscriber['type'])
                            ->from('Zikula_Doctrine_Model_HookProviders')
                            ->execute()
                            ->toArray();

            if ($provider) {
                $provider = $provider[0];
                $linked = true;
                $handlerName = $provider['name'];
                $weight = $provider['weight'];
                self::unRegisterHandler($subscriber['eventname'], $handlerName, $weight);
            }
        }

        // delete binding
        Doctrine_Query::create()->delete()
                ->where('subarea = ?', $subscriberArea)
                ->andWhere('providerarea = ?', $providerArea)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->execute();
    }

    /**
     * Get All subscribers connected to a given provider module.
     *
     * @param string $providerName Name of provider.
     *
     * @return array
     */
    public static function getSubscribersConnectedToProvider($providerName)
    {
        return Doctrine_Query::create()->select()
                ->andWhere('providerowner = ?', $providerName)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->execute()
                ->toArray();
    }

    /**
     * Get All providers in use by a given subscriber.
     *
     * @param string $subscriberName Subscriber's name.
     *
     * @return array
     */
    public static function getProvidersInUseBy($subscriberName)
    {
        return Doctrine_Query::create()->select()
                ->andWhere('subowner = ?', $subscriberName)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->execute()
                ->toArray();
    }
}
