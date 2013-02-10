<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

class MetaData
{
    private $name;
    private $class;
    private $namespace;
    private $basePath;
    private $psr0;

    public function __construct($json)
    {
        $this->name = $json->name;
        $this->class = $json->extra->zikula->class;
        $this->namespace = substr($this->class, 0, strrpos($this->class, '\\')+1);
        $this->basePath = $json->extra->zikula->{'base-path'};
        $this->psr0 = $json->autoload->{'psr-0'};
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPsr0()
    {
        return $this->psr0;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}