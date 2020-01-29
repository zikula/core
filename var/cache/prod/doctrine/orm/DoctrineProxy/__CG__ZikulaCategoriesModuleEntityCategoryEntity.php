<?php

namespace DoctrineProxy\__CG__\Zikula\CategoriesModule\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class CategoryEntity extends \Zikula\CategoriesModule\Entity\CategoryEntity implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lft', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lvl', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'rgt', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'root', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'parent', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'children', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'is_locked', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'is_leaf', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'name', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'value', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'display_name', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'display_desc', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'status', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'icon', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'attributes', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'cr_uid', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lu_uid', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'cr_date', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lu_date', 'reflection'];
        }

        return ['__isInitialized__', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lft', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lvl', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'rgt', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'root', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'parent', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'children', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'is_locked', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'is_leaf', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'name', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'value', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'display_name', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'display_desc', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'status', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'icon', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'attributes', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'cr_uid', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lu_uid', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'cr_date', '' . "\0" . 'Zikula\\CategoriesModule\\Entity\\CategoryEntity' . "\0" . 'lu_date', 'reflection'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (CategoryEntity $proxy) {
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
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);

        parent::__clone();
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
    public function getParent(): ?\Zikula\CategoriesModule\Entity\CategoryEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getParent', []);

        return parent::getParent();
    }

    /**
     * {@inheritDoc}
     */
    public function setParent(\Zikula\CategoriesModule\Entity\CategoryEntity $parent = NULL): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setParent', [$parent]);

        parent::setParent($parent);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren(): \Doctrine\Common\Collections\Collection
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getChildren', []);

        return parent::getChildren();
    }

    /**
     * {@inheritDoc}
     */
    public function setChildren(\Doctrine\Common\Collections\Collection $children): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setChildren', [$children]);

        parent::setChildren($children);
    }

    /**
     * {@inheritDoc}
     */
    public function getIs_locked(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIs_locked', []);

        return parent::getIs_locked();
    }

    /**
     * {@inheritDoc}
     */
    public function getIsLocked(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsLocked', []);

        return parent::getIsLocked();
    }

    /**
     * {@inheritDoc}
     */
    public function setIs_locked(bool $is_locked): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIs_locked', [$is_locked]);

        parent::setIs_locked($is_locked);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsLocked(bool $isLocked): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsLocked', [$isLocked]);

        parent::setIsLocked($isLocked);
    }

    /**
     * {@inheritDoc}
     */
    public function getIs_leaf(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIs_leaf', []);

        return parent::getIs_leaf();
    }

    /**
     * {@inheritDoc}
     */
    public function getIsLeaf(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsLeaf', []);

        return parent::getIsLeaf();
    }

    /**
     * {@inheritDoc}
     */
    public function setIs_leaf(bool $is_leaf): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIs_leaf', [$is_leaf]);

        parent::setIs_leaf($is_leaf);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsLeaf(bool $isLeaf): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsLeaf', [$isLeaf]);

        parent::setIsLeaf($isLeaf);
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
    public function getValue(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getValue', []);

        return parent::getValue();
    }

    /**
     * {@inheritDoc}
     */
    public function setValue(string $value): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setValue', [$value]);

        parent::setValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplay_name(string $lang = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDisplay_name', [$lang]);

        return parent::getDisplay_name($lang);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDisplayName', []);

        return parent::getDisplayName();
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplay_name(array $display_name): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDisplay_name', [$display_name]);

        parent::setDisplay_name($display_name);
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplayName(array $display_name): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDisplayName', [$display_name]);

        parent::setDisplayName($display_name);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplay_desc(string $lang = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDisplay_desc', [$lang]);

        return parent::getDisplay_desc($lang);
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayDesc(string $lang = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDisplayDesc', [$lang]);

        return parent::getDisplayDesc($lang);
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplay_desc(array $display_desc): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDisplay_desc', [$display_desc]);

        parent::setDisplay_desc($display_desc);
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplayDesc(array $display_desc): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDisplayDesc', [$display_desc]);

        parent::setDisplayDesc($display_desc);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStatus', []);

        return parent::getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStatus', [$status]);

        parent::setStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIcon', []);

        return parent::getIcon();
    }

    /**
     * {@inheritDoc}
     */
    public function setIcon(string $icon): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIcon', [$icon]);

        parent::setIcon($icon);
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
    public function getAttributes(): \Doctrine\Common\Collections\Collection
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAttributes', []);

        return parent::getAttributes();
    }

    /**
     * {@inheritDoc}
     */
    public function setAttributes(\Doctrine\Common\Collections\Collection $attributes): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAttributes', [$attributes]);

        parent::setAttributes($attributes);
    }

    /**
     * {@inheritDoc}
     */
    public function addAttribute(\Zikula\CategoriesModule\Entity\CategoryAttributeEntity $attribute): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addAttribute', [$attribute]);

        parent::addAttribute($attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAttribute(\Zikula\CategoriesModule\Entity\CategoryAttributeEntity $attribute): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeAttribute', [$attribute]);

        parent::removeAttribute($attribute);
    }

    /**
     * {@inheritDoc}
     */
    public function setAttribute(string $name, string $value): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAttribute', [$name, $value]);

        parent::setAttribute($name, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function delAttribute(string $name): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'delAttribute', [$name]);

        parent::delAttribute($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getLft(): int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLft', []);

        return parent::getLft();
    }

    /**
     * {@inheritDoc}
     */
    public function setLft(int $lft): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLft', [$lft]);

        parent::setLft($lft);
    }

    /**
     * {@inheritDoc}
     */
    public function getLvl(): int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLvl', []);

        return parent::getLvl();
    }

    /**
     * {@inheritDoc}
     */
    public function setLvl(int $lvl): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLvl', [$lvl]);

        parent::setLvl($lvl);
    }

    /**
     * {@inheritDoc}
     */
    public function getRgt(): int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRgt', []);

        return parent::getRgt();
    }

    /**
     * {@inheritDoc}
     */
    public function setRgt(int $rgt): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRgt', [$rgt]);

        parent::setRgt($rgt);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoot(): \Zikula\CategoriesModule\Entity\CategoryEntity
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRoot', []);

        return parent::getRoot();
    }

    /**
     * {@inheritDoc}
     */
    public function setRoot(\Zikula\CategoriesModule\Entity\CategoryEntity $root): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRoot', [$root]);

        parent::setRoot($root);
    }

    /**
     * {@inheritDoc}
     */
    public function toJson(string $prefix = '', string $locale = 'en'): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toJson', [$prefix, $locale]);

        return parent::toJson($prefix, $locale);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', []);

        return parent::__toString();
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
