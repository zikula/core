<?php

namespace DoctrineProxy\__CG__\Zikula\ExtensionsModule\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class ExtensionVarEntity extends \Zikula\ExtensionsModule\Entity\ExtensionVarEntity implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array<string, null> properties to be lazy loaded, indexed by property name
     */
    public static $lazyPropertiesNames = array (
);

    /**
     * @var array<string, mixed> default values of properties to be lazy loaded, with keys being the property names
     *
     * @see \Doctrine\Common\Proxy\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array (
);



    public function __construct(?\Closure $initializer = null, ?\Closure $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'modname', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'name', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'value', 'reflection'];
        }

        return ['__isInitialized__', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'modname', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'name', '' . "\0" . 'Zikula\\ExtensionsModule\\Entity\\ExtensionVarEntity' . "\0" . 'value', 'reflection'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (ExtensionVarEntity $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy::$lazyPropertiesDefaults as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @deprecated no longer in use - generated code now relies on internal components rather than generated public API
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getModname(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModname', []);

        return parent::getModname();
    }

    /**
     * {@inheritDoc}
     */
    public function setModname(string $modname): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModname', [$modname]);

        parent::setModname($modname);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', []);

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', [$name]);

        parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getValue', []);

        return parent::getValue();
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setValue', [$value]);

        parent::setValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflection(): \ReflectionObject
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getReflection', []);

        return parent::getReflection();
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($key): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'offsetExists', [$key]);

        return parent::offsetExists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($key)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'offsetGet', [$key]);

        return parent::offsetGet($key);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($key, $value): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'offsetSet', [$key, $value]);

        parent::offsetSet($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($key): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'offsetUnset', [$key]);

        parent::offsetUnset($key);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', []);

        return parent::toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function merge(array $array = array (
)): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'merge', [$array]);

        parent::merge($array);
    }

}
