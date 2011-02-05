<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Common
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ClassLoader for PHP 5.3 namespaces and PEAR style class name mapping.
 *
 * Example for PHP 5.3+ namespace Zikula\Core
 * $autoloader = new ClassLoader('Zikula\Core');
 * $autoloader->register();
 *
 * Example for based PEAR class to directory mappings like Form_Plugin => Form/Plugin.php
 * This creates a fake namespace Form
 * $autoloader = new ClassLoader('Form', '/path/to/Form);
 * $autoloader->setSeparator('_');
 * $autoloader->register();
 *
 * Namespaces can also be empty also for classes in the root namespace by defining ''.
 */
class ClassLoader
{
    /**
     * Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Path.
     *
     * @var string
     */
    protected $path;

    /**
     * Namespace separator.
     *
     * @var string valid characters \ and _.
     */
    protected $separator;

    /**
     * Constructor.
     *
     * @param string $namespace Default ''.
     * @param string $path      Deafult ''.
     * @param string $separator Default \.
     */
    public function __construct($namespace = '', $path = '', $separator = '\\')
    {
        $this->namespace = $namespace;
        $this->path = $path;
        $this->separator = $separator;
    }

    // getters

    /**
     * Returns the registered include path.
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the namespace separator.
     *
     * @return string $separator
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    // setters

    /**
     * Set the include path for this namespace.
     *
     * @param string $path Search path for this namespace.
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Set the namespace of this autoloader.
     *
     * @param string $separator The namespace separator, (e.g. \ or _).
     *
     * @return void
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * Register this autloader in the SPL autoload stack.
     *
     * @return void
     */
    public function register()
    {
        \spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Register this autloader in the SPL autoload stack.
     *
     * @return void
     */
    public function unregister()
    {
        \spl_autoload_unregister(array($this, 'autoload'));
    }

    /**
     * Get class include path.
     *
     * @param string $class The class name to autoload.
     *
     * @return string|boolean $file Path or boolean false if this loader does apply.
     */
    public function getClassIncludePath($class)
    {
        // execute only if namespace is empty or namespace+separator matches in the beginning of the requested class:
        // namespace 'Foo', class Another\BadFoo\Class should not match (namespace somewhere in path).
        // namespace 'Foo', class Foo\BadFoo\Class should match and become Foo/BadFoo/Class.php
        // namespace 'Bar', separator '_', class Bar should match and become Bar.php
        // namespace 'Bar', separator '_', class Bar_Exception should match and become Bar\Exception.php
        if (empty($this->namespace) || !empty($this->namespace) && \strpos($class, $this->namespace.$this->separator) === 0 || $class == $this->namespace) {
            // replace namespace separator with \DIRECTORY_SEPARATOR
            $file = \str_replace($this->separator, \DIRECTORY_SEPARATOR, $class);

            // Translate PEAR style classnames to paths
            $file = \str_replace('_', \DIRECTORY_SEPARATOR, $file) . '.php';

            // add include path if required
            if (!empty($this->path)) {
                $file = $this->path . \DIRECTORY_SEPARATOR . $file;
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
        $file = $this->getClassIncludePath($class);
        // Handle empty namespace autoloaders where we must test for file_exists().
        //if (!$file || (empty($this->namespace) && \file_exists($file) === false)) {
        if (!$file) {
            return;
        }

        // Must test in case get_class() will try to load the class to test if the class is available.
        // If it doesnt exist we'll get an E_NOTICE (drak).
        if (\file_exists($file)) {
            include $file;
        }
    }
        
}
