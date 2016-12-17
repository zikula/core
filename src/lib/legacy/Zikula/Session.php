<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Zikula_Session class.
 *
 * @deprecated
 */
class Zikula_Session extends Session
{
    /**
     * The message type for status messages, to use with, for example, {@link hasMessages()}.
     *
     * @var string
     */
    const MESSAGE_STATUS = 'status';

    /**
     * The message type for warning messages, to use with, for example, {@link hasMessages()}.
     *
     * @var string
     */
    const MESSAGE_WARNING = 'warning';

    /**
     * The message type for error messages, to use with, for example, {@link hasMessages()}.
     *
     * @var string
     */
    const MESSAGE_ERROR = 'error';

    /**
     * Check if session has started.
     *
     * @return boolean
     */
    public function hasStarted()
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        return $this->isStarted();
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
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        $this->invalidate();
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
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        $this->migrate();
    }

    /**
     * Add session message to the stack for a given type.
     *
     * @param string $type  Type
     * @param mixed  $value Value
     *
     * @return void
     */
    public function addMessage($type, $value)
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        $this->getFlashBag()->add($type, $value);
    }

    /**
     * Get special attributes by type.
     *
     * @param string $type    Type
     * @param mixed  $default Default value to return (default = [])
     *
     * @return mixed
     */
    public function getMessages($type, $default = [])
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        return $this->getFlashBag()->get($type, $default);
    }

    /**
     * Has attributes of type.
     *
     * @param string $type Type
     *
     * @return boolean
     */
    public function hasMessages($type)
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        return $this->getFlashBag()->has($type);
    }

    /**
     * Clear messages of type.
     *
     * @param string $type Type
     *
     * @return void
     */
    public function clearMessages($type = null)
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        $this->getFlashBag()->get($type);
    }

    /**
     * Set session variable.
     *
     * @param string $key       Key
     * @param mixed  $default   Default = null
     * @param string $namespace Namespace
     *
     * @throws InvalidArgumentException If illegal namespace received
     *
     * @return mixed
     */
    public function get($key, $default = null, $namespace = '/')
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        return parent::get($key, $default);
    }

    /**
     * Set a session variable.
     *
     * @param string $key       Key
     * @param mixed  $value     Value
     * @param string $namespace Namespace
     *
     * @throws InvalidArgumentException If illegal namespace received
     *
     * @return void
     */
    public function set($key, $value, $namespace = '/')
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        parent::set($key, $value);
    }

    /**
     * Delete session variable by key.
     *
     * @param string $key       Key
     * @param string $namespace Namespace
     *
     * @throws InvalidArgumentException If illegal namespace received
     *
     * @return void
     */
    public function del($key, $namespace = '/')
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        parent::remove($key);
    }

    /**
     * Check if session variable key exists.
     *
     * @param string $key       Key
     * @param string $namespace Namespace
     *
     * @throws InvalidArgumentException If illegal namespace received
     *
     * @return boolean
     */
    public function has($key, $namespace = '/')
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        return parent::has($key);
    }

    /**
     * Clear an entire namespace.
     *
     * Use with caution, and only if you know that no other code makes use of the namespace.
     *
     * @param string $namespace Namespace
     *
     * @deprecated
     *
     * @throws InvalidArgumentException If illegal namespace received
     *
     * @return void
     */
    public function clearNamespace($namespace)
    {
        @trigger_error('Zikula_Session is deprecated, please use Symfony session instead.', E_USER_DEPRECATED);

        $this->remove($namespace);
    }
}
