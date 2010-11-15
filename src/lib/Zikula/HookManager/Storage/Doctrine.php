<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager_Storage
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine storage class.
 */
class Zikula_HookManager_Storage_Doctrine implements Zikula_HookManager_StorageInterface
{
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
     * @param string $hookEntityName        Class name of the Hook entity class.
     * @param string $hookBindingEntityName Class name of the HookBinding entity class.
     */
    public function __construct($hookEntityName, $hookBindingEntityName)
    {
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
            $this->hooks = Doctrine_Core::getTable($this->hookEntityName)->findAll(Doctrine_Core::HYDRATE_ARRAY);
        }

        return $this->hooks;
    }

    /**
     * Unregister a hook by event name and service name from persistence layer.
     *
     * @param string $hookName Name of the hook event.
     *
     * @return void
     */
    public function unregisterHook($hookName)
    {
        Doctrine_Core::getTable($this->hookEntityName)->createQuery()
            ->delete()
            ->where('hockname = ?', $hookName)
            ->execute();

        Doctrine_Core::getTable($this->hookBindingEntityName)->createQuery()
            ->delete()
            ->where('hookname = ?', $hookName)
            ->execute();
        $this->hooks = null;
    }

    /**
     * Register a new hook with the persistence layer.
     *
     * @param string $hookName      The name of the hook event.
     * @param string $handlerClass  The name of the class that hosts the event handler.
     * @param string $handlerMethod Name of the method in the hookclass that hosts the event handler.
     * @param string $serviceName   The service name (ID).
     *
     * @return void
     */
    public function registerHook($hookName, $handlerClass, $handlerMethod, $serviceName=null)
    {
        $hook = Doctrine_Core::getTable($this->hookEntityName)
                    ->create(array('hookname' => $hookName,
                                   'servicename' => $serviceName,
                                   'handlerclass' => $handlerClass,
                                   'handlermethod' => $handlerMethod));
        $hook->save();
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
            return Doctrine_Core::getTable($this->hookBindingEntityName)->createQuery()
                    ->where('who = ? and hookname LIKE ?', array($who, '%'.$type))
                    ->orderBy('weight')
                    ->fetchArray();
        }

        if (!$this->hookBindings) {
            $this->hookBindings = Doctrine_Core::getTable($this->hookBindingEntityName)->createQuery()
                ->orderBy('weight')
                ->fetchArray();
        }

        return $this->hookBindings;
    }

    /**
     * Bind a hook to an object.
     *
     * @param string $binding Name Name of the hook.
     * @param string $who  The name of the object to hook the event to.
     *
     * @return void
     */
    public function bindHook($hookName, $who)
    {
        $next = count($this->getHookBindings()) + 1;

        $binding = Doctrine_Core::getTable($this->hookBindingEntityName)
                ->create(array('hookname' => $hookName,
                               'who' => $who,
                               'weight' => $next));
        $binding->save();
        $this->hookBindings[] = $binding;
    }

    /**
     * Bind a hook from an object.
     *
     * @param string $hookName Hookname.
     *
     * @return void
     */
    public function unbindHook($hookName)
    {
        Doctrine_Core::getTable($this->hookBindingEntityName)->createQuery()
            ->delete()
            ->where('hookname = ?', $hookName)
            ->execute();

        $bindings = Doctrine_Core::getTable($this->hookBindingEntityName)->createQuery()
            ->orderBy('weight')
            ->execute();

        // reorder sequences.
        $count = 1;
        foreach ($bindings as $binding) {
            $binding->setWeight($count);
            $binding->save();
            $count++;
        }

        $this->hookBindings = null;
    }
}