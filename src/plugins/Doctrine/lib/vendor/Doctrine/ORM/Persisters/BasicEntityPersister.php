<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Persisters;

use PDO,
    Doctrine\DBAL\LockMode,
    Doctrine\DBAL\Types\Type,
    Doctrine\DBAL\Connection,
    Doctrine\ORM\ORMException,
    Doctrine\ORM\OptimisticLockException,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Query,
    Doctrine\ORM\PersistentCollection,
    Doctrine\ORM\Mapping\MappingException,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Events,
    Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * A BasicEntityPersiter maps an entity to a single table in a relational database.
 *
 * A persister is always responsible for a single entity type.
 *
 * EntityPersisters are used during a UnitOfWork to apply any changes to the persistent
 * state of entities onto a relational database when the UnitOfWork is committed,
 * as well as for basic querying of entities and their associations (not DQL).
 *
 * The persisting operations that are invoked during a commit of a UnitOfWork to
 * persist the persistent entity state are:
 *
 *   - {@link addInsert} : To schedule an entity for insertion.
 *   - {@link executeInserts} : To execute all scheduled insertions.
 *   - {@link update} : To update the persistent state of an entity.
 *   - {@link delete} : To delete the persistent state of an entity.
 *
 * As can be seen from the above list, insertions are batched and executed all at once
 * for increased efficiency.
 *
 * The querying operations invoked during a UnitOfWork, either through direct find
 * requests or lazy-loading, are the following:
 *
 *   - {@link load} : Loads (the state of) a single, managed entity.
 *   - {@link loadAll} : Loads multiple, managed entities.
 *   - {@link loadOneToOneEntity} : Loads a one/many-to-one entity association (lazy-loading).
 *   - {@link loadOneToManyCollection} : Loads a one-to-many entity association (lazy-loading).
 *   - {@link loadManyToManyCollection} : Loads a many-to-many entity association (lazy-loading).
 *
 * The BasicEntityPersister implementation provides the default behavior for
 * persisting and querying entities that are mapped to a single database table.
 *
 * Subclasses can be created to provide custom persisting and querying strategies,
 * i.e. spanning multiple tables.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @author Giorgio Sironi <piccoloprincipeazzurro@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @since 2.0
 */
class BasicEntityPersister
{
    /**
     * Metadata object that describes the mapping of the mapped entity class.
     *
     * @var Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $_class;

    /**
     * The underlying DBAL Connection of the used EntityManager.
     *
     * @var Doctrine\DBAL\Connection $conn
     */
    protected $_conn;

    /**
     * The database platform.
     * 
     * @var Doctrine\DBAL\Platforms\AbstractPlatform
     */
    protected $_platform;

    /**
     * The EntityManager instance.
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * Queued inserts.
     *
     * @var array
     */
    protected $_queuedInserts = array();
    
    /**
     * ResultSetMapping that is used for all queries. Is generated lazily once per request.
     * 
     * TODO: Evaluate Caching in combination with the other cached SQL snippets.
     * 
     * @var Query\ResultSetMapping
     */
    protected $_rsm;

    /**
     * The map of column names to DBAL mapping types of all prepared columns used
     * when INSERTing or UPDATEing an entity.
     * 
     * @var array
     * @see _prepareInsertData($entity)
     * @see _prepareUpdateData($entity)
     */
    protected $_columnTypes = array();

    /**
     * The INSERT SQL statement used for entities handled by this persister.
     * This SQL is only generated once per request, if at all.
     * 
     * @var string
     */
    private $_insertSql;

    /**
     * The SELECT column list SQL fragment used for querying entities by this persister.
     * This SQL fragment is only generated once per request, if at all.
     * 
     * @var string
     */
    protected $_selectColumnListSql;
    
    /**
     * The JOIN SQL fragement used to eagerly load all many-to-one and one-to-one
     * associations configured as FETCH_EAGER, aswell as all inverse one-to-one associations.
     * 
     * @var string
     */
    protected $_selectJoinSql;

    /**
     * Counter for creating unique SQL table and column aliases.
     * 
     * @var integer
     */
    protected $_sqlAliasCounter = 0;

    /**
     * Map from class names (FQCN) to the corresponding generated SQL table aliases.
     * 
     * @var array
     */
    protected $_sqlTableAliases = array();

    /**
     * Initializes a new <tt>BasicEntityPersister</tt> that uses the given EntityManager
     * and persists instances of the class described by the given ClassMetadata descriptor.
     * 
     * @param Doctrine\ORM\EntityManager $em
     * @param Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $em, ClassMetadata $class)
    {
        $this->_em = $em;
        $this->_class = $class;
        $this->_conn = $em->getConnection();
        $this->_platform = $this->_conn->getDatabasePlatform();
    }

    /**
     * @return Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->_class;
    }

    /**
     * Adds an entity to the queued insertions.
     * The entity remains queued until {@link executeInserts} is invoked.
     *
     * @param object $entity The entity to queue for insertion.
     */
    public function addInsert($entity)
    {
        $this->_queuedInserts[spl_object_hash($entity)] = $entity;
    }

    /**
     * Executes all queued entity insertions and returns any generated post-insert
     * identifiers that were created as a result of the insertions.
     * 
     * If no inserts are queued, invoking this method is a NOOP.
     *
     * @return array An array of any generated post-insert IDs. This will be an empty array
     *               if the entity class does not use the IDENTITY generation strategy.
     */
    public function executeInserts()
    {
        if ( ! $this->_queuedInserts) {
            return;
        }

        $postInsertIds = array();
        $idGen = $this->_class->idGenerator;
        $isPostInsertId = $idGen->isPostInsertGenerator();

        $stmt = $this->_conn->prepare($this->_getInsertSQL());
        $tableName = $this->_class->table['name'];

        foreach ($this->_queuedInserts as $entity) {
            $insertData = $this->_prepareInsertData($entity);

            if (isset($insertData[$tableName])) {
                $paramIndex = 1;
                foreach ($insertData[$tableName] as $column => $value) {
                    $stmt->bindValue($paramIndex++, $value, $this->_columnTypes[$column]);
                }
            }

            $stmt->execute();

            if ($isPostInsertId) {
                $id = $idGen->generate($this->_em, $entity);
                $postInsertIds[$id] = $entity;
            } else {
                $id = $this->_class->getIdentifierValues($entity);
            }

            if ($this->_class->isVersioned) {
                $this->assignDefaultVersionValue($entity, $id);
            }
        }

        $stmt->closeCursor();
        $this->_queuedInserts = array();

        return $postInsertIds;
    }

    /**
     * Retrieves the default version value which was created
     * by the preceding INSERT statement and assigns it back in to the 
     * entities version field.
     *
     * @param object $entity
     * @param mixed $id
     */
    protected function assignDefaultVersionValue($entity, $id)
    {
        $value = $this->fetchVersionValue($this->_class, $id);
        $this->_class->setFieldValue($entity, $this->_class->versionField, $value);
    }

    /**
     * Fetch the current version value of a versioned entity.
     * 
     * @param Doctrine\ORM\Mapping\ClassMetadata $versionedClass
     * @param mixed $id
     * @return mixed
     */
    protected function fetchVersionValue($versionedClass, $id)
    {
        $versionField = $versionedClass->versionField;
        $identifier = $versionedClass->getIdentifierColumnNames();
        $versionFieldColumnName = $versionedClass->getColumnName($versionField);
        //FIXME: Order with composite keys might not be correct
        $sql = "SELECT " . $versionFieldColumnName . " FROM " . $versionedClass->getQuotedTableName($this->_platform)
               . " WHERE " . implode(' = ? AND ', $identifier) . " = ?";
        $value = $this->_conn->fetchColumn($sql, array_values((array)$id));

        return Type::getType($versionedClass->fieldMappings[$versionField]['type'])->convertToPHPValue($value, $this->_platform);
    }

    /**
     * Updates a managed entity. The entity is updated according to its current changeset
     * in the running UnitOfWork. If there is no changeset, nothing is updated.
     *
     * The data to update is retrieved through {@link _prepareUpdateData}.
     * Subclasses that override this method are supposed to obtain the update data
     * in the same way, through {@link _prepareUpdateData}.
     * 
     * Subclasses are also supposed to take care of versioning when overriding this method,
     * if necessary. The {@link _updateTable} method can be used to apply the data retrieved
     * from {@_prepareUpdateData} on the target tables, thereby optionally applying versioning.
     *
     * @param object $entity The entity to update.
     */
    public function update($entity)
    {
        $updateData = $this->_prepareUpdateData($entity);
        $tableName = $this->_class->table['name'];
        if (isset($updateData[$tableName]) && $updateData[$tableName]) {
            $this->_updateTable(
                $entity, $this->_class->getQuotedTableName($this->_platform),
                $updateData[$tableName], $this->_class->isVersioned
            );

            if ($this->_class->isVersioned) {
                $id = $this->_em->getUnitOfWork()->getEntityIdentifier($entity);
                $this->assignDefaultVersionValue($entity, $id);
            }
        }
    }

    /**
     * Performs an UPDATE statement for an entity on a specific table.
     * The UPDATE can optionally be versioned, which requires the entity to have a version field.
     *
     * @param object $entity The entity object being updated.
     * @param string $quotedTableName The quoted name of the table to apply the UPDATE on.
     * @param array $updateData The map of columns to update (column => value).
     * @param boolean $versioned Whether the UPDATE should be versioned.
     */
    protected final function _updateTable($entity, $quotedTableName, array $updateData, $versioned = false)
    {
        $set = $params = $types = array();

        foreach ($updateData as $columnName => $value) {
            if (isset($this->_class->fieldNames[$columnName])) {
                $set[] = $this->_class->getQuotedColumnName($this->_class->fieldNames[$columnName], $this->_platform) . ' = ?';
            } else {
                $set[] = $columnName . ' = ?';
            }
            $params[] = $value;
            $types[] = $this->_columnTypes[$columnName];
        }

        $where = array();
        $id = $this->_em->getUnitOfWork()->getEntityIdentifier($entity);
        foreach ($this->_class->identifier as $idField) {
            if (isset($this->_class->associationMappings[$idField])) {
                $targetMapping = $this->_em->getClassMetadata($this->_class->associationMappings[$idField]['targetEntity']);
                $where[] = $this->_class->associationMappings[$idField]['joinColumns'][0]['name'];
                $params[] = $id[$idField];
                $types[] = $targetMapping->fieldMappings[$targetMapping->identifier[0]]['type'];
            } else {
                $where[] = $this->_class->getQuotedColumnName($idField, $this->_platform);
                $params[] = $id[$idField];
                $types[] = $this->_class->fieldMappings[$idField]['type'];
            }
        }

        if ($versioned) {
            $versionField = $this->_class->versionField;
            $versionFieldType = $this->_class->fieldMappings[$versionField]['type'];
            $versionColumn = $this->_class->getQuotedColumnName($versionField, $this->_platform);
            if ($versionFieldType == Type::INTEGER) {
                $set[] = $versionColumn . ' = ' . $versionColumn . ' + 1';
            } else if ($versionFieldType == Type::DATETIME) {
                $set[] = $versionColumn . ' = CURRENT_TIMESTAMP';
            }
            $where[] = $versionColumn;
            $params[] = $this->_class->reflFields[$versionField]->getValue($entity);
            $types[] = $this->_class->fieldMappings[$versionField]['type'];
        }

        $sql = "UPDATE $quotedTableName SET " . implode(', ', $set)
            . ' WHERE ' . implode(' = ? AND ', $where) . ' = ?';

        $result = $this->_conn->executeUpdate($sql, $params, $types);

        if ($versioned && ! $result) {
            throw OptimisticLockException::lockFailed($entity);
        }
    }

    /**
     * @todo Add check for platform if it supports foreign keys/cascading.
     * @param array $identifier
     * @return void
     */
    protected function deleteJoinTableRecords($identifier)
    {
        foreach ($this->_class->associationMappings as $mapping) {
            if ($mapping['type'] == ClassMetadata::MANY_TO_MANY) {
                // @Todo this only covers scenarios with no inheritance or of the same level. Is there something
                // like self-referential relationship between different levels of an inheritance hierachy? I hope not!
                $selfReferential = ($mapping['targetEntity'] == $mapping['sourceEntity']);
                
                if ( ! $mapping['isOwningSide']) {
                    $relatedClass = $this->_em->getClassMetadata($mapping['targetEntity']);
                    $mapping = $relatedClass->associationMappings[$mapping['mappedBy']];
                    $keys = array_keys($mapping['relationToTargetKeyColumns']);
                    if ($selfReferential) {
                        $otherKeys = array_keys($mapping['relationToSourceKeyColumns']);
                    }
                } else {
                    $keys = array_keys($mapping['relationToSourceKeyColumns']);
                    if ($selfReferential) {
                        $otherKeys = array_keys($mapping['relationToTargetKeyColumns']);
                    }
                }

                if ( ! isset($mapping['isOnDeleteCascade'])) {
                    $this->_conn->delete($mapping['joinTable']['name'], array_combine($keys, $identifier));

                    if ($selfReferential) {
                        $this->_conn->delete($mapping['joinTable']['name'], array_combine($otherKeys, $identifier));
                    }
                }
            }
        }
    }

    /**
     * Deletes a managed entity.
     *
     * The entity to delete must be managed and have a persistent identifier.
     * The deletion happens instantaneously.
     *
     * Subclasses may override this method to customize the semantics of entity deletion.
     *
     * @param object $entity The entity to delete.
     */
    public function delete($entity)
    {
        $identifier = $this->_em->getUnitOfWork()->getEntityIdentifier($entity);
        $this->deleteJoinTableRecords($identifier);

        $id = array_combine($this->_class->getIdentifierColumnNames(), $identifier);
        $this->_conn->delete($this->_class->getQuotedTableName($this->_platform), $id);
    }

    /**
     * Prepares the changeset of an entity for database insertion (UPDATE).
     *
     * The changeset is obtained from the currently running UnitOfWork.
     * 
     * During this preparation the array that is passed as the second parameter is filled with
     * <columnName> => <value> pairs, grouped by table name.
     *
     * Example:
     * <code>
     * array(
     *    'foo_table' => array('column1' => 'value1', 'column2' => 'value2', ...),
     *    'bar_table' => array('columnX' => 'valueX', 'columnY' => 'valueY', ...),
     *    ...
     * )
     * </code>
     *
     * @param object $entity The entity for which to prepare the data.
     * @return array The prepared data.
     */
    protected function _prepareUpdateData($entity)
    {
        $result = array();
        $uow = $this->_em->getUnitOfWork();

        if ($versioned = $this->_class->isVersioned) {
            $versionField = $this->_class->versionField;
        }

        foreach ($uow->getEntityChangeSet($entity) as $field => $change) {
            if ($versioned && $versionField == $field) {
                continue;
            }

            $oldVal = $change[0];
            $newVal = $change[1];

            if (isset($this->_class->associationMappings[$field])) {
                $assoc = $this->_class->associationMappings[$field];
                // Only owning side of x-1 associations can have a FK column.
                if ( ! $assoc['isOwningSide'] || ! ($assoc['type'] & ClassMetadata::TO_ONE)) {
                    continue;
                }

                if ($newVal !== null) {
                    $oid = spl_object_hash($newVal);
                    if (isset($this->_queuedInserts[$oid]) || $uow->isScheduledForInsert($newVal)) {
                        // The associated entity $newVal is not yet persisted, so we must
                        // set $newVal = null, in order to insert a null value and schedule an
                        // extra update on the UnitOfWork.
                        $uow->scheduleExtraUpdate($entity, array(
                            $field => array(null, $newVal)
                        ));
                        $newVal = null;
                    }
                }

                if ($newVal !== null) {
                    $newValId = $uow->getEntityIdentifier($newVal);
                }

                $targetClass = $this->_em->getClassMetadata($assoc['targetEntity']);
                $owningTable = $this->getOwningTable($field);

                foreach ($assoc['sourceToTargetKeyColumns'] as $sourceColumn => $targetColumn) {
                    if ($newVal === null) {
                        $result[$owningTable][$sourceColumn] = null;
                    } else if ($targetClass->containsForeignIdentifier) {
                        $result[$owningTable][$sourceColumn] = $newValId[$targetClass->getFieldForColumn($targetColumn)];
                    } else {
                        $result[$owningTable][$sourceColumn] = $newValId[$targetClass->fieldNames[$targetColumn]];
                    }
                    $this->_columnTypes[$sourceColumn] = $targetClass->getTypeOfColumn($targetColumn);
                }
            } else {
                $columnName = $this->_class->columnNames[$field];
                $this->_columnTypes[$columnName] = $this->_class->fieldMappings[$field]['type'];
                $result[$this->getOwningTable($field)][$columnName] = $newVal;
            }
        }
        return $result;
    }

    /**
     * Prepares the data changeset of a managed entity for database insertion (initial INSERT).
     * The changeset of the entity is obtained from the currently running UnitOfWork.
     *
     * The default insert data preparation is the same as for updates.
     *
     * @param object $entity The entity for which to prepare the data.
     * @return array The prepared data for the tables to update.
     * @see _prepareUpdateData
     */
    protected function _prepareInsertData($entity)
    {
        return $this->_prepareUpdateData($entity);
    }

    /**
     * Gets the name of the table that owns the column the given field is mapped to.
     *
     * The default implementation in BasicEntityPersister always returns the name
     * of the table the entity type of this persister is mapped to, since an entity
     * is always persisted to a single table with a BasicEntityPersister.
     *
     * @param string $fieldName The field name.
     * @return string The table name.
     */
    public function getOwningTable($fieldName)
    {
        return $this->_class->table['name'];
    }

    /**
     * Loads an entity by a list of field criteria.
     *
     * @param array $criteria The criteria by which to load the entity.
     * @param object $entity The entity to load the data into. If not specified,
     *        a new entity is created.
     * @param $assoc The association that connects the entity to load to another entity, if any.
     * @param array $hints Hints for entity creation.
     * @param int $lockMode
     * @return object The loaded and managed entity instance or NULL if the entity can not be found.
     * @todo Check identity map? loadById method? Try to guess whether $criteria is the id?
     */
    public function load(array $criteria, $entity = null, $assoc = null, array $hints = array(), $lockMode = 0)
    {
        $sql = $this->_getSelectEntitiesSQL($criteria, $assoc, $lockMode);
        list($params, $types) = $this->expandParameters($criteria);
        $stmt = $this->_conn->executeQuery($sql, $params, $types);
        
        if ($entity !== null) {
            $hints[Query::HINT_REFRESH] = true;
        }

        if ($this->_selectJoinSql) {
            $hydrator = $this->_em->newHydrator(Query::HYDRATE_OBJECT);
        } else {
            $hydrator = $this->_em->newHydrator(Query::HYDRATE_SIMPLEOBJECT);
        }
        $entities = $hydrator->hydrateAll($stmt, $this->_rsm, $hints);
        return $entities ? $entities[0] : null;
    }

    /**
     * Loads an entity of this persister's mapped class as part of a single-valued
     * association from another entity.
     *
     * @param array $assoc The association to load.
     * @param object $sourceEntity The entity that owns the association (not necessarily the "owning side").
     * @param object $targetEntity The existing ghost entity (proxy) to load, if any.
     * @param array $identifier The identifier of the entity to load. Must be provided if
     *                          the association to load represents the owning side, otherwise
     *                          the identifier is derived from the $sourceEntity.
     * @return object The loaded and managed entity instance or NULL if the entity can not be found.
     */
    public function loadOneToOneEntity(array $assoc, $sourceEntity, $targetEntity, array $identifier = array())
    {
        if ($foundEntity = $this->_em->getUnitOfWork()->tryGetById($identifier, $assoc['targetEntity'])) {
            return $foundEntity;
        }

        $targetClass = $this->_em->getClassMetadata($assoc['targetEntity']);

        if ($assoc['isOwningSide']) {
            $isInverseSingleValued = $assoc['inversedBy'] && ! $targetClass->isCollectionValuedAssociation($assoc['inversedBy']);

            // Mark inverse side as fetched in the hints, otherwise the UoW would
            // try to load it in a separate query (remember: to-one inverse sides can not be lazy).
            $hints = array();
            if ($isInverseSingleValued) {
                $hints['fetched'][$targetClass->name][$assoc['inversedBy']] = true;
                if ($targetClass->subClasses) {
                    foreach ($targetClass->subClasses as $targetSubclassName) {
                        $hints['fetched'][$targetSubclassName][$assoc['inversedBy']] = true;
                    }
                }
            }
            /* cascade read-only status
            if ($this->_em->getUnitOfWork()->isReadOnly($sourceEntity)) {
                $hints[Query::HINT_READ_ONLY] = true;
            }
            */

            $targetEntity = $this->load($identifier, $targetEntity, $assoc, $hints);

            // Complete bidirectional association, if necessary
            if ($targetEntity !== null && $isInverseSingleValued) {
                $targetClass->reflFields[$assoc['inversedBy']]->setValue($targetEntity, $sourceEntity);
            }
        } else {
            $sourceClass = $this->_em->getClassMetadata($assoc['sourceEntity']);
            $owningAssoc = $targetClass->getAssociationMapping($assoc['mappedBy']);
            // TRICKY: since the association is specular source and target are flipped
            foreach ($owningAssoc['targetToSourceKeyColumns'] as $sourceKeyColumn => $targetKeyColumn) {
                if (isset($sourceClass->fieldNames[$sourceKeyColumn])) {
                    $identifier[$targetKeyColumn] = $sourceClass->reflFields[$sourceClass->fieldNames[$sourceKeyColumn]]->getValue($sourceEntity);
                } else {
                    throw MappingException::joinColumnMustPointToMappedField(
                        $sourceClass->name, $sourceKeyColumn
                    );
                }
            }

            $targetEntity = $this->load($identifier, $targetEntity, $assoc);

            if ($targetEntity !== null) {
                $targetClass->setFieldValue($targetEntity, $assoc['mappedBy'], $sourceEntity);
            }
        }

        return $targetEntity;
    }

    /**
     * Refreshes a managed entity.
     * 
     * @param array $id The identifier of the entity as an associative array from
     *                  column or field names to values.
     * @param object $entity The entity to refresh.
     */
    public function refresh(array $id, $entity)
    {
        $sql = $this->_getSelectEntitiesSQL($id);
        list($params, $types) = $this->expandParameters($id);
        $stmt = $this->_conn->executeQuery($sql, $params, $types);
        
        $hydrator = $this->_em->newHydrator(Query::HYDRATE_OBJECT);
        $hydrator->hydrateAll($stmt, $this->_rsm, array(Query::HINT_REFRESH => true));

        if (isset($this->_class->lifecycleCallbacks[Events::postLoad])) {
            $this->_class->invokeLifecycleCallbacks(Events::postLoad, $entity);
        }
        $evm = $this->_em->getEventManager();
        if ($evm->hasListeners(Events::postLoad)) {
            $evm->dispatchEvent(Events::postLoad, new LifecycleEventArgs($entity, $this->_em));
        }
    }

    /**
     * Loads a list of entities by a list of field criteria.
     * 
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function loadAll(array $criteria = array(), array $orderBy = null, $limit = null, $offset = null)
    {
        $entities = array();
        $sql = $this->_getSelectEntitiesSQL($criteria, null, 0, $limit, $offset, $orderBy);
        list($params, $types) = $this->expandParameters($criteria);
        $stmt = $this->_conn->executeQuery($sql, $params, $types);

        if ($this->_selectJoinSql) {
            $hydrator = $this->_em->newHydrator(Query::HYDRATE_OBJECT);
        } else {
            $hydrator = $this->_em->newHydrator(Query::HYDRATE_SIMPLEOBJECT);
        }
        return $hydrator->hydrateAll($stmt, $this->_rsm, array('deferEagerLoads' => true));
    }

    /**
     * Get (sliced or full) elements of the given collection.
     * 
     * @param array $assoc
     * @param object $sourceEntity
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function getManyToManyCollection(array $assoc, $sourceEntity, $offset = null, $limit = null)
    {
        $stmt = $this->getManyToManyStatement($assoc, $sourceEntity, $offset, $limit);
        return $this->loadArrayFromStatement($assoc, $stmt);
    }

    /**
     * Load an array of entities from a given dbal statement.
     * 
     * @param array $assoc
     * @param Doctrine\DBAL\Statement $stmt
     * @return array
     */
    private function loadArrayFromStatement($assoc, $stmt)
    {
        $hints = array('deferEagerLoads' => true);

        if (isset($assoc['indexBy'])) {
            $rsm = clone ($this->_rsm); // this is necessary because the "default rsm" should be changed.
            $rsm->addIndexBy('r', $assoc['indexBy']);
        } else {
            $rsm = $this->_rsm;
        }

        $hydrator = $this->_em->newHydrator(Query::HYDRATE_OBJECT);
        return $hydrator->hydrateAll($stmt, $rsm, $hints);
    }

    /**
     * Hydrate a collection from a given dbal statement.
     * 
     * @param array $assoc
     * @param Doctrine\DBAL\Statement $stmt
     * @param PersistentCollection $coll
     */
    private function loadCollectionFromStatement($assoc, $stmt, $coll)
    {        
        $hints = array('deferEagerLoads' => true, 'collection' => $coll);

        if (isset($assoc['indexBy'])) {
            $rsm = clone ($this->_rsm); // this is necessary because the "default rsm" should be changed.
            $rsm->addIndexBy('r', $assoc['indexBy']);
        } else {
            $rsm = $this->_rsm;
        }

        $hydrator = $this->_em->newHydrator(Query::HYDRATE_OBJECT);
        $hydrator->hydrateAll($stmt, $rsm, $hints);
    }

    /**
     * Loads a collection of entities of a many-to-many association.
     *
     * @param ManyToManyMapping $assoc The association mapping of the association being loaded.
     * @param object $sourceEntity The entity that owns the collection.
     * @param PersistentCollection $coll The collection to fill.
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
    public function loadManyToManyCollection(array $assoc, $sourceEntity, PersistentCollection $coll)
    {
        $stmt = $this->getManyToManyStatement($assoc, $sourceEntity);
        return $this->loadCollectionFromStatement($assoc, $stmt, $coll);
    }

    private function getManyToManyStatement(array $assoc, $sourceEntity, $offset = null, $limit = null)
    {
        $criteria = array();
        $sourceClass = $this->_em->getClassMetadata($assoc['sourceEntity']);
        if ($assoc['isOwningSide']) {
            $quotedJoinTable = $sourceClass->getQuotedJoinTableName($assoc, $this->_platform);
            foreach ($assoc['relationToSourceKeyColumns'] as $relationKeyColumn => $sourceKeyColumn) {
                if ($sourceClass->containsForeignIdentifier) {
                    $field = $sourceClass->getFieldForColumn($sourceKeyColumn);
                    $value = $sourceClass->reflFields[$field]->getValue($sourceEntity);
                    if (isset($sourceClass->associationMappings[$field])) {
                        $value = $this->_em->getUnitOfWork()->getEntityIdentifier($value);
                        $value = $value[$this->_em->getClassMetadata($assoc['targetEntity'])->identifier[0]];
                    }

                    $criteria[$quotedJoinTable . "." . $relationKeyColumn] = $value;
                } else if (isset($sourceClass->fieldNames[$sourceKeyColumn])) {
                    $criteria[$quotedJoinTable . "." . $relationKeyColumn] = $sourceClass->reflFields[$sourceClass->fieldNames[$sourceKeyColumn]]->getValue($sourceEntity);
                } else {
                    throw MappingException::joinColumnMustPointToMappedField(
                        $sourceClass->name, $sourceKeyColumn
                    );
                }
            }
        } else {
            $owningAssoc = $this->_em->getClassMetadata($assoc['targetEntity'])->associationMappings[$assoc['mappedBy']];
            $quotedJoinTable = $sourceClass->getQuotedJoinTableName($owningAssoc, $this->_platform);
            // TRICKY: since the association is inverted source and target are flipped
            foreach ($owningAssoc['relationToTargetKeyColumns'] as $relationKeyColumn => $sourceKeyColumn) {
                if ($sourceClass->containsForeignIdentifier) {
                    $field = $sourceClass->getFieldForColumn($sourceKeyColumn);
                    $value = $sourceClass->reflFields[$field]->getValue($sourceEntity);
                    if (isset($sourceClass->associationMappings[$field])) {
                        $value = $this->_em->getUnitOfWork()->getEntityIdentifier($value);
                        $value = $value[$this->_em->getClassMetadata($assoc['targetEntity'])->identifier[0]];
                    }
                    $criteria[$quotedJoinTable . "." . $relationKeyColumn] = $value;
                } else if (isset($sourceClass->fieldNames[$sourceKeyColumn])) {
                    $criteria[$quotedJoinTable . "." . $relationKeyColumn] = $sourceClass->reflFields[$sourceClass->fieldNames[$sourceKeyColumn]]->getValue($sourceEntity);
                } else {
                    throw MappingException::joinColumnMustPointToMappedField(
                        $sourceClass->name, $sourceKeyColumn
                    );
                }
            }
        }

        $sql = $this->_getSelectEntitiesSQL($criteria, $assoc, 0, $limit, $offset);
        list($params, $types) = $this->expandParameters($criteria);
        return $this->_conn->executeQuery($sql, $params, $types);
    }

    /**
     * Gets the SELECT SQL to select one or more entities by a set of field criteria.
     *
     * @param array $criteria
     * @param AssociationMapping $assoc
     * @param string $orderBy
     * @param int $lockMode
     * @param int $limit
     * @param int $offset
     * @param array $orderBy
     * @return string
     * @todo Refactor: _getSelectSQL(...)
     */
    protected function _getSelectEntitiesSQL(array $criteria, $assoc = null, $lockMode = 0, $limit = null, $offset = null, array $orderBy = null)
    {
        $joinSql = $assoc != null && $assoc['type'] == ClassMetadata::MANY_TO_MANY ?
                $this->_getSelectManyToManyJoinSQL($assoc) : '';

        $conditionSql = $this->_getSelectConditionSQL($criteria, $assoc);

        $orderBy = ($assoc !== null && isset($assoc['orderBy'])) ? $assoc['orderBy'] : $orderBy;
        $orderBySql = $orderBy ? $this->_getOrderBySQL($orderBy, $this->_getSQLTableAlias($this->_class->name)) : '';

        $lockSql = '';
        if ($lockMode == LockMode::PESSIMISTIC_READ) {
            $lockSql = ' ' . $this->_platform->getReadLockSql();
        } else if ($lockMode == LockMode::PESSIMISTIC_WRITE) {
            $lockSql = ' ' . $this->_platform->getWriteLockSql();
        }

        return $this->_platform->modifyLimitQuery('SELECT ' . $this->_getSelectColumnListSQL()
             . $this->_platform->appendLockHint(' FROM ' . $this->_class->getQuotedTableName($this->_platform) . ' '
             . $this->_getSQLTableAlias($this->_class->name), $lockMode)
             . $this->_selectJoinSql . $joinSql
             . ($conditionSql ? ' WHERE ' . $conditionSql : '')
             . $orderBySql, $limit, $offset)
             . $lockSql;
    }

    /**
     * Gets the ORDER BY SQL snippet for ordered collections.
     * 
     * @param array $orderBy
     * @param string $baseTableAlias
     * @return string
     * @todo Rename: _getOrderBySQL
     */
    protected final function _getOrderBySQL(array $orderBy, $baseTableAlias)
    {
        $orderBySql = '';
        foreach ($orderBy as $fieldName => $orientation) {
            if ( ! isset($this->_class->fieldMappings[$fieldName])) {
                throw ORMException::unrecognizedField($fieldName);
            }

            $tableAlias = isset($this->_class->fieldMappings[$fieldName]['inherited']) ?
                    $this->_getSQLTableAlias($this->_class->fieldMappings[$fieldName]['inherited'])
                    : $baseTableAlias;

            $columnName = $this->_class->getQuotedColumnName($fieldName, $this->_platform);
            $orderBySql .= $orderBySql ? ', ' : ' ORDER BY ';
            $orderBySql .= $tableAlias . '.' . $columnName . ' ' . $orientation;
        }

        return $orderBySql;
    }

    /**
     * Gets the SQL fragment with the list of columns to select when querying for
     * an entity in this persister.
     *
     * Subclasses should override this method to alter or change the select column
     * list SQL fragment. Note that in the implementation of BasicEntityPersister
     * the resulting SQL fragment is generated only once and cached in {@link _selectColumnListSql}.
     * Subclasses may or may not do the same.
     * 
     * @return string The SQL fragment.
     * @todo Rename: _getSelectColumnsSQL()
     */
    protected function _getSelectColumnListSQL()
    {
        if ($this->_selectColumnListSql !== null) {
            return $this->_selectColumnListSql;
        }

        $columnList = '';
        $this->_rsm = new Query\ResultSetMapping();
        $this->_rsm->addEntityResult($this->_class->name, 'r'); // r for root

        // Add regular columns to select list
        foreach ($this->_class->fieldNames as $field) {
            if ($columnList) $columnList .= ', ';
            $columnList .= $this->_getSelectColumnSQL($field, $this->_class);
        }

        $this->_selectJoinSql = '';
        $eagerAliasCounter = 0;
        foreach ($this->_class->associationMappings as $assocField => $assoc) {
            $assocColumnSQL = $this->_getSelectColumnAssociationSQL($assocField, $assoc, $this->_class);
            if ($assocColumnSQL) {
                if ($columnList) $columnList .= ', ';
                $columnList .= $assocColumnSQL;
            }
            
            if ($assoc['type'] & ClassMetadata::TO_ONE && ($assoc['fetch'] == ClassMetadata::FETCH_EAGER || !$assoc['isOwningSide'])) {
                $eagerEntity = $this->_em->getClassMetadata($assoc['targetEntity']);
                if ($eagerEntity->inheritanceType != ClassMetadata::INHERITANCE_TYPE_NONE) {
                    continue; // now this is why you shouldn't use inheritance
                }
                
                $assocAlias = 'e' . ($eagerAliasCounter++);
                $this->_rsm->addJoinedEntityResult($assoc['targetEntity'], $assocAlias, 'r', $assocField);
                
                foreach ($eagerEntity->fieldNames AS $field) {
                    if ($columnList) $columnList .= ', ';
                    $columnList .= $this->_getSelectColumnSQL($field, $eagerEntity, $assocAlias);
                }
                
                foreach ($eagerEntity->associationMappings as $assoc2Field => $assoc2) {
                    $assoc2ColumnSQL = $this->_getSelectColumnAssociationSQL($assoc2Field, $assoc2, $eagerEntity, $assocAlias);
                    if ($assoc2ColumnSQL) {
                        if ($columnList) $columnList .= ', ';
                        $columnList .= $assoc2ColumnSQL;
                    }
                }
                $this->_selectJoinSql .= ' LEFT JOIN'; // TODO: Inner join when all join columns are NOT nullable.
                $first = true;
                if ($assoc['isOwningSide']) {
                    $this->_selectJoinSql .= ' ' . $eagerEntity->table['name'] . ' ' . $this->_getSQLTableAlias($eagerEntity->name, $assocAlias) .' ON ';
                    
                    foreach ($assoc['sourceToTargetKeyColumns'] AS $sourceCol => $targetCol) {
                        if (!$first) {
                            $this->_selectJoinSql .= ' AND ';
                        }
                        $this->_selectJoinSql .= $this->_getSQLTableAlias($assoc['sourceEntity']) . '.'.$sourceCol.' = ' .
                                                 $this->_getSQLTableAlias($assoc['targetEntity'], $assocAlias) . '.'.$targetCol.' ';
                        $first = false;
                    }
                } else {
                    $eagerEntity = $this->_em->getClassMetadata($assoc['targetEntity']);
                    $owningAssoc = $eagerEntity->getAssociationMapping($assoc['mappedBy']);
                    
                    $this->_selectJoinSql .= ' ' . $eagerEntity->table['name'] . ' ' . $this->_getSQLTableAlias($eagerEntity->name, $assocAlias) .' ON ';

                    foreach ($owningAssoc['sourceToTargetKeyColumns'] AS $sourceCol => $targetCol) {
                        if (!$first) {
                            $this->_selectJoinSql .= ' AND ';
                        }
                        $this->_selectJoinSql .= $this->_getSQLTableAlias($owningAssoc['sourceEntity'], $assocAlias) . '.'.$sourceCol.' = ' .
                                                 $this->_getSQLTableAlias($owningAssoc['targetEntity']) . '.' . $targetCol . ' ';
                        $first = false;
                    }
                }
            }
        }

        $this->_selectColumnListSql = $columnList;

        return $this->_selectColumnListSql;
    }
    
    protected function _getSelectColumnAssociationSQL($field, $assoc, ClassMetadata $class, $alias = 'r')
    {
        $columnList = '';
        if ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE) {
            foreach ($assoc['targetToSourceKeyColumns'] as $srcColumn) {
                if ($columnList) $columnList .= ', ';

                $columnAlias = $srcColumn . $this->_sqlAliasCounter++;
                $columnList .= $this->_getSQLTableAlias($class->name, ($alias == 'r' ? '' : $alias) )  . ".$srcColumn AS $columnAlias";
                $resultColumnName = $this->_platform->getSQLResultCasing($columnAlias);
                $this->_rsm->addMetaResult($alias, $this->_platform->getSQLResultCasing($columnAlias), $srcColumn, isset($assoc['id']) && $assoc['id'] === true);
            }
        }
        return $columnList;
    }

    /**
     * Gets the SQL join fragment used when selecting entities from a
     * many-to-many association.
     *
     * @param ManyToManyMapping $manyToMany
     * @return string
     */
    protected function _getSelectManyToManyJoinSQL(array $manyToMany)
    {
        if ($manyToMany['isOwningSide']) {
            $owningAssoc = $manyToMany;
            $joinClauses = $manyToMany['relationToTargetKeyColumns'];
        } else {
            $owningAssoc = $this->_em->getClassMetadata($manyToMany['targetEntity'])->associationMappings[$manyToMany['mappedBy']];
            $joinClauses = $owningAssoc['relationToSourceKeyColumns'];
        }
        
        $joinTableName = $this->_class->getQuotedJoinTableName($owningAssoc, $this->_platform);
        
        $joinSql = '';
        foreach ($joinClauses as $joinTableColumn => $sourceColumn) {
            if ($joinSql != '') $joinSql .= ' AND ';

            if ($this->_class->containsForeignIdentifier && !isset($this->_class->fieldNames[$sourceColumn])) {
                $quotedColumn = $sourceColumn; // join columns cannot be quoted
            } else {
                $quotedColumn = $this->_class->getQuotedColumnName($this->_class->fieldNames[$sourceColumn], $this->_platform);
            }

            $joinSql .= $this->_getSQLTableAlias($this->_class->name) .
                    '.' . $quotedColumn . ' = '
                    . $joinTableName . '.' . $joinTableColumn;
        }

        return " INNER JOIN $joinTableName ON $joinSql";
    }

    /**
     * Gets the INSERT SQL used by the persister to persist a new entity.
     * 
     * @return string
     */
    protected function _getInsertSQL()
    {
        if ($this->_insertSql === null) {
            $insertSql = '';
            $columns = $this->_getInsertColumnList();
            if (empty($columns)) {
                $insertSql = $this->_platform->getEmptyIdentityInsertSQL(
                        $this->_class->getQuotedTableName($this->_platform),
                        $this->_class->getQuotedColumnName($this->_class->identifier[0], $this->_platform)
                );
            } else {
                $columns = array_unique($columns);
                $values = array_fill(0, count($columns), '?');

                $insertSql = 'INSERT INTO ' . $this->_class->getQuotedTableName($this->_platform)
                        . ' (' . implode(', ', $columns) . ') '
                        . 'VALUES (' . implode(', ', $values) . ')';
            }
            $this->_insertSql = $insertSql;
        }
        return $this->_insertSql;
    }

    /**
     * Gets the list of columns to put in the INSERT SQL statement.
     *
     * Subclasses should override this method to alter or change the list of
     * columns placed in the INSERT statements used by the persister.
     *
     * @return array The list of columns.
     */
    protected function _getInsertColumnList()
    {
        $columns = array();
        foreach ($this->_class->reflFields as $name => $field) {
            if ($this->_class->isVersioned && $this->_class->versionField == $name) {
                continue;
            }
            if (isset($this->_class->associationMappings[$name])) {
                $assoc = $this->_class->associationMappings[$name];
                if ($assoc['isOwningSide'] && $assoc['type'] & ClassMetadata::TO_ONE) {
                    foreach ($assoc['targetToSourceKeyColumns'] as $sourceCol) {
                        $columns[] = $sourceCol;
                    }
                }
            } else if ($this->_class->generatorType != ClassMetadata::GENERATOR_TYPE_IDENTITY ||
                    $this->_class->identifier[0] != $name) {
                $columns[] = $this->_class->getQuotedColumnName($name, $this->_platform);
            }
        }

        return $columns;
    }

    /**
     * Gets the SQL snippet of a qualified column name for the given field name.
     *
     * @param string $field The field name.
     * @param ClassMetadata $class The class that declares this field. The table this class is
     *                             mapped to must own the column for the given field.
     * @param string $alias
     */
    protected function _getSelectColumnSQL($field, ClassMetadata $class, $alias = 'r')
    {
        $columnName = $class->columnNames[$field];
        $sql = $this->_getSQLTableAlias($class->name, $alias == 'r' ? '' : $alias) . '.' . $class->getQuotedColumnName($field, $this->_platform);
        $columnAlias = $this->_platform->getSQLResultCasing($columnName . $this->_sqlAliasCounter++);
        $this->_rsm->addFieldResult($alias, $columnAlias, $field);

        return "$sql AS $columnAlias";
    }

    /**
     * Gets the SQL table alias for the given class name.
     * 
     * @param string $className
     * @return string The SQL table alias.
     * @todo Reconsider. Binding table aliases to class names is not such a good idea.
     */
    protected function _getSQLTableAlias($className, $assocName = '')
    {
        if ($assocName) {
            $className .= '#'.$assocName;
        }
        
        if (isset($this->_sqlTableAliases[$className])) {
            return $this->_sqlTableAliases[$className];
        }
        $tableAlias = 't' . $this->_sqlAliasCounter++;

        $this->_sqlTableAliases[$className] = $tableAlias;
        return $tableAlias;
    }

    /**
     * Lock all rows of this entity matching the given criteria with the specified pessimistic lock mode
     *
     * @param array $criteria
     * @param int $lockMode
     * @return void
     */
    public function lock(array $criteria, $lockMode)
    {
        $conditionSql = $this->_getSelectConditionSQL($criteria);

        if ($lockMode == LockMode::PESSIMISTIC_READ) {
            $lockSql = $this->_platform->getReadLockSql();
        } else if ($lockMode == LockMode::PESSIMISTIC_WRITE) {
            $lockSql = $this->_platform->getWriteLockSql();
        }

        $sql = 'SELECT 1 '
             . $this->_platform->appendLockHint($this->getLockTablesSql(), $lockMode)
             . ($conditionSql ? ' WHERE ' . $conditionSql : '') . ' ' . $lockSql;
        list($params, $types) = $this->expandParameters($criteria);
        $stmt = $this->_conn->executeQuery($sql, $params, $types);
    }

    /**
     * Get the FROM and optionally JOIN conditions to lock the entity managed by this persister.
     *
     * @return string
     */
    protected function getLockTablesSql()
    {
        return 'FROM ' . $this->_class->getQuotedTableName($this->_platform) . ' '
                . $this->_getSQLTableAlias($this->_class->name);
    }

    /**
     * Gets the conditional SQL fragment used in the WHERE clause when selecting
     * entities in this persister.
     *
     * Subclasses are supposed to override this method if they intend to change
     * or alter the criteria by which entities are selected.
     *
     * @param array $criteria
     * @param AssociationMapping $assoc
     * @return string
     */
    protected function _getSelectConditionSQL(array $criteria, $assoc = null)
    {
        $conditionSql = '';
        foreach ($criteria as $field => $value) {
            $conditionSql .= $conditionSql ? ' AND ' : '';

            if (isset($this->_class->columnNames[$field])) {
                if (isset($this->_class->fieldMappings[$field]['inherited'])) {
                    $conditionSql .= $this->_getSQLTableAlias($this->_class->fieldMappings[$field]['inherited']) . '.';
                } else {
                    $conditionSql .= $this->_getSQLTableAlias($this->_class->name) . '.';
                }
                $conditionSql .= $this->_class->getQuotedColumnName($field, $this->_platform);
            } else if (isset($this->_class->associationMappings[$field])) {
                if (!$this->_class->associationMappings[$field]['isOwningSide']) {
                    throw ORMException::invalidFindByInverseAssociation($this->_class->name, $field);
                }

                if (isset($this->_class->associationMappings[$field]['inherited'])) {
                    $conditionSql .= $this->_getSQLTableAlias($this->_class->associationMappings[$field]['inherited']) . '.';
                } else {
                    $conditionSql .= $this->_getSQLTableAlias($this->_class->name) . '.';
                }

                $conditionSql .= $this->_class->associationMappings[$field]['joinColumns'][0]['name'];
            } else if ($assoc !== null && strpos($field, " ") === false && strpos($field, "(") === false) {
                // very careless developers could potentially open up this normally hidden api for userland attacks,
                // therefore checking for spaces and function calls which are not allowed.
                
                // found a join column condition, not really a "field"
                $conditionSql .= $field;
            } else {
                throw ORMException::unrecognizedField($field);
            }
            $conditionSql .= (is_array($value)) ? ' IN (?)' : (($value === null) ? ' IS NULL' : ' = ?');
        }
        return $conditionSql;
    }

    /**
     * Return an array with (sliced or full list) of elements in the specified collection.
     *
     * @param array $assoc
     * @param object $sourceEntity
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getOneToManyCollection(array $assoc, $sourceEntity, $offset = null, $limit = null)
    {
        $stmt = $this->getOneToManyStatement($assoc, $sourceEntity, $offset, $limit);
        return $this->loadArrayFromStatement($assoc, $stmt);
    }

    /**
     * Loads a collection of entities in a one-to-many association.
     *
     * @param array $assoc
     * @param object $sourceEntity
     * @param PersistentCollection $coll The collection to load/fill.
     * @param int|null $offset
     * @param int|null $limit
     */
    public function loadOneToManyCollection(array $assoc, $sourceEntity, PersistentCollection $coll)
    {
        $stmt = $this->getOneToManyStatement($assoc, $sourceEntity);
        $this->loadCollectionFromStatement($assoc, $stmt, $coll);
    }

    /**
     * Build criteria and execute SQL statement to fetch the one to many entities from.
     *
     * @param array $assoc
     * @param object $sourceEntity
     * @param int|null $offset
     * @param int|null $limit
     * @return Doctrine\DBAL\Statement
     */
    private function getOneToManyStatement(array $assoc, $sourceEntity, $offset = null, $limit = null)
    {
        $criteria = array();
        $owningAssoc = $this->_class->associationMappings[$assoc['mappedBy']];
        $sourceClass = $this->_em->getClassMetadata($assoc['sourceEntity']);

        $tableAlias = isset($owningAssoc['inherited']) ?
                    $this->_getSQLTableAlias($owningAssoc['inherited'])
                    : $this->_getSQLTableAlias($this->_class->name);

        foreach ($owningAssoc['targetToSourceKeyColumns'] as $sourceKeyColumn => $targetKeyColumn) {
            if ($sourceClass->containsForeignIdentifier) {
                $field = $sourceClass->getFieldForColumn($sourceKeyColumn);
                $value = $sourceClass->reflFields[$field]->getValue($sourceEntity);
                if (isset($sourceClass->associationMappings[$field])) {
                    $value = $this->_em->getUnitOfWork()->getEntityIdentifier($value);
                    $value = $value[$this->_em->getClassMetadata($assoc['targetEntity'])->identifier[0]];
                }
                $criteria[$tableAlias . "." . $targetKeyColumn] = $value;
            } else {
                $criteria[$tableAlias . "." . $targetKeyColumn] = $sourceClass->reflFields[$sourceClass->fieldNames[$sourceKeyColumn]]->getValue($sourceEntity);
            }
        }

        $sql = $this->_getSelectEntitiesSQL($criteria, $assoc, 0, $limit, $offset);
        list($params, $types) = $this->expandParameters($criteria);

        return $this->_conn->executeQuery($sql, $params, $types);
    }

    /**
     * Expand the parameters from the given criteria and use the correct binding types if found.
     *
     * @param  array $criteria
     * @return array
     */
    private function expandParameters($criteria)
    {
        $params = $types = array();

        foreach ($criteria AS $field => $value) {
            if ($value === null) {
                continue; // skip null values.
            }

            $type = null;
            if (isset($this->_class->fieldMappings[$field])) {
                $type = Type::getType($this->_class->fieldMappings[$field]['type'])->getBindingType();
            }
            if (is_array($value)) {
                $type += Connection::ARRAY_PARAM_OFFSET;
            }
            
            $params[] = $value;
            $types[] = $type;
        }
        return array($params, $types);
    }

    /**
     * Checks whether the given managed entity exists in the database.
     *
     * @param object $entity
     * @return boolean TRUE if the entity exists in the database, FALSE otherwise.
     */
    public function exists($entity, array $extraConditions = array())
    {
        $criteria = $this->_class->getIdentifierValues($entity);
        if ($extraConditions) {
            $criteria = array_merge($criteria, $extraConditions);
        }

        $sql = 'SELECT 1 FROM ' . $this->_class->getQuotedTableName($this->_platform)
                . ' ' . $this->_getSQLTableAlias($this->_class->name)
                . ' WHERE ' . $this->_getSelectConditionSQL($criteria);

        return (bool) $this->_conn->fetchColumn($sql, array_values($criteria));
    }
}
