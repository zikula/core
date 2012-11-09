<?php

namespace Gedmo\Tree\Strategy;

use Gedmo\Tree\Strategy;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\TreeListener;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Mapping\Event\AdapterInterface;
use Gedmo\Exception\RuntimeException;
use Gedmo\Exception\TreeLockingException;

/**
 * This strategy makes tree using materialized path strategy
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tree.Strategy
 * @subpackage AbstractMaterializedPath
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

abstract class AbstractMaterializedPath implements Strategy
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_REMOVE = 'remove';

    /**
     * TreeListener
     *
     * @var AbstractTreeListener
     */
    protected $listener = null;

    /**
     * Array of objects which were scheduled for path processes
     *
     * @var array
     */
    protected $scheduledForPathProcess = array();

    /**
     * Array of objects which were scheduled for path process.
     * This time, this array contains the objects with their ID
     * already set
     *
     * @var array
     */
    protected $scheduledForPathProcessWithIdSet = array();

    /**
     * Roots of trees which needs to be locked
     *
     * @var array
     */
    protected $rootsOfTreesWhichNeedsLocking = array();

    /**
     * Objects which are going to be inserted (set only if tree locking is used)
     *
     * @var array
     */
    protected $pendingObjectsToInsert = array();

    /**
     * Objects which are going to be updated (set only if tree locking is used)
     *
     * @var array
     */
    protected $pendingObjectsToUpdate = array();

    /**
     * Objects which are going to be removed (set only if tree locking is used)
     *
     * @var array
     */
    protected $pendingObjectsToRemove = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(TreeListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Strategy::MATERIALIZED_PATH;
    }

    /**
     * {@inheritdoc}
     */
    public function processScheduledInsertion($om, $node, AdapterInterface $ea)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);
        $fieldMapping = $meta->getFieldMapping($config['path_source']);

        if ($meta->isIdentifier($config['path_source']) || $fieldMapping['type'] === 'string') {
            $this->scheduledForPathProcess[spl_object_hash($node)] = $node;
        } else {
            $this->updateNode($om, $node, $ea);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processScheduledUpdate($om, $node, AdapterInterface $ea)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);
        $uow = $om->getUnitOfWork();
        $changeSet = $ea->getObjectChangeSet($uow, $node);

        if (isset($changeSet[$config['parent']]) || isset($changeSet[$config['path_source']])) {
            if (isset($changeSet[$config['path']])) {
                $originalPath = $changeSet[$config['path']][0];
            } else {
                $pathProp = $meta->getReflectionProperty($config['path']);
                $pathProp->setAccessible(true);
                $originalPath = $pathProp->getValue($node);
            }

            $this->updateNode($om, $node, $ea);
            $this->updateChildren($om, $node, $ea, $originalPath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processPostPersist($om, $node, AdapterInterface $ea)
    {
        $oid = spl_object_hash($node);

        if ($this->scheduledForPathProcess && array_key_exists($oid, $this->scheduledForPathProcess)) {
            $this->scheduledForPathProcessWithIdSet[$oid] = $node;

            unset($this->scheduledForPathProcess[$oid]);

            if (empty($this->scheduledForPathProcess)) {
                foreach ($this->scheduledForPathProcessWithIdSet as $oid => $node) {
                    $this->updateNode($om, $node, $ea);

                    unset($this->scheduledForPathProcessWithIdSet[$oid]);
                }
            }
        }

        $this->processPostEventsActions($om, $ea, $node, self::ACTION_INSERT);
    }

    /**
     * {@inheritdoc}
     */
    public function processPostUpdate($om, $node, AdapterInterface $ea)
    {
        $this->processPostEventsActions($om, $ea, $node, self::ACTION_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function processPostRemove($om, $node, AdapterInterface $ea)
    {
        $this->processPostEventsActions($om, $ea, $node, self::ACTION_REMOVE);
    }

    /**
     * {@inheritdoc}
     */
    public function onFlushEnd($om, AdapterInterface $ea)
    {
        $this->lockTrees($om, $ea);
    }

    /**
     * {@inheritdoc}
     */
    public function processPreRemove($om, $node)
    {
        $this->processPreLockingActions($om, $node, self::ACTION_REMOVE);
    }

    /**
     * {@inheritdoc}
     */
    public function processPrePersist($om, $node)
    {
        $this->processPreLockingActions($om, $node, self::ACTION_INSERT);
    }

    /**
     * {@inheritdoc}
     */
    public function processPreUpdate($om, $node)
    {
        $this->processPreLockingActions($om, $node, self::ACTION_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function processMetadataLoad($om, $meta)
    {}

    /**
     * {@inheritdoc}
     */
    public function processScheduledDelete($om, $node)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);

        $this->removeNode($om, $meta, $config, $node);
    }

    /**
     * Update the $node
     *
     * @param ObjectManager $om
     * @param object $node - target node
     * @param object $ea - event adapter
     * @return void
     */
    public function updateNode(ObjectManager $om, $node, AdapterInterface $ea)
    {
        $oid = spl_object_hash($node);
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);
        $uow = $om->getUnitOfWork();
        $parentProp = $meta->getReflectionProperty($config['parent']);
        $parentProp->setAccessible(true);
        $parent = $parentProp->getValue($node);
        $pathProp = $meta->getReflectionProperty($config['path']);
        $pathProp->setAccessible(true);
        $pathSourceProp = $meta->getReflectionProperty($config['path_source']);
        $pathSourceProp->setAccessible(true);
        $path = $pathSourceProp->getValue($node);

        // We need to avoid the presence of the path separator in the path source
        if (strpos($path, $config['path_separator']) !== false) {
            $msg = 'You can\'t use the Path separator ("%s") as a character for your PathSource field value.';

            throw new RuntimeException(sprintf($msg, $config['path_separator']));
        }

        $fieldMapping = $meta->getFieldMapping($config['path_source']);

        // If PathSource field is a string, we append the ID to the path
        if ($fieldMapping['type'] === 'string') {
            if (method_exists($meta, 'getIdentifierValue')) {
                $identifier = $meta->getIdentifierValue($node);
            } else {
                $identifierProp = $meta->getReflectionProperty($meta->getSingleIdentifierFieldName());
                $identifierProp->setAccessible(true);
                $identifier = $identifierProp->getValue($node);
            }

            $path .= '-'.$identifier;
        }

        $path .= $config['path_separator'];

        if ($parent) {
            // Ensure parent has been initialized in the case where it's a proxy
            $om->initializeObject($parent);
            
            $changeSet = $uow->isScheduledForUpdate($parent) ? $ea->getObjectChangeSet($uow, $parent) : false;
            $pathOrPathSourceHasChanged = $changeSet && (isset($changeSet[$config['path_source']]) || isset($changeSet[$config['path']]));

            if ($pathOrPathSourceHasChanged || !$pathProp->getValue($parent)) {
                $this->updateNode($om, $parent, $ea);
            }

            $path = $pathProp->getValue($parent).$path;
        }
        
        $pathProp->setValue($node, $path);
        $changes = array(
            $config['path'] => array(null, $path)
        );

        if (isset($config['level'])) {
            $level = substr_count($path, $config['path_separator']);
            $levelProp = $meta->getReflectionProperty($config['level']);
            $levelProp->setAccessible(true);
            $levelProp->setValue($node, $level);
            $changes[$config['level']] = array(null, $level);
        }

        $uow->scheduleExtraUpdate($node, $changes);
        $ea->setOriginalObjectProperty($uow, $oid, $config['path'], $path);
    }

    /**
     * Update node's children
     *
     * @param ObjectManager $om
     * @param object $node
     * @param AdapterInterface $ea
     * @param string $originalPath
     * @return void
     */
    public function updateChildren(ObjectManager $om, $node, AdapterInterface $ea, $originalPath)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);
        $children = $this->getChildren($om, $meta, $config, $originalPath);

        foreach ($children as $child) {
            $this->updateNode($om, $child, $ea);
        }
    }

    /**
     * Process pre-locking actions
     *
     * @param ObjectManager $om
     * @param object $node
     * @param string $action
     * @return void
     */
    public function processPreLockingActions($om, $node, $action)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);

        if ($config['activate_locking']) {;
            $parentProp = $meta->getReflectionProperty($config['parent']);
            $parentProp->setAccessible(true);
            $parentNode = $node;

            while (!is_null($parent = $parentProp->getValue($parentNode))) {
                $parentNode = $parent;
            }

            // In some cases, the parent could be a not initialized proxy. In this case, the
            // "lockTime" field may NOT be loaded yet and have null instead of the date.
            // We need to be sure that this field has its real value
            if ($parentNode !== $node && $parentNode instanceof \Doctrine\ODM\MongoDB\Proxy\Proxy) {
                $reflMethod = new \ReflectionMethod(get_class($parentNode), '__load');
                $reflMethod->setAccessible(true);

                $reflMethod->invoke($parentNode);
            }
            
            // If tree is already locked, we throw an exception
            $lockTimeProp = $meta->getReflectionProperty($config['lock_time']);
            $lockTimeProp->setAccessible(true);
            $lockTime = $lockTimeProp->getValue($parentNode);

            if (!is_null($lockTime)) {
                $lockTime = $lockTime instanceof \MongoDate ? $lockTime->sec : $lockTime->getTimestamp();
            }

            if (!is_null($lockTime) && ($lockTime >= (time() - $config['locking_timeout']))) {
                $msg = 'Tree with root id "%s" is locked.';
                $id = $meta->getIdentifierValue($parentNode);

                throw new TreeLockingException(sprintf($msg, $id));
            }

            $this->rootsOfTreesWhichNeedsLocking[spl_object_hash($parentNode)] = $parentNode;

            $oid = spl_object_hash($node);

            switch ($action) {
                case self::ACTION_INSERT:
                    $this->pendingObjectsToInsert[$oid] = $node;

                    break;
                case self::ACTION_UPDATE:
                    $this->pendingObjectsToUpdate[$oid] = $node;

                    break;
                case self::ACTION_REMOVE:
                    $this->pendingObjectsToRemove[$oid] = $node;

                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid action.', $action));
            }
        }
    }

    /**
     * Process pre-locking actions
     *
     * @param ObjectManager $om
     * @param AdapterInterface $ea
     * @param object $node
     * @param string $action
     * @return void
     */
    public function processPostEventsActions(ObjectManager $om, AdapterInterface $ea, $node, $action)
    {
        $meta = $om->getClassMetadata(get_class($node));
        $config = $this->listener->getConfiguration($om, $meta->name);

        if ($config['activate_locking']) {
            switch ($action) {
                case self::ACTION_INSERT:
                    unset($this->pendingObjectsToInsert[spl_object_hash($node)]);

                    break;
                case self::ACTION_UPDATE:
                    unset($this->pendingObjectsToUpdate[spl_object_hash($node)]);

                    break;
                case self::ACTION_REMOVE:
                    unset($this->pendingObjectsToRemove[spl_object_hash($node)]);

                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid action.', $action));
            }

            if (empty($this->pendingObjectsToInsert) && empty($this->pendingObjectsToUpdate) &&
                empty($this->pendingObjectsToRemove)) {
                $this->releaseTreeLocks($om, $ea);
            }
        }
    }
    
    /**
     * Locks all needed trees
     *
     * @param ObjectManager $om
     * @param AdapterInterface $ea
     * @return void
     */
    protected function lockTrees(ObjectManager $om, AdapterInterface $ea)
    {
        // Do nothing by default
    }

    /**
     * Releases all trees which are locked
     *
     * @param ObjectManager $om
     * @param AdapterInterface $ea
     * @return void
     */
    protected function releaseTreeLocks(ObjectManager $om, AdapterInterface $ea)
    {
        // Do nothing by default
    }

    /**
     * Remove node and its children
     *
     * @param ObjectManager $om
     * @param object $meta - Metadata
     * @param object $config - config
     * @param object $node - node to remove
     * @return void
     */
    abstract public function removeNode($om, $meta, $config, $node);

    /**
     * Returns children of the node with its original path
     *
     * @param ObjectManager $om
     * @param object $meta - Metadata
     * @param object $config - config
     * @param string $originalPath - original path of object
     * @return Doctrine\ODM\MongoDB\Cursor
     */
    abstract public function getChildren($om, $meta, $config, $originalPath);
}
