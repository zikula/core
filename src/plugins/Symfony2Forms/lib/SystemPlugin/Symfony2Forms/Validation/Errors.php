<?php

namespace SystemPlugin\Symfony2Forms\Validation;

/**
 *
 */
class Errors
{
    private $errors;
    private $fields;
    
    public function __construct()
    {
        $this->errors = array();
        $this->fields = array();
    }
    
    /**
     * @param string $name array key / class property name
     * @return Errors 
     */
    public function getField($name) 
    {
        if(!isset($this->fields[$name])) {
            $this->fields[$name] = new Errors();
        }
        
        return $this->fields[$name];
    }
    
    public function addError($msg) 
    {
        $this->errors[] = $msg;
    }
    
    public function getErrors() 
    {
        return $this->errors;
    }
    
    public function getFields() 
    {
        return $this->fields;
    }
    
    public function hasErrors() 
    {
        return count($this->errors) > 0;
    }
    
    public function setFromErrorsObject(Errors $errorsObj) 
    {
        $this->fields = $errorsObj->getFields();
        $this->errors = $errorsObj->getErrors();
    }
}

