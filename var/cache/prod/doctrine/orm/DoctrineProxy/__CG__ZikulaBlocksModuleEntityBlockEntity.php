<?php

namespace DoctrineProxy\__CG__\Zikula\BlocksModule\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class BlockEntity extends \Zikula\BlocksModule\Entity\BlockEntity implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'bid', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'bkey', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'blocktype', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'title', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'description', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'properties', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'module', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'filters', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'active', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'last_update', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'language', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'placements', 'reflection'];
        }

        return ['__isInitialized__', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'bid', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'bkey', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'blocktype', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'title', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'description', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'properties', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'module', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'filters', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'active', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'last_update', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'language', '' . "\0" . 'Zikula\\BlocksModule\\Entity\\BlockEntity' . "\0" . 'placements', 'reflection'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (BlockEntity $proxy) {
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
    public function getBid(): ?int
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getBid();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBid', []);

        return parent::getBid();
    }

    /**
     * {@inheritDoc}
     */
    public function setBid(int $bid): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBid', [$bid]);

        parent::setBid($bid);
    }

    /**
     * {@inheritDoc}
     */
    public function getBkey(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBkey', []);

        return parent::getBkey();
    }

    /**
     * {@inheritDoc}
     */
    public function setBkey(string $bkey): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBkey', [$bkey]);

        parent::setBkey($bkey);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlocktype(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBlocktype', []);

        return parent::getBlocktype();
    }

    /**
     * {@inheritDoc}
     */
    public function setBlocktype(string $blocktype): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBlocktype', [$blocktype]);

        parent::setBlocktype($blocktype);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTitle', []);

        return parent::getTitle();
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle(string $title): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTitle', [$title]);

        parent::setTitle($title);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription(string $description): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', [$description]);

        parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties(): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getProperties', []);

        return parent::getProperties();
    }

    /**
     * {@inheritDoc}
     */
    public function setProperties(array $properties = array (
)): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProperties', [$properties]);

        parent::setProperties($properties);
    }

    /**
     * {@inheritDoc}
     */
    public function getModule(): \Zikula\ExtensionsModule\Entity\ExtensionEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModule', []);

        return parent::getModule();
    }

    /**
     * {@inheritDoc}
     */
    public function setModule(\Zikula\ExtensionsModule\Entity\ExtensionEntity $module): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModule', [$module]);

        parent::setModule($module);
    }

    /**
     * {@inheritDoc}
     */
    public function getFilters(): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFilters', []);

        return parent::getFilters();
    }

    /**
     * {@inheritDoc}
     */
    public function setFilters(array $filters = array (
)): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFilters', [$filters]);

        parent::setFilters($filters);
    }

    /**
     * {@inheritDoc}
     */
    public function getActive(): int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActive', []);

        return parent::getActive();
    }

    /**
     * {@inheritDoc}
     */
    public function setActive(int $active): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setActive', [$active]);

        parent::setActive($active);
    }

    /**
     * {@inheritDoc}
     */
    public function getLast_Update(): \DateTime
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLast_Update', []);

        return parent::getLast_Update();
    }

    /**
     * {@inheritDoc}
     */
    public function setLast_Update(): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLast_Update', []);

        parent::setLast_Update();
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLanguage', []);

        return parent::getLanguage();
    }

    /**
     * {@inheritDoc}
     */
    public function setLanguage(string $language): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLanguage', [$language]);

        parent::setLanguage($language);
    }

    /**
     * {@inheritDoc}
     */
    public function getPlacements(): \Doctrine\Common\Collections\Collection
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPlacements', []);

        return parent::getPlacements();
    }

    /**
     * {@inheritDoc}
     */
    public function addPlacement(\Zikula\BlocksModule\Entity\BlockPlacementEntity $placement): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addPlacement', [$placement]);

        parent::addPlacement($placement);
    }

    /**
     * {@inheritDoc}
     */
    public function removePlacement(\Zikula\BlocksModule\Entity\BlockPlacementEntity $placement): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removePlacement', [$placement]);

        parent::removePlacement($placement);
    }

    /**
     * {@inheritDoc}
     */
    public function getPositions(): \Doctrine\Common\Collections\ArrayCollection
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPositions', []);

        return parent::getPositions();
    }

    /**
     * {@inheritDoc}
     */
    public function setPositions(\Doctrine\Common\Collections\ArrayCollection $positions): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPositions', [$positions]);

        parent::setPositions($positions);
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
