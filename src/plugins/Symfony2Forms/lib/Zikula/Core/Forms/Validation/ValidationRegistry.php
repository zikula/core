<?php

namespace Zikula\Core\Forms\Validation;

/**
 *
 */
class ValidationRegistry
{
    private static $classes = array();


    private function __construct()
    {
    }
    
    public static function registerClassValidator($class, Validator $validator) 
    {
        self::$classes[$class] = $validator;
    }
    
    /**
     * @param string $class name of a class
     * @return Validator validator of requested class or null 
     */
    public static function getClassValidator($class) 
    {
        return self::$classes[$class];
    }
}
