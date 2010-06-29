<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Class Properties.
 *
 * For use in universal constructor pattern.
 *
 * <samp>
 * class Foo
 * {
 *     protected $data;
 *     public function __construct($array)
 *     {
 *         ClassProperties::load($array);
 *     }
 * }
 *
 * $foo = new Foo('data' => 'Hello world);
 * </samp>
 */
class Zikula_ClassProperties
{

    /**
     * Load all keys from properties to resepective properties using set{$key}.
     *
     * @param object $object     The object to act upon.
     * @param array  $properties The associative array of properties.
     *
     * @return void
     */
    public static function load($object, array $properties)
    {
        if (!$properties) {
            return;
        }

        $reflection = new ReflectionObject($object);
        $className = $reflection->getName();
        $methods = $reflection->getMethods();
        $methodMap = array();
        foreach ($methods as $method) {
            $methodMap[strtolower($method->name)] = $method->name;
        }

        foreach ($properties as $k => $v) {
            $lookup = 'set'.strtolower($k);
            if (isset($methodMap[$lookup])) {
                $reflectionMethod = new ReflectionMethod($className, $methodMap[$lookup]);
                $reflectionMethod->invoke($object, $v);
            }
        }
    }
}
