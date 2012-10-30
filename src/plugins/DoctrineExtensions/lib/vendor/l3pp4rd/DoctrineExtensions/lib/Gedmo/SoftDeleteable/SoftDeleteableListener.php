<?php

namespace Gedmo\SoftDeleteable;

use Doctrine\Common\Persistence\ObjectManager,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\MappedEventSubscriber,
    Gedmo\Loggable\Mapping\Event\LoggableAdapter,
    Doctrine\Common\EventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * SoftDeleteable listener
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.SoftDeleteable
 * @subpackage SoftDeleteableListener
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class SoftDeleteableListener extends MappedEventSubscriber
{

    /**
     * Pre soft-delete event
     *
     * @var string
     */
    const PRE_SOFT_DELETE = "preSoftDelete";

    /**
     * Post soft-delete event
     *
     * @var string
     */
    const POST_SOFT_DELETE = "postSoftDelete";

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
            'onFlush'
        );
    }

    /**
     * If it's a SoftDeleteable object, update the "deletedAt" field
     * and skip the removal of the object
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        $evm = $om->getEventManager();
        
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $meta = $om->getClassMetadata(get_class($entity));
            $config = $this->getConfiguration($om, $meta->name);

            if (isset($config['softDeleteable']) && $config['softDeleteable']) {
                $evm->dispatchEvent(self::PRE_SOFT_DELETE, new LifecycleEventArgs(
                    $entity,
                    $om
                ));

                $reflProp = $meta->getReflectionProperty($config['fieldName']);
                $date = new \DateTime();
                $oldValue = $reflProp->getValue($entity);
                $reflProp->setValue($entity, $date);

                $om->persist($entity);
                $uow->propertyChanged($entity, $config['fieldName'], $oldValue, $date);
                $uow->scheduleExtraUpdate($entity, array(
                    $config['fieldName'] => array($oldValue, $date)
                ));

                $evm->dispatchEvent(self::POST_SOFT_DELETE, new LifecycleEventArgs(
                    $entity,
                    $om
                ));
            }
        }
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
