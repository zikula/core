<?php

namespace DoctrineProxy\__CG__\Zikula\ZAuthModule\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class AuthenticationMappingEntity extends \Zikula\ZAuthModule\Entity\AuthenticationMappingEntity implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'method', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'uid', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'uname', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'email', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'verifiedEmail', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'pass', 'reflection'];
        }

        return ['__isInitialized__', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'id', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'method', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'uid', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'uname', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'email', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'verifiedEmail', '' . "\0" . 'Zikula\\ZAuthModule\\Entity\\AuthenticationMappingEntity' . "\0" . 'pass', 'reflection'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (AuthenticationMappingEntity $proxy) {
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
    public function getMethod(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMethod', []);

        return parent::getMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function setMethod(string $method): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMethod', [$method]);

        parent::setMethod($method);
    }

    /**
     * {@inheritDoc}
     */
    public function getUid(): ?int
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUid', []);

        return parent::getUid();
    }

    /**
     * {@inheritDoc}
     */
    public function setUid(int $uid): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUid', [$uid]);

        parent::setUid($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function getUname(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUname', []);

        return parent::getUname();
    }

    /**
     * {@inheritDoc}
     */
    public function setUname(string $uname): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUname', [$uname]);

        parent::setUname($uname);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmail', []);

        return parent::getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail(string $email): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEmail', [$email]);

        parent::setEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function isVerifiedEmail(): bool
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isVerifiedEmail', []);

        return parent::isVerifiedEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function setVerifiedEmail(bool $verifiedEmail): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVerifiedEmail', [$verifiedEmail]);

        parent::setVerifiedEmail($verifiedEmail);
    }

    /**
     * {@inheritDoc}
     */
    public function getPass(): ?string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPass', []);

        return parent::getPass();
    }

    /**
     * {@inheritDoc}
     */
    public function setPass(string $pass): void
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPass', [$pass]);

        parent::setPass($pass);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserEntityData(): array
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUserEntityData', []);

        return parent::getUserEntityData();
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
