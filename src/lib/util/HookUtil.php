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
 * HookUtil.
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
     * @param string $area      Subscriber area.
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
     * Un-register a subscriber's availability.
     *
     * @param string $owner     Owner of the hook handler.
     * @param string $area      Subscriber area.
     * @param string $type      Hook type.
     * @param string $eventName EventName called.
     *
     * @return array
     */
    public static function unregisterSubscriber($owner, $area, $type, $eventName)
    {
        return Doctrine_Query::create()->delete()
                ->where('owner = ?', $owner)
                ->andWhere('area = ?', $area)
                ->andWhere('type = ?', $type)
                ->andWhere('eventname = ?', $eventName)
                ->from('Zikula_Doctrine_Model_HookSubscribers')
                ->execute();
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
                ->fetchArray();
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
                ->fetchArray();
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
        return Doctrine_Core::getTable('Zikula_Doctrine_Model_HookSubscribers')->findOneBy('eventname', $eventName, Doctrine_Core::HYDRATE_ARRAY);
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
     * Unregister a single provider handler.
     *
     * This removed all runtime handlers for events of subscribers that might be attached.
     *
     * @param string $name Name of provider handler.
     *
     * @return void
     */
    public static function unregisterProvider($name)
    {
        $provider = self::getProvider($name);
        if (!$provider) {
            return;
        }

        // We have to remove any persistent event handlers from persistance and EventManager
        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers');
        foreach ($handlers as $handler) {
            self::unregisterHandler($handler['eventname'], $handler['name'], $handler['weight']);
        }

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
        $providers = Doctrine_Query::create()->select()
                        ->where('owner = ?', $owner)
                        ->from('Zikula_Doctrine_Model_HookProviders')
                        ->fetchArray();

        if (!$providers) {
            return;
        }

        foreach ($providers as $provider) {
            self::unregisterProvider($provider['name']);
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
        $provider = self::getProvider($handlerName);
        if (!$provider) {
            throw new InvalidArgumentException(sprintf('Hook handler %s does not exist', $handlerName));
        }

        $provider['weight'] = (is_null($weight)) ? (int)$provider['weight'] : (int)$weight;
        $provider['eventname'] = $eventName;
        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers', array());
        $handlers[] = $provider;
        ModUtil::setVar(self::HANDLERS, '/handlers', $handlers);
    }

    /**
     * Unregister a persistent (runtime) hook handler.
     *
     * @param string  $eventName   Name of hookable event.
     * @param string  $handlerName Common name of handler.
     * @param integer $weight      The event handler weight, default = 10.
     *
     * @return void
     */
    public static function unregisterHandler($eventName, $handlerName, $weight=10)
    {
        $provider = self::getProvider($handlerName);
        if (!$provider) {
            return;
        }

        $provider['weight'] = (is_null($weight)) ? (int)$provider['weight'] : (int)$weight;
        $provider['eventname'] = $eventName;

        $handlers = ModUtil::getVar(self::HANDLERS, '/handlers', false);
        if (!$handlers) {
            // nothing to do
            return;
        }

        $filteredHandlers = array();
        foreach ($handlers as $handler) {
            if ($handler !== $provider) {
                $filteredHandlers[] = $handler;
            } else {
                // remove any runtime event handlers
                EventUtil::getManager()->detach($handler['eventname'], self::resolveCallable($handler));
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
        $sm = new Zikula_ServiceManager_Service('zikula.servicemanager');
        foreach ($handlers as $key => $handler) {
            if ($handler['serviceid'] && !$serviceManager->hasService($handler['serviceid'])) {
                $definition = new Zikula_ServiceManager_Definition($handler['classname'], array($sm));
                $serviceManager->registerService(new Zikula_ServiceManager_Service($handler['serviceid'], $definition));
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
     * @return array|Zikula_ServiceHandler
     */
    protected static function resolveCallable($handler)
    {
        if ($handler['serviceid']) {
            $callable = new Zikula_ServiceHandler($handler['serviceid'], $handler['method']);
        } else {
            $callable = array($handler['classname'], $handler['method']);
        }

        return $callable;
    }

    /**
     * Sort out display hooks according to configuration.
     *
     * @param string $subscriberArea Owner.
     * @param string $results        Assoc-array of results.
     *
     * @return array
     */
    public static function sortDisplayHooks($subscriberArea, $results)
    {
        if (!$results) {
            return $results;
        }

        // Get correct order of event responses.
        $orderBy = self::getDisplaySortsByArea($subscriberArea);
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
     * @param string $area  Owner.
     * @param array  $array Non-assoc array of owners in order, array('Comments', 'Ratings').
     *
     * @return void
     */
    public static function setDisplaySortsByArea($area, array $array)
    {
        ModUtil::setVar(self::SORTS, $area, $array);
    }

    /**
     * Get Display Hook sorting information.
     *
     * @param string $area Owner.
     *
     * @return array Non-assoc array of providers in the order they should be sorted.
     */
    public static function getDisplaySortsByArea($area)
    {
        return ModUtil::getVar(self::SORTS, $area, array());
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
     * Unregister providers by bundle.
     *
     * This cascades to remove all bindings by any subscribers to the providers in these bundles.
     * 
     * @param Zikula_Version $version Module's version object.
     *
     * @return void
     */
    public static function unregisterHookProviderBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookProviderBundles();
        $providerName = $version->getName();

        $subscribers = self::getSubscribersInUseBy($providerName);
        foreach ($subscribers as $subscriber) {
            // remove handlers for this binding, the associated sorts and update bindings table.
            self::unbindSubscribersFromProvider($subscriber['subarea'], $subscriber['providerarea']);
        }

        // now delete availability of bundles from subscriber availability table.
        foreach ($bundles as $bundle) {
            Doctrine_Query::create()->delete()
                    ->where('owner = ?', $providerName)
                    ->andWhere('area = ?', $bundle->getArea())
                    ->from('Zikula_Doctrine_Model_HookProviders')
                    ->execute();
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
     * Upgrade subscriber bundles.
     *
     * Adds any new area bundles, or types within a bundle.
     * Removes any removed areas.  Does not remove single types within an area.
     * 
     * @param Zikula_Version $version Zikula version instance.
     *
     * @return void
     */
    public static function upgradeHookSubscriberBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookSubscriberBundles();
        $owner = $version->getName();

        // add missing elements of bundles
        foreach ($bundles as $bundle) {
            foreach ($bundle->getHookTypes() as $type => $eventName) {
                $exists = Doctrine_Query::create()->select()
                                ->where('owner = ?', $owner)
                                ->andWhere('area = ?', $bundle->getArea())
                                ->andWhere('type = ?', $type)
                                ->andWhere('eventname = ?', $eventName)
                                ->from('Zikula_Doctrine_Model_HookSubscribers')
                                ->count();
                if (!$exists) {
                    self::registerSubscriber($owner, $bundle->getArea(), $type, $eventName);
                }
            }
        }

        // remove any areas that no longer exist
        $subscribers = self::getSubscribersForOwner($owner);
        foreach ($subscribers as $subscriber) {
            try {
                $bundle = $version->getHookSubscriberBundle($subscriber['area']);
            } catch (InvalidArgumentException $e) {
                // this area doesnt exist any more so delete the bundle and unassociate hook bindings
                $providers = self::getProvidersInUseBy($owner);
                foreach ($providers as $provider) {
                    // remove handlers for this binding, the associated sorts and update bindings table.
                    self::unbindSubscribersFromProvider($provider['subarea'], $provider['providerarea']);
                }
                self::unregisterSubscriber($subscriber['owner'], $subscriber['area'], $subscriber['type'], $subscriber['eventname']);
                continue;
            }
        }
    }

    /**
     * Upgrade provider bundles.
     *
     * Adds any new area bundles, or types within a bundle.
     * Removes any removed areas.  Does not remove single types within an area.
     *
     * @param Zikula_Version $version Zikula version instance.
     *
     * @return void
     */
    public static function upgradeHookProviderBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookProviderBundles();
        $owner = $version->getName();

        // add missing elements of bundles
        foreach ($bundles as $bundle) {
            foreach ($bundle->getHooks() as $name => $hook) {
                $exists = Doctrine_Query::create()->select()
                                ->where('owner = ?', $owner)
                                ->andWhere('area = ?', $bundle->getArea())
                                ->andWhere('type = ?', $hook['type'])
                                ->andWhere('name = ?', $name)
                                ->andWhere('classname = ?', $hook['classname'])
                                ->andWhere('method = ?', $hook['method'])
                                ->andWhere('weight = ?', $hook['weight'])
                                ->andWhere('serviceid = ?', $hook['serviceid'])
                                ->from('Zikula_Doctrine_Model_HookProviders')
                                ->count();
                if (!$exists) {
                    self::registerProvider($name, $owner, $bundle->getArea(), $hook['type'], $hook['classname'], $hook['method'], $hook['serviceid'], $hook['weight']);
                }
            }
        }

        // remove any areas that no longer exist
        $providers = self::getProvidersForOwner($owner);
        foreach ($providers as $provider) {
            try {
                $bundle = $version->getHookProviderBundle($provider['area']);
            } catch (InvalidArgumentException $e) {
                // this area doesnt exist any more so delete the bundle and unassociate hook bindings
                $providersInUse = self::getSubscribersInUseBy($owner);
                foreach ($providersInUse as $inUse) {
                    // remove any subscribers to this provider and removing any bindings
                    self::unbindSubscribersFromProvider($inUse['subarea'], $inUse['providerarea']);
                }
                self::unregisterProvider($provider['name']);
                continue;
            }
        }
    }

    /**
     * Unregister all subscribers from the system.
     * 
     * This cascades to remove all event handlers, sorting data and update bindings table.
     *
     * @param Zikula_Version $version Module's version object.
     *
     * @return void
     */
    public static function unregisterHookSubscriberBundles(Zikula_Version $version)
    {
        $bundles = $version->getHookSubscriberBundles();
        $subscriberName = $version->getName();

        $providers = self::getProvidersInUseBy($subscriberName);
        foreach ($providers as $provider) {
            // remove handlers for this binding, the associated sorts and update bindings table.
            self::unbindSubscribersFromProvider($provider['subarea'], $provider['providerarea']);
        }

        // now delete availability of bundles from subscriber availability table.
        foreach ($bundles as $bundle) {
            Doctrine_Query::create()->delete()
                    ->where('owner = ?', $subscriberName)
                    ->andWhere('area = ?', $bundle->getArea())
                    ->from('Zikula_Doctrine_Model_HookSubscribers')
                    ->execute();
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
                        ->fetchArray();

        if (!$subscribers) {
            return false;
        }

        // Link all subscriber events types that match the selected provider
        $linked = false;
        foreach ($subscribers as $subscriber) {
            $hookprovider = Doctrine_Query::create()->select()
                            ->where('area = ?', $providerArea)
                            ->andWhere('type = ?', $subscriber['type'])
                            ->from('Zikula_Doctrine_Model_HookProviders')
                            ->fetchArray();

            if ($hookprovider) {
                $provider = $hookprovider[0];
                $linked = true;
                $handlerName = $provider['name'];
                $weight = $provider['weight'];
                self::registerHandler($subscriber['eventname'], $handlerName, $weight);
            }
        }

        if ($linked) {
            $binding = new Zikula_Doctrine_Model_HookBindings();
            $binding->subowner = $subscriber['owner'];
            $binding->providerowner = $provider['owner'];
            $binding->subarea = $subscriberArea;
            $binding->providerarea = $providerArea;
            $binding->save();

            $sort = self::getDisplaySortsByArea($subscriberArea);
            if (!in_array($providerArea, $sort)) {
                $sort[] = $providerArea;
                self::setDisplaySortsByArea($subscriberArea, $sort);
            }
        }
    }

    /**
     * Un-bind all subscribers from a provider for a given area.
     *
     * @param string $subscriberArea Subscriber area name.
     * @param string $providerArea   Provider area name.
     *
     * @return boolean
     */
    public static function unbindSubscribersFromProvider($subscriberArea, $providerArea)
    {
        $subscribers = Doctrine_Query::create()->select()
                        ->where('area = ?', $subscriberArea)
                        ->from('Zikula_Doctrine_Model_HookSubscribers')
                        ->fetchArray();

        if (!$subscribers) {
            return false;
        }

        // Unlink all subscriber events types that match the selected provider
        foreach ($subscribers as $subscriber) {
            $hookprovider = Doctrine_Query::create()->select()
                            ->where('area = ?', $providerArea)
                            ->andWhere('type = ?', $subscriber['type'])
                            ->from('Zikula_Doctrine_Model_HookProviders')
                            ->fetchArray();

            if ($hookprovider) {
                $provider = $hookprovider[0];
                $linked = true;
                $handlerName = $provider['name'];
                $weight = $provider['weight'];
                self::unregisterHandler($subscriber['eventname'], $handlerName, $weight);
            }
        }

        // delete binding
        Doctrine_Query::create()->delete()
                ->where('subarea = ?', $subscriberArea)
                ->andWhere('providerarea = ?', $providerArea)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->execute();

        if (isset($linked)) {
            $sort = self::getDisplaySortsByArea($subscriberArea);
            $key = array_search($providerArea, $sort);
            if ($key !== false) {
                unset($sort[$key]);
                self::setDisplaySortsByArea($subscriberArea, $sort);
            }
        }
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
                ->fetchArray();
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
                ->fetchArray();
    }

    /**
     * Get all subscribers that use a given provider.
     *
     * @param string $providerName Provider's name.
     *
     * @return array
     */
    public static function getSubscribersInUseBy($providerName)
    {
        return Doctrine_Query::create()->select()
                ->andWhere('providerowner = ?', $providerName)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->fetchArray();
    }

    /**
     * Check if given provider is in use by a given subscriber.
     *
     * @param string $subscriberName Subscriber's name.
     * @param string $providerName   Provider's name.
     *
     * @return array
     */
    public static function bindingsBetweenProviderAndSubscriber($subscriberName, $providerName)
    {
        return Doctrine_Query::create()->select()
                ->andWhere('subowner = ?', $subscriberName)
                ->andWhere('providerowner  = ?', $providerName)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->fetchArray();
    }

    /**
     * Get all areas of a provider.
     *
     * @param string $providerName Provider's name.
     *
     * @return array
     */
    public static function getProviderAreasByOwner($providerName)
    {
        return (array)Doctrine_Query::create()->select('DISTINCT p.area')
                ->where('p.owner = ?', $providerName)
                ->from('Zikula_Doctrine_Model_HookProviders p')
                ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Get all areas of a subscriber.
     *
     * @param string $subscriberName Subscriber's name.
     *
     * @return array
     */
    public static function getSubscriberAreasByOwner($subscriberName)
    {
        return (array)Doctrine_Query::create()->select('DISTINCT s.area')
                ->where('s.owner = ?', $subscriberName)
                ->from('Zikula_Doctrine_Model_HookSubscribers s')
                ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Get owner (subscriber) given an area.
     *
     * @param string $area Subscriber's area.
     *
     * @return string
     */
    public static function getOwnerBySubscriberArea($area)
    {
        return Doctrine_Query::create()->select('DISTINCT s.owner')
                ->where('s.area = ?', $area)
                ->from('Zikula_Doctrine_Model_HookSubscribers s')
                ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Get owner (provider) given an area.
     *
     * @param string $area Provider's area.
     *
     * @return string
     */
    public static function getOwnerByProviderArea($area)
    {
        return Doctrine_Query::create()->select('DISTINCT p.owner')
                ->where('p.area = ?', $area)
                ->from('Zikula_Doctrine_Model_HookProviders p')
                ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
    }
    
    /**
     * Check if given areas (subscriberarea and providerarea) are bound together.
     *
     * @param string $subscriberarea Subscriber's area.
     * @param string $providerarea   Provider's area.
     *
     * @return array
     */
    public static function bindingBetweenAreas($subscriberarea, $providerarea)
    {
        return Doctrine_Query::create()->select()
                ->andWhere('subarea = ?', $subscriberarea)
                ->andWhere('providerarea  = ?', $providerarea)
                ->from('Zikula_Doctrine_Model_HookBindings')
                ->fetchOne();
    }
    
    /**
     * Check if given areas (subscriberarea and providerarea) can be bound together.
     *
     * @param string $subscriberarea Subscriber's area.
     * @param string $providerarea   Provider's area.
     *
     * @return array
     */
    public static function allowBindingBetweenAreas($subscriberarea, $providerarea)
    {
        $subscribers = Doctrine_Query::create()->select()
                        ->where('area = ?', $subscriberarea)
                        ->from('Zikula_Doctrine_Model_HookSubscribers')
                        ->fetchArray();

        if (!$subscribers) {
            return false;
        }

        $allow = false;
        foreach ($subscribers as $subscriber) {
            $hookprovider = Doctrine_Query::create()->select()
                            ->where('area = ?', $providerarea)
                            ->andWhere('type = ?', $subscriber['type'])
                            ->from('Zikula_Doctrine_Model_HookProviders')
                            ->fetchArray();

             if ($hookprovider) {
                $allow = true;
                break;
            }
        }

        return $allow;
    }
}
