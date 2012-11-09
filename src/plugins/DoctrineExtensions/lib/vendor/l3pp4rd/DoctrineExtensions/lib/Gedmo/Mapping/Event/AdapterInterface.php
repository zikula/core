<?php

namespace Gedmo\Mapping\Event;

use Doctrine\Common\EventArgs;

/**
 * Doctrine event adapter interface is used
 * to retrieve common functionality for Doctrine
 * events
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Mapping.Event
 * @subpackage AdapterInterface
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface AdapterInterface
{
    /**
     * Set the eventargs
     *
     * @param \Doctrine\Common\EventArgs $args
     */
    function setEventArgs(EventArgs $args);

    /**
     * Call specific method on event args
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    function __call($method, $args);

    /**
     * Get the name of domain object
     *
     * @return string
     */
    function getDomainObjectName();

    /**
     * Get the name of used manager for this
     * event adapter
     *
     * @return string
     */
    function getManagerName();

    /**
     * Get used object manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    function getObjectManager();

    /**
     * Get the object changeset from a UnitOfWork
     *
     * @param UnitOfWork $uow
     * @param Object $object
     * @return array
     */
    function getObjectChangeSet($uow, $object);

    /**
     * Get the single identifier field name
     *
     * @param ClassMetadata $meta
     * @return string
     */
    function getSingleIdentifierFieldName($meta);

    /**
     * Recompute the single object changeset from a UnitOfWork
     *
     * @param UnitOfWork $uow
     * @param ClassMetadata $meta
     * @param Object $object
     * @return void
     */
    function recomputeSingleObjectChangeSet($uow, $meta, $object);

    /**
     * Get the scheduled object updates from a UnitOfWork
     *
     * @param UnitOfWork $uow
     * @return array
     */
    function getScheduledObjectUpdates($uow);

    /**
     * Get the scheduled object insertions from a UnitOfWork
     *
     * @param UnitOfWork $uow
     * @return array
     */
    function getScheduledObjectInsertions($uow);

    /**
     * Get the scheduled object deletions from a UnitOfWork
     *
     * @param UnitOfWork $uow
     * @return array
     */
    function getScheduledObjectDeletions($uow);

    /**
     * Sets a property value of the original data array of an object
     *
     * @param UnitOfWork $uow
     * @param string $oid
     * @param string $property
     * @param mixed $value
     * @return void
     */
    function setOriginalObjectProperty($uow, $oid, $property, $value);

    /**
     * Clears the property changeset of the object with the given OID.
     *
     * @param UnitOfWork $uow
     * @param string $oid The object's OID.
     */
    function clearObjectChangeSet($uow, $oid);
}