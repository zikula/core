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

    public function start()
    {
        $config = [
            'gc_probability' => System::getVar('gc_probability'),
            'gc_divisor' => 10000,
            'gc_maxlifetime' => System::getVar('secinactivemins'),
        ];

        $path = System::getBaseUri();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }

        $config['cookie_path'] = $path;

        $host = System::serverGetVar('HTTP_HOST');

        if (($pos = strpos($host, ':')) !== false) {
            $host = substr($host, 0, $pos);
        }

        // PHP configuration variables
        // Set lifetime of session cookie
        $seclevel = System::getVar('seclevel');
        switch ($seclevel) {
            case 'High':
                // Session lasts duration of browser
                $lifetime = 0;
                // Referer check
                // ini_set('session.referer_check', $host.$path);
                $config['referer_check'] = $host;
                break;
            case 'Medium':
                // Session lasts set number of days
                $lifetime = System::getVar('secmeddays') * 86400;
                break;
            case 'Low':
            default:
                // (Currently set to 1 year)
                $lifetime = 31536000;
                break;
        }

        $config['cookie_lifetime'] = $lifetime;

        $this->storage->setOptions($config);

        return parent::start();
    }

    /**
     * Check if session has started.
     *
     * @return boolean
     */
    public function hasStarted()
    {
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
        $this->migrate();
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
        $this->getFlashBag()->add($type, $value);
    }

    /**
     * Get special attributes by type.
     *
     * @param string $type    Type.
     * @param mixed  $default Default value to return (default = []).
     *
     * @return mixed
     */
    public function getMessages($type, $default = [])
    {
        return $this->getFlashBag()->get($type, $default);
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
        return $this->getFlashBag()->has($type);
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
        $this->getFlashBag()->get($type);
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
        return parent::get($key, $default);
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
        parent::set($key, $value);
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
        parent::remove($key);
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
        return parent::has($key);
    }

    /**
     * Clear an entire namespace.
     *
     * Use with caution, and only if you know that no other code makes use of the namespace.
     *
     * @param string $namespace Namespace.
     *
     * @deprecated
     *
     * @throws InvalidArgumentException If illegal namespace received.
     *
     * @return void
     */
    public function clearNamespace($namespace)
    {
        $this->remove($namespace);
    }
}
