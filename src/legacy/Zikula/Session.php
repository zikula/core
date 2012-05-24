<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Session
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Session class.
 */
class Zikula_Session
{
    /**
     * The message type for status messages, to use with, for example, {@link hasMessages()}.
     *
     * @var string
     */
    const MESSAGE_STATUS = 'status';

    /**
     * The message type for error messages, to use with, for example, {@link hasMessages()}.
     *
     * @var string
     */
    const MESSAGE_ERROR = 'error';

    /**
     * Storage engine.
     *
     * @var Zikula_Session_Storage
     */
    protected $storage;

    /**
     * Flag.
     *
     * @var boolean
     */
    protected $started;

    /**
     * Constructor.
     *
     * @param Zikula_Session_StorageInterface $storage Storage engine.
     */
    public function __construct(Zikula_Session_StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->started = false;
    }

    /**
     * Start session.
     *
     * @throws RuntimeException If illegal namespace received.
     *
     * @return boolean
     */
    public function start()
    {
        if (!$this->started && session_id()) {
            throw new RuntimeException('Error! Session has already been started outside of Zikula_Session.');
        }

        if (!$this->started) {
            register_shutdown_function('session_write_close');
            $this->storage->start();
            $_SESSION['_zikula_messages'] = array_key_exists('_zikula_messages', $_SESSION) ? $_SESSION['_zikula_messages'] : array();
            $this->started = true;
        }

        return $this->started;
    }

    /**
     * Check if session has started.
     *
     * @return boolean
     */
    public function hasStarted()
    {
        return $this->started;
    }

    /**
     * Expire session.
     *
     * Changes session ID and lose all data associated with a session.
     *
     * @return void
     */
    public function expire()
    {
        $this->storage->expire();
    }

    /**
     * Regenerate session.
     *
     * Changes the session ID while retaining session data.
     *
     * @return void
     */
    public function regenerate()
    {
        $this->storage->regenerate();
    }

    /**
     * Add session message to the stack for a given type.
     *
     * @param string $type  Type.
     * @param mixed  $value Value.
     *
     * @return void
     */
    public function addMessage($type, $value)
    {
        if (!$this->hasMessages($type)) {
            $_SESSION['_zikula_messages'][$type] = array();
        }
        $_SESSION['_zikula_messages'][$type][] = $value;
    }

    /**
     * Get special attributes by type.
     *
     * @param string $type    Type.
     * @param mixed  $default Default value to return (default = array()).
     *
     * @return mixed
     */
    public function getMessages($type, $default = array())
    {
        if (isset($_SESSION) && array_key_exists($type, $_SESSION['_zikula_messages'])) {
            return $_SESSION['_zikula_messages'][$type];
        }

        return $default;
    }

    /**
     * Has attributes of type.
     *
     * @param string $type Type.
     *
     * @return boolean
     */
    public function hasMessages($type)
    {
        return isset($_SESSION) && array_key_exists($type, $_SESSION['_zikula_messages']) && !empty($_SESSION['_zikula_messages'][$type]);
    }

    /**
     * Clear messages of type.
     *
     * @param string $type Type.
     *
     * @return void
     */
    public function clearMessages($type = null)
    {
        if (is_null($type)) {
            $_SESSION['_zikula_messages'] = array();
        }

        if ($this->hasMessages($type)) {
            $_SESSION['_zikula_messages'][$type] = array();
        }
    }

    /**
     * Set session variable.
     *
     * @param string $key       Key.
     * @param mixed  $default   Default = null.
     * @param string $namespace Namespace.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return mixed
     */
    public function get($key, $default = null, $namespace = '/')
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly');
        }

        return $this->has($key, $namespace) ? $_SESSION[$namespace][$key] : $default;
    }

    /**
     * Set a session variable.
     *
     * @param string $key       Key.
     * @param mixed  $value     Value.
     * @param string $namespace Namespace.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return void
     */
    public function set($key, $value, $namespace = '/')
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly');
        }
        $_SESSION[$namespace][$key] = $value;
    }

    /**
     * Delete session variable by key.
     *
     * @param string $key       Key.
     * @param string $namespace Namespace.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return void
     */
    public function del($key, $namespace = '/')
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly');
        }
        if ($this->has($key, $namespace)) {
            unset($_SESSION[$namespace][$key]);
        }
    }

    /**
     * Check if session variable key exists.
     *
     * @param string $key       Key.
     * @param string $namespace Namespace.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return boolean
     */
    public function has($key, $namespace = '/')
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly');
        }

        if (isset($_SESSION[$namespace])) {
            return array_key_exists($key, $_SESSION[$namespace]);
        }

        return false;
    }

    /**
     * Get the contents of an entire namespace as an array.
     *
     * @param string $namespace Namespace name; optional; default is '/'.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return array The contents of the namespace as an array indexed by session variable keys; empty if the namespace is empty or does not exist.
     */
    public function getNamespaceContents($namespace = '/')
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly.');
        }

        $contents = array();

        if (isset($_SESSION[$namespace]) && !empty($_SESSION[$namespace]) && is_array($_SESSION[$namespace])) {
            $contents = $_SESSION[$namespace];
        }

        return $contents;
    }

    /**
     * Clear an entire namespace.
     *
     * Use with caution, and only if you know that no other code makes use of the namespace.
     *
     * @param string $namespace Namespace.
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return void
     */
    public function clearNamespace($namespace)
    {
        if ($namespace == '_zikula_messages') {
            throw new InvalidArgumentException('You cannot access _zikula_messages directly.');
        }

        $_SESSION[$namespace] = array();
    }
}
