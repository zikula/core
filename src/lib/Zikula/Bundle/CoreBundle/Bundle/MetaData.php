<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Zikula\Common\Translator\TranslatorTrait;

class MetaData implements \ArrayAccess
{
    use TranslatorTrait;

    const TYPE_MODULE = 2;

    const TYPE_SYSTEM = 3;

    const TYPE_CORE = 4;

    const DEPENDENCY_REQUIRED = 1;

    const DEPENDENCY_RECOMMENDED = 2;

    const DEPENDENCY_CONFLICTS = 3;

    private $name;

    private $version;

    private $description;

    private $type;

    private $dependencies;

    private $shortName;

    private $class;

    private $namespace;

    private $basePath;

    private $rootPath;

    private $autoload;

    private $displayName;

    private $url;

    private $oldNames;

    private $capabilities;

    private $securitySchema;

    private $extensionType;

    private $coreCompatibility;

    public function __construct($json)
    {
        $this->name = $json['name'];
        $this->version = isset($json['version']) ? $json['version'] : '';
        $this->description = isset($json['description']) ? $json['description'] : '';
        $this->type = $json['type'];
        $this->dependencies = $this->formatDependencies($json);
        $this->shortName = $json['extra']['zikula']['short-name'];
        $this->class = $json['extra']['zikula']['class'];
        $this->namespace = substr($this->class, 0, strrpos($this->class, '\\') + 1);
        $this->basePath = $json['extra']['zikula']['base-path'];
        $this->rootPath = $json['extra']['zikula']['root-path'];
        $this->autoload = $json['autoload'];
        $this->displayName = isset($json['extra']['zikula']['displayname']) ? $json['extra']['zikula']['displayname'] : '';
        $this->url = isset($json['extra']['zikula']['url']) ? $json['extra']['zikula']['url'] : '';
        $this->oldNames = isset($json['extra']['zikula']['oldnames']) ? $json['extra']['zikula']['oldnames'] : [];
        $this->capabilities = isset($json['extra']['zikula']['capabilities']) ? $json['extra']['zikula']['capabilities'] : [];
        $this->securitySchema = isset($json['extra']['zikula']['securityschema']) ? $json['extra']['zikula']['securityschema'] : [];
        $this->extensionType = isset($json['extensionType']) ? $json['extensionType'] : self::TYPE_MODULE;
        $this->coreCompatibility = $json['extra']['zikula']['core-compatibility'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getPsr0()
    {
        return isset($this->autoload['psr-0']) ? $this->autoload['psr-0'] : [];
    }

    public function getPsr4()
    {
        return isset($this->autoload['psr-4']) ? $this->autoload['psr-4'] : [];
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

    public function getDescription()
    {
        $this->confirmTranslator();

        $description = $this->__(/** @Ignore */$this->description);

        return (empty($description)) ? $this->description : $description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function getDisplayName()
    {
        $this->confirmTranslator();

        $display_name = $this->__(/** @Ignore */$this->displayName);

        return (empty($display_name)) ? $this->displayName : $display_name;
    }

    public function setDisplayName($name)
    {
        $this->displayName = $name;
    }

    public function getUrl($translated = true)
    {
        $this->confirmTranslator();

        $url = $this->__(/** @Ignore */$this->url);

        return $translated ? ((empty($url)) ? $this->$url : $url) : $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getOldNames()
    {
        return $this->oldNames;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function getSecuritySchema()
    {
        return $this->securitySchema;
    }

    public function getExtensionType()
    {
        return $this->extensionType;
    }

    public function getCoreCompatibility()
    {
        return $this->coreCompatibility;
    }

    private function formatDependencies(array $json)
    {
        $dependencies = [];
        if (!empty($json['require'])) {
            foreach ($json['require'] as $package => $version) {
                $dependencies[] = [
                    'modname' => $package,
                    'minversion' => $version,
                    'maxversion' => $version,
                    'status' => self::DEPENDENCY_REQUIRED
                ];
            }
        } else {
            $dependencies[] = [
                'modname' => 'zikula/core',
                'minversion' => '>=1.4.1 <3.0.0',
                'maxversion' => '>=1.4.1 <3.0.0',
                'status' => self::DEPENDENCY_REQUIRED
            ];
        }
        if (!empty($json['suggest'])) {
            foreach ($json['suggest'] as $package => $reason) {
                if (strpos($package, ':')) {
                    list($name, $version) = explode(':', $package, 2);
                } else {
                    $name = $package;
                    $version = '-1';
                }
                $dependencies[] = [
                    'modname' => $name,
                    'minversion' => $version,
                    'maxversion' => $version,
                    'reason' => $reason,
                    'status' => self::DEPENDENCY_RECOMMENDED
                ];
            }
        }

        return $dependencies;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    private function confirmTranslator()
    {
        if (!isset($this->translator)) {
            throw new PreconditionRequiredHttpException(sprintf("The translator property is not set correctly in %s", __CLASS__));
        }
    }

    /**
     * Theme MetaData as array
     *
     * @return array
     */
    public function getThemeFilteredVersionInfoArray()
    {
        $capabilities = $this->getCapabilities();

        return [
            'name' => $this->getShortName(),
            'type' => $this->getExtensionType(),
            'displayname' => $this->getDisplayName(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
//            'capabilities' => $this->getCapabilities(),
            // It would be better to add capabilities to DB and move to inverse in legacy code and refactor later checks. refs #3644
            'user' => isset($capabilities['user']) ? $capabilities['user'] : true,
            'admin' => isset($capabilities['admin']) ? $capabilities['admin'] : true,
            'system' => isset($capabilities['system']) ? $capabilities['system'] : false,
            'xhtml' => isset($capabilities['xhtml']) ? $capabilities['xhtml'] : true, // @todo is this valid any longer?
        ];
    }

    /**
     * Module MetaData as array
     *
     * @return array
     */
    public function getFilteredVersionInfoArray()
    {
        return [
            'name' => $this->getShortName(),
            'type' => $this->getExtensionType(),
            'displayname' => $this->getDisplayName(),
            'oldnames' => $this->getOldNames(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'url' => $this->getUrl(),
            'capabilities' => $this->getCapabilities(),
            'securityschema' => $this->getSecuritySchema(),
            'dependencies' => $this->getDependencies(),
            'corecompatibility' => $this->getCoreCompatibility(),
            'core_max' => '' // core_min is set from corecompatibility
        ];
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        $method = "get" . ucwords($offset);

        return $this->$method();
    }

    public function offsetSet($offset, $value)
    {
        // not allowed
        throw new \Exception('Setting values by array access is not allowed.');
    }

    public function offsetUnset($offset)
    {
        // not allowed
        throw new \Exception('Unsetting values by array access is not allowed.');
    }
}
