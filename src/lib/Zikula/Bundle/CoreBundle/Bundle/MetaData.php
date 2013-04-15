<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

class MetaData
{
    private $name;
    private $type;
    private $shortName;
    private $class;
    private $namespace;
    private $basePath;
    private $rootPath;
    private $autoload;

    public function __construct($json)
    {
        $this->name = $json['name'];
        $this->type = $json['type'];
        $this->shortName = $json['extra']['zikula']['short-name'];
        $this->class = $json['extra']['zikula']['class'];
        $this->namespace = substr($this->class, 0, strrpos($this->class, '\\')+1);
        $this->basePath = $json['extra']['zikula']['base-path'];
        $this->rootPath = $json['extra']['zikula']['root-path'];
        $this->autoload = $json['autoload'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getPsr0()
    {
        return $this->autoload['psr-0'];
    }

    public function getAutoload()
    {
        return $this->autoload;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getType()
    {
        return $this->type;
    }
}