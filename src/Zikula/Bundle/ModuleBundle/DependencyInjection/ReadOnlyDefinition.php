<?php

namespace Zikula\ModulesBundle\DependencyInjection;

/**
 *
 */
class ReadOnlyDefinition
{
    /**
     * @var \Symfony\Component\DependencyInjection\Definition 
     */
    private $delegate;
    
    public function __construct($delegate)
    {
        $this->delegate = $delegate;
    }
    
    public function __call($name, $arguments)
    {
        if(substr($name, 0, 3) == 'set' 
                || substr($name, 0, 3) == 'add' 
                || substr($name, 0, 6) == 'remove' 
                || substr($name, 0, 4) == 'clear' 
                || substr($name, 0, 7) == 'replace') {
            throw new \LogicException('forbidden');
        }
        
        return call_user_func_array(array($this->delegate, $name), $arguments);
    }
}
