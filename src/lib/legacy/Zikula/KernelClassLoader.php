<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ClassLoader for PHP 5.3 namespaces and PEAR style class name mapping.
 *
 * Based on Zikula\Common\ClassLoader but is an all in one class loader
 * for use with the Zikula Kernel.
 */
class Zikula_KernelClassLoader
{
    /**
     * Flag.
     *
     * @var boolean
     */
    protected $registered = false;

    /**
     * Storage for class namespaces.
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * Register namespace on stack.
     *
     * @param string $namespace Namespace.
     * @param string $path      Path to the class namespace.
     * @param string $separator Namespace separator.
     *
     * @throws LogicException If already registered.
     *
     * @return void
     */
    public function register($namespace, $path = '', $separator = '\\')
    {
        if (isset($this->namespaces[$namespace])) {
            throw new LogicException(sprintf('%s is already registered with this autoloader', $namespace));
        }

        $this->namespaces[$namespace] = array('path' => str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $path)), 'separator' => $separator);

        // Reverse sort keys, allows location of subnamespaces in different paths
        krsort($this->namespaces);
    }

    /**
     * Unregister namespace from autoloader.
     *
     * @param string $namespace Namespace.
     *
     * @throws LogicException If not registered.
     *
     * @return void
     */
    public function unregister($namespace)
    {
        if (!isset($this->namespaces[$namespace])) {
            throw new LogicException(sprintf('%s is not registered with this autoloader', $namespace));
        }

        unset($this->namespaces[$namespace]);
    }

    /**
     * Has autoloader check.
     *
     * @param string $namespace Namespace.
     *
     * @return boolean
     */
    public function hasAutoloader($namespace)
    {
        return (bool)array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Register this autoloader in the SPL autoload stack.
     *
     * @throws LogicException If already registered.
     *
     * @return void
     */
    public function spl_autoload_register()
    {
        if (!$this->registered) {
            spl_autoload_register(array($this, 'autoload'));
            $this->registered = true;
        } else {
            throw new LogicException('Already registered on SPL autoloader stack');
        }
    }

    /**
     * Register this autoloader in the SPL autoload stack.
     *
     * @throws LogicException If not registered.
     *
     * @return void
     */
    public function spl_autoload_unregister()
    {
        if ($this->registered) {
            spl_autoload_unregister(array($this, 'autoload'));
            $this->registered = false;
        } else {
            throw new LogicException('Not registered on SPL autoloader stack');
        }

    }

    /**
     * Get class include path.
     *
     * @param string $namespace The class name to autoload.
     * @param array  $array     Metadata about this namespace.
     * @param string $class     Class name.
     *
     * @return string|boolean $file Path or boolean false if this loader does apply.
     */
    public function getClassIncludePath($namespace, array $array, $class)
    {
        // execute only if namespace is empty or namespace+separator matches in the beginning of the requested class:
        // namespace 'Foo', class Another\BadFoo\Class should not match (namespace somewhere in path).
        // namespace 'Foo', class Foo\BadFoo\Class should match and become Foo/BadFoo/Class.php
        // namespace 'Bar', separator '_', class Bar should match and become Bar.php
        // namespace 'Bar', separator '_', class Bar_Exception should match and become Bar\Exception.php
        if (strpos($class, $namespace.$array['separator']) === 0 || $class == $namespace || empty($namespace)) {
            // replace namespace separator with \DIRECTORY_SEPARATOR
            $file = str_replace($array['separator'], DIRECTORY_SEPARATOR, $class);

            // Translate PEAR style classnames to paths
            $file = str_replace('_', DIRECTORY_SEPARATOR, $file) . '.php';

            // add include path if required
            if (!empty($array['path'])) {
                $file = $array['path'] . DIRECTORY_SEPARATOR . $file;
            }

            return $file;
        }

        return false;
    }

    /**
     * Autoloader.
     *
     * @param string $class Class to load.
     *
     * @return void
     */
    public function autoload($class)
    {
        if ($class[0] == '\\') {
            // This is a workaround for a bug that exists in PHP 5.3.2 where class names
            // are not passed consistently (sometimes \Foo and sometimes Foo).  This is
            // fixed since PHP 5.3.3
            $class == substr($class, 1, strlen($class));
        }

        foreach ($this->namespaces as $namespace => $array) {
            $file = $this->getClassIncludePath($namespace, $array, $class);
            if ($file) {
                break;
            }
        }

        // Must test in case get_class() will try to load the class to test if the class is available.
        // If it doesnt exist we'll get an E_NOTICE (drak).
        if (file_exists($file)) {
            include $file;
        }
    }

}
