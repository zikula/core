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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Mapping;

use ReflectionException,
    Doctrine\ORM\ORMException,
    Doctrine\ORM\EntityManager,
    Doctrine\DBAL\Platforms,
    Doctrine\ORM\Events,
    Doctrine\Common\Persistence\Mapping\ReflectionService,
    Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface,
    Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory,
    Doctrine\ORM\Id\IdentityGenerator,
    Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * The ClassMetadataFactory is used to create ClassMetadata objects that contain all the
 * metadata mapping information of a class which describes how a class should be mapped
 * to a relational database.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class ClassMetadataFactory extends AbstractClassMetadataFactory
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $targetPlatform;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    private $driver;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $evm;

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritDoc}.
     */
    protected function initialize()
    {
        $this->driver = $this->em->getConfiguration()->getMetadataDriverImpl();
        $this->targetPlatform = $this->em->getConnection()->getDatabasePlatform();
        $this->evm = $this->em->getEventManager();
        $this->initialized = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {
        /* @var $class ClassMetadata */
        /* @var $parent ClassMetadata */
        if ($parent) {
            $class->setInheritanceType($parent->inheritanceType);
            $class->setDiscriminatorColumn($parent->discriminatorColumn);
            $class->setIdGeneratorType($parent->generatorType);
            $this->addInheritedFields($class, $parent);
            $this->addInheritedRelations($class, $parent);
            $class->setIdentifier($parent->identifier);
            $class->setVersioned($parent->isVersioned);
            $class->setVersionField($parent->versionField);
            $class->setDiscriminatorMap($parent->discriminatorMap);
            $class->setLifecycleCallbacks($parent->lifecycleCallbacks);
            $class->setChangeTrackingPolicy($parent->changeTrackingPolicy);

            if ($parent->isMappedSuperclass) {
                $class->setCustomRepositoryClass($parent->customRepositoryClassName);
            }
        }

        // Invoke driver
        try {
            $this->driver->loadMetadataForClass($class->getName(), $class);
        } catch (ReflectionException $e) {
            throw MappingException::reflectionFailure($class->getName(), $e);
        }

        // If this class has a parent the id generator strategy is inherited.
        // However this is only true if the hierarchy of parents contains the root entity,
        // if it consists of mapped superclasses these don't necessarily include the id field.
        if ($parent && $rootEntityFound) {
            if ($parent->isIdGeneratorSequence()) {
                $class->setSequenceGeneratorDefinition($parent->sequenceGeneratorDefinition);
            } else if ($parent->isIdGeneratorTable()) {
                $class->tableGeneratorDefinition = $parent->tableGeneratorDefinition;
            }

            if ($parent->generatorType) {
                $class->setIdGeneratorType($parent->generatorType);
            }

            if ($parent->idGenerator) {
                $class->setIdGenerator($parent->idGenerator);
            }
        } else {
            $this->completeIdGeneratorMapping($class);
        }

        if ($parent && $parent->isInheritanceTypeSingleTable()) {
            $class->setPrimaryTable($parent->table);
        }

        if ($parent && $parent->containsForeignIdentifier) {
            $class->containsForeignIdentifier = true;
        }

        if ($parent && !empty($parent->namedQueries)) {
            $this->addInheritedNamedQueries($class, $parent);
        }

        if ($parent && !empty($parent->namedNativeQueries)) {
            $this->addInheritedNamedNativeQueries($class, $parent);
        }

        if ($parent && !empty($parent->sqlResultSetMappings)) {
            $this->addInheritedSqlResultSetMappings($class, $parent);
        }

        $class->setParentClasses($nonSuperclassParents);

        if ( $class->isRootEntity() && ! $class->isInheritanceTypeNone() && ! $class->discriminatorMap) {
            $this->addDefaultDiscriminatorMap($class);
        }

        if ($this->evm->hasListeners(Events::loadClassMetadata)) {
            $eventArgs = new LoadClassMetadataEventArgs($class, $this->em);
            $this->evm->dispatchEvent(Events::loadClassMetadata, $eventArgs);
        }

        $this->wakeupReflection($class, $this->getReflectionService());
        $this->validateRuntimeMetadata($class, $parent);
    }

    /**
     * Validate runtime metadata is correctly defined.
     *
     * @param ClassMetadata $class
     * @param $parent
     * @throws MappingException
     */
    protected function validateRuntimeMetadata($class, $parent)
    {
        if ( ! $class->reflClass ) {
            // only validate if there is a reflection class instance
            return;
        }

        $class->validateIdentifier();
        $class->validateAssocations();
        $class->validateLifecycleCallbacks($this->getReflectionService());

        // verify inheritance
        if ( ! $class->isMappedSuperclass && !$class->isInheritanceTypeNone()) {
            if ( ! $parent) {
                if (count($class->discriminatorMap) == 0) {
                    throw MappingException::missingDiscriminatorMap($class->name);
                }
                if ( ! $class->discriminatorColumn) {
                    throw MappingException::missingDiscriminatorColumn($class->name);
                }
            } else if ($parent && !$class->reflClass->isAbstract() && !in_array($class->name, array_values($class->discriminatorMap))) {
                // enforce discriminator map for all entities of an inheritance hierarchy, otherwise problems will occur.
                throw MappingException::mappedClassNotPartOfDiscriminatorMap($class->name, $class->rootEntityName);
            }
        } else if ($class->isMappedSuperclass && $class->name == $class->rootEntityName && (count($class->discriminatorMap) || $class->discriminatorColumn)) {
            // second condition is necessary for mapped superclasses in the middle of an inheritance hierarchy
            throw MappingException::noInheritanceOnMappedSuperClass($class->name);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function newClassMetadataInstance($className)
    {
        return new ClassMetadata($className, $this->em->getConfiguration()->getNamingStrategy());
    }

    /**
     * Adds a default discriminator map if no one is given
     *
     * If an entity is of any inheritance type and does not contain a
     * discriminator map, then the map is generated automatically. This process
     * is expensive computation wise.
     *
     * The automatically generated discriminator map contains the lowercase short name of
     * each class as key.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     * @throws MappingException
     */
    private function addDefaultDiscriminatorMap(ClassMetadata $class)
    {
        $allClasses = $this->driver->getAllClassNames();
        $fqcn = $class->getName();
        $map = array($this->getShortName($class->name) => $fqcn);

        $duplicates = array();
        foreach ($allClasses as $subClassCandidate) {
            if (is_subclass_of($subClassCandidate, $fqcn)) {
                $shortName = $this->getShortName($subClassCandidate);

                if (isset($map[$shortName])) {
                    $duplicates[] = $shortName;
                }

                $map[$shortName] = $subClassCandidate;
            }
        }

        if ($duplicates) {
            throw MappingException::duplicateDiscriminatorEntry($class->name, $duplicates, $map);
        }

        $class->setDiscriminatorMap($map);
    }

    /**
     * Get the lower-case short name of a class.
     *
     * @param string $className
     * @return string
     */
    private function getShortName($className)
    {
        if (strpos($className, "\\") === false) {
            return strtolower($className);
        }

        $parts = explode("\\", $className);
        return strtolower(end($parts));
    }

    /**
     * Adds inherited fields to the subclass mapping.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     */
    private function addInheritedFields(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->fieldMappings as $mapping) {
            if ( ! isset($mapping['inherited']) && ! $parentClass->isMappedSuperclass) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->addInheritedFieldMapping($mapping);
        }
        foreach ($parentClass->reflFields as $name => $field) {
            $subClass->reflFields[$name] = $field;
        }
    }

    /**
     * Adds inherited association mappings to the subclass mapping.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     * @throws MappingException
     */
    private function addInheritedRelations(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->associationMappings as $field => $mapping) {
            if ($parentClass->isMappedSuperclass) {
                if ($mapping['type'] & ClassMetadata::TO_MANY && !$mapping['isOwningSide']) {
                    throw MappingException::illegalToManyAssocationOnMappedSuperclass($parentClass->name, $field);
                }
                $mapping['sourceEntity'] = $subClass->name;
            }

            //$subclassMapping = $mapping;
            if ( ! isset($mapping['inherited']) && ! $parentClass->isMappedSuperclass) {
                $mapping['inherited'] = $parentClass->name;
            }
            if ( ! isset($mapping['declared'])) {
                $mapping['declared'] = $parentClass->name;
            }
            $subClass->addInheritedAssociationMapping($mapping);
        }
    }

    /**
     * Adds inherited named queries to the subclass mapping.
     *
     * @since 2.2
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     */
    private function addInheritedNamedQueries(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->namedQueries as $name => $query) {
            if ( ! isset ($subClass->namedQueries[$name])) {
                $subClass->addNamedQuery(array(
                    'name'  => $query['name'],
                    'query' => $query['query']
                ));
            }
        }
    }

    /**
     * Adds inherited named native queries to the subclass mapping.
     *
     * @since 2.3
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     */
    private function addInheritedNamedNativeQueries(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->namedNativeQueries as $name => $query) {
            if ( ! isset ($subClass->namedNativeQueries[$name])) {
                $subClass->addNamedNativeQuery(array(
                    'name'              => $query['name'],
                    'query'             => $query['query'],
                    'isSelfClass'       => $query['isSelfClass'],
                    'resultSetMapping'  => $query['resultSetMapping'],
                    'resultClass'       => $query['isSelfClass'] ? $subClass->name : $query['resultClass'],
                ));
            }
        }
    }

    /**
     * Adds inherited sql result set mappings to the subclass mapping.
     *
     * @since 2.3
     * @param \Doctrine\ORM\Mapping\ClassMetadata $subClass
     * @param \Doctrine\ORM\Mapping\ClassMetadata $parentClass
     */
    private function addInheritedSqlResultSetMappings(ClassMetadata $subClass, ClassMetadata $parentClass)
    {
        foreach ($parentClass->sqlResultSetMappings as $name => $mapping) {
            if ( ! isset ($subClass->sqlResultSetMappings[$name])) {
                $entities = array();
                foreach ($mapping['entities'] as $entity) {
                    $entities[] = array(
                        'fields'                => $entity['fields'],
                        'isSelfClass'           => $entity['isSelfClass'],
                        'discriminatorColumn'   => $entity['discriminatorColumn'],
                        'entityClass'           => $entity['isSelfClass'] ? $subClass->name : $entity['entityClass'],
                    );
                }

                $subClass->addSqlResultSetMapping(array(
                    'name'          => $mapping['name'],
                    'columns'       => $mapping['columns'],
                    'entities'      => $entities,
                ));
            }
        }
    }

    /**
     * Completes the ID generator mapping. If "auto" is specified we choose the generator
     * most appropriate for the targeted database platform.
     *
     * @param ClassMetadataInfo $class
     * @throws ORMException
     */
    private function completeIdGeneratorMapping(ClassMetadataInfo $class)
    {
        $idGenType = $class->generatorType;
        if ($idGenType == ClassMetadata::GENERATOR_TYPE_AUTO) {
            if ($this->targetPlatform->prefersSequences()) {
                $class->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_SEQUENCE);
            } else if ($this->targetPlatform->prefersIdentityColumns()) {
                $class->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
            } else {
                $class->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_TABLE);
            }
        }

        // Create & assign an appropriate ID generator instance
        switch ($class->generatorType) {
            case ClassMetadata::GENERATOR_TYPE_IDENTITY:
                // For PostgreSQL IDENTITY (SERIAL) we need a sequence name. It defaults to
                // <table>_<column>_seq in PostgreSQL for SERIAL columns.
                // Not pretty but necessary and the simplest solution that currently works.
                $sequenceName = null;

                if ($this->targetPlatform instanceof Platforms\PostgreSQLPlatform) {
                    $fieldName      = $class->getSingleIdentifierFieldName();
                    $columnName     = $class->getSingleIdentifierColumnName();
                    $quoted         = isset($class->fieldMappings[$fieldName]['quoted']) || isset($class->table['quoted']);
                    $sequenceName   = $class->getTableName() . '_' . $columnName . '_seq';
                    $definition     = array(
                        'sequenceName' => $this->targetPlatform->fixSchemaElementName($sequenceName)
                    );

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $sequenceName = $this->em->getConfiguration()->getQuoteStrategy()->getSequenceName($definition, $class, $this->targetPlatform);
                }

                $class->setIdGenerator(new \Doctrine\ORM\Id\IdentityGenerator($sequenceName));
                break;

            case ClassMetadata::GENERATOR_TYPE_SEQUENCE:
                // If there is no sequence definition yet, create a default definition
                $definition = $class->sequenceGeneratorDefinition;

                if ( ! $definition) {
                    $fieldName      = $class->getSingleIdentifierFieldName();
                    $columnName     = $class->getSingleIdentifierColumnName();
                    $quoted         = isset($class->fieldMappings[$fieldName]['quoted']) || isset($class->table['quoted']);
                    $sequenceName   = $class->getTableName() . '_' . $columnName . '_seq';
                    $definition     = array(
                        'sequenceName'      => $this->targetPlatform->fixSchemaElementName($sequenceName),
                        'allocationSize'    => 1,
                        'initialValue'      => 1,
                    );

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $class->setSequenceGeneratorDefinition($definition);
                }

                $sequenceGenerator = new \Doctrine\ORM\Id\SequenceGenerator(
                    $this->em->getConfiguration()->getQuoteStrategy()->getSequenceName($definition, $class, $this->targetPlatform),
                    $definition['allocationSize']
                );
                $class->setIdGenerator($sequenceGenerator);
                break;

            case ClassMetadata::GENERATOR_TYPE_NONE:
                $class->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_UUID:
                $class->setIdGenerator(new \Doctrine\ORM\Id\UuidGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_TABLE:
                throw new ORMException("TableGenerator not yet implemented.");
                break;

            case ClassMetadata::GENERATOR_TYPE_CUSTOM:
                $definition = $class->customGeneratorDefinition;
                if ( ! class_exists($definition['class'])) {
                    throw new ORMException("Can't instantiate custom generator : " .
                        $definition['class']);
                }
                $class->setIdGenerator(new $definition['class']);
                break;

            default:
                throw new ORMException("Unknown generator type: " . $class->generatorType);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function wakeupReflection(ClassMetadataInterface $class, ReflectionService $reflService)
    {
        /* @var $class ClassMetadata */
        $class->wakeupReflection($reflService);
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeReflection(ClassMetadataInterface $class, ReflectionService $reflService)
    {
        /* @var $class ClassMetadata */
        $class->initializeReflection($reflService);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return $this->em->getConfiguration()->getEntityNamespace($namespaceAlias) . '\\' . $simpleClassName;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * {@inheritDoc}
     */
    protected function isEntity(ClassMetadataInterface $class)
    {
        return isset($class->isMappedSuperclass) && $class->isMappedSuperclass === false;
    }
}
