<?php

namespace Zikula\ModuleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 *
 */
class SandboxContainerBuilder extends ContainerBuilder
{
    /**
     * @var ContainerBuilder 
     */
    private $delegate;
    
    private $ownServiceIds = array();
    
    public function __construct($delegate, &$ownServiceIds)
    {
        $this->delegate = $delegate;
        $this->ownServiceIds =& $ownServiceIds;
    }
    
    public function registerExtension(\Symfony\Component\DependencyInjection\Extension\ExtensionInterface $extension)
    {
        throw new \LogicException('forbidden');
    }
    
    public function addCompilerPass(\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, $type = PassConfig::TYPE_BEFORE_OPTIMIZATION)
    {
       throw new \LogicException('forbidden');
    }
    
    public function merge(ContainerBuilder $container)
    {
        throw new \LogicException('forbidden');
    }
    
    public function set($id, $service, $scope = self::SCOPE_CONTAINER)
    {
        if(!in_array($id, $this->ownServiceIds)) {
            if($this->delegate->has($id)) {
                throw new \LogicException('forbidden');
            } else {
                $this->ownServiceIds[] = $id;
            }
        }
        
        $this->delegate->set($id, $service, $scope);
    }
    
    public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if(!in_array($id, $this->ownServiceIds)) {
            throw new \LogicException('forbidden');
        }
        
        return $this->delegate->get($id, $invalidBehavior);
    }
    
    public function setDefinition($id, \Symfony\Component\DependencyInjection\Definition $definition)
    {
        if(!in_array($id, $this->ownServiceIds)) {
            if($this->delegate->hasDefinition($id)) {
                throw new \LogicException('forbidden');
            } else {
                $this->ownServiceIds[] = $id;
            }
        }
        
        foreach($definition->getTags() as $tag => $attributes) {
            if($tag != 'kernel.event_listener') {
                throw new \LogicException('forbidden');
            }
        }
        
        $this->delegate->setDefinition($id, $definition);
    }
    
    public function getDefinition($id)
    {
        $def = $this->delegate->getDefinition($id);
        
        if(!in_array($id, $this->ownServiceIds)) {
            throw new \LogicException('forbidden');
        }
        
        return $def;
    }
    
    public function addAliases(array $aliases)
    {
        return $this->delegate->addAliases($aliases);
    }
    
    public function addDefinitions(array $definitions)
    {
        return $this->delegate->addDefinitions($definitions);
    }
    
    public function addObjectResource($object)
    {
        return $this->delegate->addObjectResource($object);
    }
    
    public function addResource(\Symfony\Component\Config\Resource\ResourceInterface $resource)
    {
        return $this->delegate->addResource($resource);
    }
    
    public function addScope(\Symfony\Component\DependencyInjection\ScopeInterface $scope)
    {
        return $this->delegate->addScope($scope);
    }
    
    public function compile()
    {
        return $this->delegate->compile();
    }
    
    public function enterScope($name)
    {
        return $this->delegate->enterScope($name);
    }
    
    public function findDefinition($id)
    {
        return $this->delegate->findDefinition($id);
    }
    
    public function findTaggedServiceIds($name)
    {
        return $this->delegate->findTaggedServiceIds($name);
    }
    
    public function getAlias($id)
    {
        return $this->delegate->getAlias($id);
    }
    
    public function getAliases()
    {
        return $this->delegate->getAliases();
    }
    
    public function getCompiler()
    {
        return $this->delegate->getCompiler();
    }
    
    public function getCompilerPassConfig()
    {
        return $this->delegate->getCompilerPassConfig();
    }
    
    public function getDefinitions()
    {
        return $this->delegate->getDefinitions();
    }
    
    public function getExtension($name)
    {
        return $this->delegate->getExtension($name);
    }
    
    public function getExtensionConfig($name)
    {
        return $this->delegate->getExtensionConfig($name);
    }
    
    public function getExtensions()
    {
        return $this->delegate->getExtensions();
    }
    
    public function getParameter($name)
    {
        return $this->delegate->getParameter($name);
    }
    
    public function getParameterBag()
    {
        return $this->delegate->getParameterBag();
    }
    
    public function getResources()
    {
        return $this->delegate->getResources();
    }
    
    public function getScopeChildren()
    {
        return $this->delegate->getScopeChildren();
    }
    
    public function getScopes()
    {
        return $this->delegate->getScopes();
    }
    
    public static function getServiceConditionals($value)
    {
        return $this->delegate->getServiceConditionals($value);
    }
    
    public function getServiceIds()
    {
        return $this->delegate->getServiceIds();
    }
    
    public function has($id)
    {
        return $this->delegate->has($id);
    }
    
    public function hasAlias($id)
    {
        return $this->delegate->hasAlias($id);
    }
    
    public function hasDefinition($id)
    {
        return $this->delegate->hasDefinition($id);
    }
    
    public function hasExtension($name)
    {
        return $this->delegate->hasExtension($name);
    }
    
    public function hasParameter($name)
    {
        return $this->delegate->hasParameter($name);
    }
    
    public function hasScope($name)
    {
        return $this->delegate->hasScope($name);
    }
    
    public function isFrozen()
    {
        return $this->delegate->isFrozen();
    }
    
    public function isScopeActive($name)
    {
        return $this->delegate->isScopeActive($name);
    }
    
    public function leaveScope($name)
    {
        return $this->delegate->leaveScope($name);
    }
    
    public function loadFromExtension($extension, array $values = array())
    {
        return $this->delegate->loadFromExtension($extension, $values);
    }
    
    public function register($id, $class = null)
    {
        return $this->delegate->register($id, $class);
    }
    
    public function removeAlias($alias)
    {
        return $this->delegate->removeAlias($alias);
    }
    
    public function removeDefinition($id)
    {
        return $this->delegate->removeDefinition($id);
    }
    
    public function resolveServices($value)
    {
        return $this->delegate->resolveServices($value);
    }
    
    public function setAlias($alias, $id)
    {
        return $this->delegate->setAlias($alias, $id);
    }
    
    public function setAliases(array $aliases)
    {
        return $this->delegate->setAliases($aliases);
    }
    
    public function setDefinitions(array $definitions)
    {
        return $this->delegate->setDefinitions($definitions);
    }
    
    public function setParameter($name, $value)
    {
        return $this->delegate->setParameter($name, $value);
    }
}
