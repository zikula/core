<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineExtensions\StandardFields;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\Proxy;
use Gedmo\Mapping\MappedEventSubscriber;

/**
 * The StandardFields listener handles the update of
 * user ids on creation and update.
 */
class StandardFieldsListener extends MappedEventSubscriber
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'onFlush',
            'loadClassMetadata'
        ];
    }

    /**
     * Mapps additional metadata for the Entity
     *
     * @param EventArgs $eventArgs
     *
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * Looks for Timestampable objects being updated
     * to update modification date
     *
     * @param EventArgs $args
     *
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        // check all scheduled updates
        foreach ($ea->getScheduledObjectUpdates($uow) as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            if ($config = $this->getConfiguration($om, $meta->name)) {
                $changeSet = $ea->getObjectChangeSet($uow, $object);
                $needChanges = false;

                if (isset($config['update'])) {
                    foreach ($config['update'] as $field) {
                        if (!isset($changeSet[$field])) { // let manual values
                            $needChanges = true;
                            $meta->getReflectionProperty($field)->setValue($object, $ea->getUserIdValue($meta, $field));
                        }
                    }
                }

                if (isset($config['change'])) {
                    foreach ($config['change'] as $options) {
                        if (isset($changeSet[$options['field']])) {
                            continue; // value was set manually
                        }

                        $tracked = $options['trackedField'];
                        $trackedChild = null;
                        $parts = explode('.', $tracked);
                        if (isset($parts[1])) {
                            $tracked = $parts[0];
                            $trackedChild = $parts[1];
                        }

                        if (isset($changeSet[$tracked])) {
                            $changes = $changeSet[$tracked];
                            if (isset($trackedChild)) {
                                $changingObject = $changes[1];
                                if (!is_object($changingObject)) {
                                    throw new \Gedmo\Exception\UnexpectedValueException("Field - [{$field}] is expected to be object in class - {$meta->name}");
                                }
                                $objectMeta = $om->getClassMetadata(get_class($changingObject));
                                $trackedChild instanceof Proxy && $om->refresh($trackedChild);
                                $value = $objectMeta->getReflectionProperty($trackedChild)
                                    ->getValue($changingObject);
                            } else {
                                $value = $changes[1];
                            }

                            if ($options['value'] == $value) {
                                $needChanges = true;
                                $meta->getReflectionProperty($options['field'])
                                    ->setValue($object, $ea->getUserIdValue($meta, $options['field']));
                            }
                        }
                    }
                }

                if ($needChanges) {
                    $ea->recomputeSingleObjectChangeSet($uow, $meta, $object);
                }
            }
        }
    }

    /**
     * Checks for persisted Timestampable objects
     * to update creation and modification dates
     *
     * @param EventArgs $args
     *
     * @return void
     */
    public function prePersist(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();

        $meta = $om->getClassMetadata(get_class($object));
        if ($config = $this->getConfiguration($om, $meta->name)) {
            if (isset($config['update'])) {
                foreach ($config['update'] as $field) {
                    if ($meta->getReflectionProperty($field)->getValue($object) === null) { // let manual values
                        $meta->getReflectionProperty($field)->setValue($object, $ea->getUserIdValue($meta, $field));
                    }
                }
            }

            if (isset($config['create'])) {
                foreach ($config['create'] as $field) {
                    if ($meta->getReflectionProperty($field)->getValue($object) === null) { // let manual values
                        $meta->getReflectionProperty($field)->setValue($object, $ea->getUserIdValue($meta, $field));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
