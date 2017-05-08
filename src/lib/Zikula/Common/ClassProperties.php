<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common;

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
 *
 * @deprecated remove in Core-2.0
 */
class ClassProperties
{
    /**
     * Load all keys from properties to resepective properties using set{$key}.
     *
     * @param object $object     The object to act upon
     * @param array  $properties The associative array of properties
     *
     * @return void
     */
    public static function load($object, array $properties)
    {
        if (!$properties) {
            return;
        }

        $reflection = new \ReflectionObject($object);
        $className = $reflection->getName();
        $methods = $reflection->getMethods();
        $methodMap = [];
        foreach ($methods as $method) {
            $methodMap[strtolower($method->name)] = $method->name;
        }

        foreach ($properties as $k => $v) {
            $lookup = 'set'.strtolower($k);
            if (isset($methodMap[$lookup])) {
                $reflectionMethod = new \ReflectionMethod($className, $methodMap[$lookup]);
                $reflectionMethod->invoke($object, $v);
            }
        }
    }
}
