<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ClassLoader;

/**
 * Autoloader checking if the class is really defined in the file found.
 *
 * The DebugClassLoader will wrap all registered autoloaders providing a
 * findFile method and will throw an exception if a file is found but does
 * not declare the class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christophe Coevoet <stof@notk.org>
 *
 * @api
 */
class DebugClassLoader
{
    private $classFinder;

    /**
     * Constructor.
     *
     * @param object $classFinder
     *
     * @api
     */
    public function __construct($classFinder)
    {
        $this->classFinder = $classFinder;
    }

    /**
     * Replaces all autoloaders implementing a findFile method by a DebugClassLoader wrapper.
     */
    static public function enable()
    {
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }

        foreach ($functions as $function) {
            spl_autoload_unregister($function);
        }

        foreach ($functions as $function) {
            if (is_array($function) && method_exists($function[0], 'findFile')) {
                $function = array(new static($function[0]), 'loadClass');
            }

            spl_autoload_register($function);
        }
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     * 
     * @return Boolean|null True, if loaded
     */
    public function loadClass($class)
    {
        if ($file = $this->classFinder->findFile($class)) {
            require $file;

            if (!class_exists($class, false) && !interface_exists($class, false) && (!function_exists('trait_exists') || !trait_exists($class, false))) {
                throw new \RuntimeException(sprintf('The autoloader expected class "%s" to be defined in file "%s". The file was found but the class was not in it, the class name or namespace probably has a typo.', $class, $file));
            }

            return true;
        }
    }
}
