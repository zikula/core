<?php

namespace Zikula\Core\Forms\Validation\Constraints;

use SystemPlugin\Symfony2Forms\Validation\Constraint;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class Url implements Constraint
{
    const REGEX = '@^(%s)://[^\s/$.?#].[^\s]*$@iS';
    
    private $protocols;
    
    public function __construct(array $protocols = array('http','https'))
    {
        $this->protocols = $protocols;
    }
    
    public function isValid($value, Errors $error)
    {
        if($value === '' || $value === null) {
            return true;
        }
        
        if(!preg_match(sprintf(self::REGEX, implode('|', $this->protocols)), $value)) {
            $error->addError('Must be a valid URL');
            return false;
        } else {
            return true;
        }
    }
}

