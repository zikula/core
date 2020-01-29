<?php

namespace DoctrineProxy\__CG__\Zikula\CategoriesModule\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class CategoryRegistryEntity extends \Zikula\CategoriesModule\Entity\CategoryRegistryEntity implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'modname', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'entityname', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'property', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'category', 'cr_uid', 'lu_uid', 'cr_date', 'lu_date', 'obj_status', 'reflection'];
        }

        return ['__isInitialized__', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'modname', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'entityname', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'property', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryRegistryEntity' . "\0" . 'category', 'cr_uid', 'lu_uid', 'cr_date', 'lu_date', 'obj_status', 'reflection'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (CategoryRegistryEntity $proxy) {
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
    public function setId(int $id): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setId', [$id]);

        parent::setId($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getModname(): ?string
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
    public function getEntityname(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEntityname', []);

        return parent::getEntityname();
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityname(string $entityname): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEntityname', [$entityname]);

        parent::setEntityname($entityname);
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getProperty', []);

        return parent::getProperty();
    }

    /**
     * {@inheritDoc}
     */
    public function setProperty(string $property): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProperty', [$property]);

        parent::setProperty($property);
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory(): ?\Zikula\CategoriesModule\Entity\CategoryEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCategory', []);

        return parent::getCategory();
    }

    /**
     * {@inheritDoc}
     */
    public function setCategory(\Zikula\CategoriesModule\Entity\CategoryEntity $category): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCategory', [$category]);

        parent::setCategory($category);
    }

    /**
     * {@inheritDoc}
     */
    public function getCr_date(): \DateTime
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCr_date', []);

        return parent::getCr_date();
    }

    /**
     * {@inheritDoc}
     */
    public function setCr_date(\DateTime $cr_date): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCr_date', [$cr_date]);

        parent::setCr_date($cr_date);
    }

    /**
     * {@inheritDoc}
     */
    public function getCr_uid(): \Zikula\UsersModule\Entity\UserEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCr_uid', []);

        return parent::getCr_uid();
    }

    /**
     * {@inheritDoc}
     */
    public function setCr_uid(\Zikula\UsersModule\Entity\UserEntity $cr_uid): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCr_uid', [$cr_uid]);

        parent::setCr_uid($cr_uid);
    }

    /**
     * {@inheritDoc}
     */
    public function getLu_date(): \DateTime
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLu_date', []);

        return parent::getLu_date();
    }

    /**
     * {@inheritDoc}
     */
    public function setLu_date(\DateTime $lu_date): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLu_date', [$lu_date]);

        parent::setLu_date($lu_date);
    }

    /**
     * {@inheritDoc}
     */
    public function getLu_uid(): \Zikula\UsersModule\Entity\UserEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLu_uid', []);

        return parent::getLu_uid();
    }

    /**
     * {@inheritDoc}
     */
    public function setLu_uid(\Zikula\UsersModule\Entity\UserEntity $lu_uid): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLu_uid', [$lu_uid]);

        parent::setLu_uid($lu_uid);
    }

    /**
     * {@inheritDoc}
     */
    public function getObj_status(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getObj_status', []);

        return parent::getObj_status();
    }

    /**
     * {@inheritDoc}
     */
    public function setObj_status(string $obj_status): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setObj_status', [$obj_status]);

        parent::setObj_status($obj_status);
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
