<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Common
 * @subpackage HookManager_Storage
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


/**
 * Doctrine storage class.
 */
class Zikula_HooksManager_Storage_Doctrine implements Zikula_HooksManager_StorageInterface
{
    /**
     * EntityManager.
     *
     * @var object
     */
    protected $entityManager;

    /**
     * Hooks.
     *
     * @var array
     */
    protected $hooks;

    /**
     * Hook associations.
     *
     * @var array
     */
    protected $hookBindings;

    /**
     * Name of the hook entity class.
     *
     * @var string
     */
    protected $hookEntityName;

    /**
     * Name of the hook binding entity class.
     *
     * @var string
     */
    protected $hookBindingEntityName;


    /**
     * Constructor.
     *
     * @param EntityManager $entityManager         Doctrine Entitymanager.
     * @param string        $hookEntityName        Class name of the Hook entity class.
     * @param string        $hookBindingEntityName Class name of the HookBinding entity class.
     */
    public function __construct(EntityManager $entityManager, $hookEntityName, $hookBindingEntityName)
    {
        $this->entityManager = $entityManager;
        $this->hookEntityName = $hookEntityName;
        $this->hookBindingEntityName = $hookBindingEntityName;
    }

    /**
     * Get hooks from database.
     *
     * @return array Hooks.
     */
    public function getHooks()
    {
        if (!$this->hooks) {
            $this->hooks = $this->entityManager->createQuery("SELECT h FROM {$this->hookEntityName} h")->getResult(Query::HYDRATE_ARRAY);
        }

        return $this->hooks;
    }

    /**
     * Unregister a hook by event name and service name from persistence layer.
     *
     * @param string $hookName Name of the hook event.
     */
    public function unregisterHook($hookName)
    {
        $this->entityManager->createQuery("delete h from {$this->hookEntityName} h where h.hookname = $hookName")->getResult();
        $this->entityManager->createQuery("delete h from {$this->hookBindingEntityName} h where h.hookname = $hookName")->getResult();
        $this->hooks = null;
    }

    /**
     * Register a new hook with the persistence layer.
     *
     * @param string $hookName      The name of the hook event.
     * @param string $serviceName   The service name (ID).
     * @param string $handlerClass  The name of the class that hosts the event handler.
     * @param string $handlerMethod Name of the method in the hookclass that hosts the event handler.
     */
    public function registerHook($hookName, $serviceName, $handlerClass, $handlerMethod)
    {
        $r = new ReflectionClass($this->hookEntityName);
        $hook = $r->newInstance();
        $hook->set($hookName, $serviceName, $handlerClass, $handlerMethod);
        $this->entityManager->persist($hook);
        $this->hooks[] = $hook;
    }

    /**
     * Find hooks bound with a given object 'who'.
     *
     * @param string $type The hook type (valid only if $who is set).
     * @param string $who  Name of the object to fine hookable events for.
     *
     * @return array
     */
    public function getHookBindings($type = null, $who = null)
    {
        if (!is_null($who)) {
            return $this->entityManager->createQuery("SELECT h FROM {$this->hookBindingEntityName} h WHERE h.who = '$who' AND h.hookname LIKE '%$type' ORDER BY h.weight")->getResult(Query::HYDRATE_ARRAY);
        }

        if (!$this->hookBindings) {
            $this->hookBindings = $this->entityManager->createQuery("SELECT h FROM {$this->hookBindingEntityName} h ORDER BY h.weight")->getResult(Query::HYDRATE_ARRAY);
        }

        return $this->hookBindings;
    }

    /**
     * Bind a hook to an object.
     *
     * @param string $binding Name Name of the hook.
     * @param string $who  The name of the object to hook the event to.
     */
    public function bindHook($hookName, $who)
    {
        $next = count($this->getHookBindings()) + 1;

        $r = new ReflectionClass($this->hookBindingEntityName);
        $binding = $r->newInstance();
        $binding->set($hookName, $who, $next);
        $this->entityManager->persist($binding);
        $this->hookBindings[] = $binding;
    }

    /**
     * Bind a hook from an object.
     *
     * @param string $hookName Hookname.
     */
    public function unbindHook($hookName)
    {
        $this->entityManager->createQuery("DELETE h FROM {$this->hookEntityName} h WHERE h.hookname = '$hookName'")->getResult();
        $bindings = $this->entityManager->createQuery("SELECT h FROM {$this->hookBindingEntityName} h ORDER BY h.weight")->getResult(Query::HYDRATE_OBJECT);

        // reorder sequences.
        $count = 1;
        foreach ($bindings as $binding) {
            $binding->setWeight($count);
            $count++;
        }

        $this->entityManager->flush();
        $this->hookBindings = null;
    }
}