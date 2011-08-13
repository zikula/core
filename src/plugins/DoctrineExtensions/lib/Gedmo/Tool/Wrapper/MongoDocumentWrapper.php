<?php

namespace Gedmo\Tool\Wrapper;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Proxy\Proxy;

/**
 * Wraps document or proxy for more convenient
 * manipulation
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tool.Wrapper
 * @subpackage MongoDocumentWrapper
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class MongoDocumentWrapper extends AbstractWrapper
{
    /**
     * Document identifier
     *
     * @var mixed
     */
    private $identifier;

    /**
     * True if document or proxy is loaded
     *
     * @var boolean
     */
    private $initialized = false;

    /**
     * Wrapp document
     *
     * @param object $document
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     */
    public function __construct($document, DocumentManager $dm)
    {
        $this->om = $dm;
        $this->object = $document;
        $this->meta = $dm->getClassMetadata(get_class($this->object));
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyValue($property)
    {
        $this->initialize();
        return $this->meta->getReflectionProperty($property)->getValue($this->object);
    }

    /**
     * {@inheritDoc}
     */
    public function getRootObjectName()
    {
        return $this->meta->rootDocumentName;
    }

    /**
     * {@inheritDoc}
     */
    public function setPropertyValue($property, $value)
    {
        $this->initialize();
        $this->meta->getReflectionProperty($property)->setValue($this->object, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasValidIdentifier()
    {
        return (bool)$this->getIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier($single = true)
    {
        if (!$this->identifier) {
            if ($this->object instanceof Proxy) {
                $uow = $this->om->getUnitOfWork();
                if ($uow->isInIdentityMap($this->object)) {
                    $this->identifier = (string)$uow->getDocumentIdentifier($this->object);
                } else {
                    $this->initialize();
                }
            }
            if (!$this->identifier) {
                $this->identifier = (string)$this->getPropertyValue($this->meta->identifier);
            }
        }
        return $this->identifier;
    }

    /**
     * Initialize the document if it is proxy
     * required when is detached or not initialized
     */
    protected function initialize()
    {
        if (!$this->initialized) {
            if ($this->object instanceof Proxy) {
                $uow = $this->om->getUnitOfWork();
                if (!$this->object->__isInitialized__) {
                    $persister = $uow->getDocumentPersister($this->meta->name);
                    $identifier = null;
                    if ($uow->isInIdentityMap($this->object)) {
                        $identifier = $this->getIdentifier();
                    } else {
                        // this may not happen but in case
                        $reflProperty = new \ReflectionProperty($this->object, 'identifier');
                        $reflProperty->setAccessible(true);
                        $identifier = $reflProperty->getValue($this->object);
                    }
                    $this->object->__isInitialized__ = true;
                    $persister->load($identifier, $this->object);
                }
            }
        }
    }
}