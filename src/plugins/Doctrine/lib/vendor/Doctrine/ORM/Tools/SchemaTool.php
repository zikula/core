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

namespace Doctrine\ORM\Tools;

use Doctrine\ORM\ORMException,
    Doctrine\DBAL\Types\Type,
    Doctrine\DBAL\Schema\Schema,
    Doctrine\DBAL\Schema\Visitor\RemoveNamespacedAssets,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Internal\CommitOrderCalculator,
    Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs,
    Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * The SchemaTool is a tool to create/drop/update database schemas based on
 * <tt>ClassMetadata</tt> class descriptors.
 *
 * 
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class SchemaTool
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $platform;

    /**
     * The quote strategy.
     *
     * @var \Doctrine\ORM\Mapping\QuoteStrategy
     */
    private $quoteStrategy;

    /**
     * Initializes a new SchemaTool instance that uses the connection of the
     * provided EntityManager.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em               = $em;
        $this->platform         = $em->getConnection()->getDatabasePlatform();
        $this->quoteStrategy    = $em->getConfiguration()->getQuoteStrategy();
    }

    /**
     * Creates the database schema for the given array of ClassMetadata instances.
     *
     * @throws ToolsException
     * @param array $classes
     * @return void
     */
    public function createSchema(array $classes)
    {
        $createSchemaSql = $this->getCreateSchemaSql($classes);
        $conn = $this->em->getConnection();

        foreach ($createSchemaSql as $sql) {
            try {
                $conn->executeQuery($sql);
            } catch(\Exception $e) {
                throw ToolsException::schemaToolFailure($sql, $e);
            }
        }
    }

    /**
     * Gets the list of DDL statements that are required to create the database schema for
     * the given list of ClassMetadata instances.
     *
     * @param array $classes
     * @return array $sql The SQL statements needed to create the schema for the classes.
     */
    public function getCreateSchemaSql(array $classes)
    {
        $schema = $this->getSchemaFromMetadata($classes);
        return $schema->toSql($this->platform);
    }

    /**
     * Some instances of ClassMetadata don't need to be processed in the SchemaTool context. This method detects them.
     *
     * @param ClassMetadata $class
     * @param array $processedClasses
     * @return bool
     */
    private function processingNotRequired($class, array $processedClasses)
    {
        return (
            isset($processedClasses[$class->name]) ||
            $class->isMappedSuperclass ||
            ($class->isInheritanceTypeSingleTable() && $class->name != $class->rootEntityName)
        );
    }

    /**
     * From a given set of metadata classes this method creates a Schema instance.
     *
     * @param array $classes
     * @return Schema
     */
    public function getSchemaFromMetadata(array $classes)
    {
        // Reminder for processed classes, used for hierarchies
        $processedClasses       = array();
        $eventManager           = $this->em->getEventManager();
        $schemaManager          = $this->em->getConnection()->getSchemaManager();
        $metadataSchemaConfig   = $schemaManager->createSchemaConfig();

        $metadataSchemaConfig->setExplicitForeignKeyIndexes(false);
        $schema = new Schema(array(), array(), $metadataSchemaConfig);

        foreach ($classes as $class) {
            if ($this->processingNotRequired($class, $processedClasses)) {
                continue;
            }

            $table      = $schema->createTable($this->quoteStrategy->getTableName($class, $this->platform));
            $columns    = array(); // table columns

            if ($class->isInheritanceTypeSingleTable()) {
                $columns = $this->_gatherColumns($class, $table);
                $this->_gatherRelationsSql($class, $table, $schema);

                // Add the discriminator column
                $this->addDiscriminatorColumnDefinition($class, $table);

                // Aggregate all the information from all classes in the hierarchy
                foreach ($class->parentClasses as $parentClassName) {
                    // Parent class information is already contained in this class
                    $processedClasses[$parentClassName] = true;
                }

                foreach ($class->subClasses as $subClassName) {
                    $subClass = $this->em->getClassMetadata($subClassName);
                    $this->_gatherColumns($subClass, $table);
                    $this->_gatherRelationsSql($subClass, $table, $schema);
                    $processedClasses[$subClassName] = true;
                }
            } else if ($class->isInheritanceTypeJoined()) {
                // Add all non-inherited fields as columns
                $pkColumns = array();
                foreach ($class->fieldMappings as $fieldName => $mapping) {
                    if ( ! isset($mapping['inherited'])) {
                        $columnName = $this->quoteStrategy->getColumnName($mapping['fieldName'], $class, $this->platform);
                        $this->_gatherColumn($class, $mapping, $table);

                        if ($class->isIdentifier($fieldName)) {
                            $pkColumns[] = $columnName;
                        }
                    }
                }

                $this->_gatherRelationsSql($class, $table, $schema);

                // Add the discriminator column only to the root table
                if ($class->name == $class->rootEntityName) {
                    $this->addDiscriminatorColumnDefinition($class, $table);
                } else {
                    // Add an ID FK column to child tables
                    /* @var \Doctrine\ORM\Mapping\ClassMetadata $class */
                    $idMapping = $class->fieldMappings[$class->identifier[0]];
                    $this->_gatherColumn($class, $idMapping, $table);
                    $columnName = $this->quoteStrategy->getColumnName($class->identifier[0], $class, $this->platform);
                    // TODO: This seems rather hackish, can we optimize it?
                    $table->getColumn($columnName)->setAutoincrement(false);

                    $pkColumns[] = $columnName;

                    // Add a FK constraint on the ID column
                    $table->addUnnamedForeignKeyConstraint(
                        $this->quoteStrategy->getTableName($this->em->getClassMetadata($class->rootEntityName), $this->platform),
                        array($columnName), array($columnName), array('onDelete' => 'CASCADE')
                    );
                }

                $table->setPrimaryKey($pkColumns);

            } else if ($class->isInheritanceTypeTablePerClass()) {
                throw ORMException::notSupported();
            } else {
                $this->_gatherColumns($class, $table);
                $this->_gatherRelationsSql($class, $table, $schema);
            }

            $pkColumns = array();
            foreach ($class->identifier as $identifierField) {
                if (isset($class->fieldMappings[$identifierField])) {
                    $pkColumns[] = $this->quoteStrategy->getColumnName($identifierField, $class, $this->platform);
                } else if (isset($class->associationMappings[$identifierField])) {
                    /* @var $assoc \Doctrine\ORM\Mapping\OneToOne */
                    $assoc = $class->associationMappings[$identifierField];
                    foreach ($assoc['joinColumns'] as $joinColumn) {
                        $pkColumns[] = $this->quoteStrategy->getJoinColumnName($joinColumn, $class, $this->platform);
                    }
                }
            }

            if ( ! $table->hasIndex('primary')) {
                $table->setPrimaryKey($pkColumns);
            }

            if (isset($class->table['indexes'])) {
                foreach ($class->table['indexes'] as $indexName => $indexData) {
                    $table->addIndex($indexData['columns'], is_numeric($indexName) ? null : $indexName);
                }
            }

            if (isset($class->table['uniqueConstraints'])) {
                foreach ($class->table['uniqueConstraints'] as $indexName => $indexData) {
                    $table->addUniqueIndex($indexData['columns'], is_numeric($indexName) ? null : $indexName);
                }
            }

            if (isset($class->table['options'])) {
                foreach ($class->table['options'] as $key => $val) {
                    $table->addOption($key, $val);
                }
            }

            $processedClasses[$class->name] = true;

            if ($class->isIdGeneratorSequence() && $class->name == $class->rootEntityName) {
                $seqDef     = $class->sequenceGeneratorDefinition;
                $quotedName = $this->quoteStrategy->getSequenceName($seqDef, $class, $this->platform);
                if ( ! $schema->hasSequence($quotedName)) {
                    $schema->createSequence(
                        $quotedName,
                        $seqDef['allocationSize'],
                        $seqDef['initialValue']
                    );
                }
            }

            if ($eventManager->hasListeners(ToolEvents::postGenerateSchemaTable)) {
                $eventManager->dispatchEvent(ToolEvents::postGenerateSchemaTable, new GenerateSchemaTableEventArgs($class, $schema, $table));
            }
        }

        if ( ! $this->platform->supportsSchemas() && ! $this->platform->canEmulateSchemas() ) {
            $schema->visit(new RemoveNamespacedAssets());
        }

        if ($eventManager->hasListeners(ToolEvents::postGenerateSchema)) {
            $eventManager->dispatchEvent(ToolEvents::postGenerateSchema, new GenerateSchemaEventArgs($this->em, $schema));
        }

        return $schema;
    }

    /**
     * Gets a portable column definition as required by the DBAL for the discriminator
     * column of a class.
     *
     * @param ClassMetadata $class
     * @return array The portable column definition of the discriminator column as required by
     *              the DBAL.
     */
    private function addDiscriminatorColumnDefinition($class, $table)
    {
        $discrColumn = $class->discriminatorColumn;

        if ( ! isset($discrColumn['type']) || (strtolower($discrColumn['type']) == 'string' && $discrColumn['length'] === null)) {
            $discrColumn['type'] = 'string';
            $discrColumn['length'] = 255;
        }

        $options = array(
            'length'    => isset($discrColumn['length']) ? $discrColumn['length'] : null,
            'notnull'   => true
        );

        if (isset($discrColumn['columnDefinition'])) {
            $options['columnDefinition'] = $discrColumn['columnDefinition'];
        }

        $table->addColumn($discrColumn['name'], $discrColumn['type'], $options);
    }

    /**
     * Gathers the column definitions as required by the DBAL of all field mappings
     * found in the given class.
     *
     * @param ClassMetadata $class
     * @param Table $table
     * @return array The list of portable column definitions as required by the DBAL.
     */
    private function _gatherColumns($class, $table)
    {
        $columns = array();
        $pkColumns = array();

        foreach ($class->fieldMappings as $fieldName => $mapping) {
            if ($class->isInheritanceTypeSingleTable() && isset($mapping['inherited'])) {
                continue;
            }

            $column = $this->_gatherColumn($class, $mapping, $table);

            if ($class->isIdentifier($mapping['fieldName'])) {
                $pkColumns[] = $this->quoteStrategy->getColumnName($mapping['fieldName'], $class, $this->platform);
            }
        }

        // For now, this is a hack required for single table inheritence, since this method is called
        // twice by single table inheritence relations
        if(!$table->hasIndex('primary')) {
            //$table->setPrimaryKey($pkColumns);
        }

        return $columns;
    }

    /**
     * Creates a column definition as required by the DBAL from an ORM field mapping definition.
     *
     * @param ClassMetadata $class The class that owns the field mapping.
     * @param array $mapping The field mapping.
     * @param Table $table
     * @return array The portable column definition as required by the DBAL.
     */
    private function _gatherColumn($class, array $mapping, $table)
    {
        $columnName = $this->quoteStrategy->getColumnName($mapping['fieldName'], $class, $this->platform);
        $columnType = $mapping['type'];

        $options = array();
        $options['length'] = isset($mapping['length']) ? $mapping['length'] : null;
        $options['notnull'] = isset($mapping['nullable']) ? ! $mapping['nullable'] : true;
        if ($class->isInheritanceTypeSingleTable() && count($class->parentClasses) > 0) {
            $options['notnull'] = false;
        }

        $options['platformOptions'] = array();
        $options['platformOptions']['version'] = $class->isVersioned && $class->versionField == $mapping['fieldName'] ? true : false;

        if(strtolower($columnType) == 'string' && $options['length'] === null) {
            $options['length'] = 255;
        }

        if (isset($mapping['precision'])) {
            $options['precision'] = $mapping['precision'];
        }

        if (isset($mapping['scale'])) {
            $options['scale'] = $mapping['scale'];
        }

        if (isset($mapping['default'])) {
            $options['default'] = $mapping['default'];
        }

        if (isset($mapping['columnDefinition'])) {
            $options['columnDefinition'] = $mapping['columnDefinition'];
        }

        if (isset($mapping['options'])) {
            $options['customSchemaOptions'] = $mapping['options'];
        }

        if ($class->isIdGeneratorIdentity() && $class->getIdentifierFieldNames() == array($mapping['fieldName'])) {
            $options['autoincrement'] = true;
        }
        if ($class->isInheritanceTypeJoined() && $class->name != $class->rootEntityName) {
            $options['autoincrement'] = false;
        }

        if ($table->hasColumn($columnName)) {
            // required in some inheritance scenarios
            $table->changeColumn($columnName, $options);
        } else {
            $table->addColumn($columnName, $columnType, $options);
        }

        $isUnique = isset($mapping['unique']) ? $mapping['unique'] : false;
        if ($isUnique) {
            $table->addUniqueIndex(array($columnName));
        }
    }

    /**
     * Gathers the SQL for properly setting up the relations of the given class.
     * This includes the SQL for foreign key constraints and join tables.
     *
     * @param ClassMetadata $class
     * @param \Doctrine\DBAL\Schema\Table $table
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    private function _gatherRelationsSql($class, $table, $schema)
    {
        foreach ($class->associationMappings as $fieldName => $mapping) {
            if (isset($mapping['inherited'])) {
                continue;
            }

            $foreignClass = $this->em->getClassMetadata($mapping['targetEntity']);

            if ($mapping['type'] & ClassMetadata::TO_ONE && $mapping['isOwningSide']) {
                $primaryKeyColumns = $uniqueConstraints = array(); // PK is unnecessary for this relation-type

                $this->_gatherRelationJoinColumns($mapping['joinColumns'], $table, $foreignClass, $mapping, $primaryKeyColumns, $uniqueConstraints);

                foreach($uniqueConstraints as $indexName => $unique) {
                    $table->addUniqueIndex($unique['columns'], is_numeric($indexName) ? null : $indexName);
                }
            } else if ($mapping['type'] == ClassMetadata::ONE_TO_MANY && $mapping['isOwningSide']) {
                //... create join table, one-many through join table supported later
                throw ORMException::notSupported();
            } else if ($mapping['type'] == ClassMetadata::MANY_TO_MANY && $mapping['isOwningSide']) {
                // create join table
                $joinTable = $mapping['joinTable'];

                $theJoinTable = $schema->createTable($this->quoteStrategy->getJoinTableName($mapping, $foreignClass, $this->platform));

                $primaryKeyColumns = $uniqueConstraints = array();

                // Build first FK constraint (relation table => source table)
                $this->_gatherRelationJoinColumns($joinTable['joinColumns'], $theJoinTable, $class, $mapping, $primaryKeyColumns, $uniqueConstraints);

                // Build second FK constraint (relation table => target table)
                $this->_gatherRelationJoinColumns($joinTable['inverseJoinColumns'], $theJoinTable, $foreignClass, $mapping, $primaryKeyColumns, $uniqueConstraints);

                $theJoinTable->setPrimaryKey($primaryKeyColumns);

                foreach($uniqueConstraints as $indexName => $unique) {
                    $theJoinTable->addUniqueIndex($unique['columns'], is_numeric($indexName) ? null : $indexName);
                }
            }
        }
    }

    /**
     * Get the class metadata that is responsible for the definition of the referenced column name.
     *
     * Previously this was a simple task, but with DDC-117 this problem is actually recursive. If its
     * not a simple field, go through all identifier field names that are associations recursivly and
     * find that referenced column name.
     *
     * TODO: Is there any way to make this code more pleasing?
     *
     * @param ClassMetadata $class
     * @param string $referencedColumnName
     * @return array(ClassMetadata, referencedFieldName)
     */
    private function getDefiningClass($class, $referencedColumnName)
    {
        $referencedFieldName = $class->getFieldName($referencedColumnName);

        if ($class->hasField($referencedFieldName)) {
            return array($class, $referencedFieldName);
        } else if (in_array($referencedColumnName, $class->getIdentifierColumnNames())) {
            // it seems to be an entity as foreign key
            foreach ($class->getIdentifierFieldNames() as $fieldName) {
                if ($class->hasAssociation($fieldName) && $class->getSingleAssociationJoinColumnName($fieldName) == $referencedColumnName) {
                    return $this->getDefiningClass(
                        $this->em->getClassMetadata($class->associationMappings[$fieldName]['targetEntity']),
                        $class->getSingleAssociationReferencedJoinColumnName($fieldName)
                    );
                }
            }
        }

        return null;
    }

    /**
     * Gather columns and fk constraints that are required for one part of relationship.
     *
     * @param array $joinColumns
     * @param \Doctrine\DBAL\Schema\Table $theJoinTable
     * @param ClassMetadata $class
     * @param array $mapping
     * @param array $primaryKeyColumns
     * @param array $uniqueConstraints
     */
    private function _gatherRelationJoinColumns($joinColumns, $theJoinTable, $class, $mapping, &$primaryKeyColumns, &$uniqueConstraints)
    {
        $localColumns       = array();
        $foreignColumns     = array();
        $fkOptions          = array();
        $foreignTableName   = $this->quoteStrategy->getTableName($class, $this->platform);

        foreach ($joinColumns as $joinColumn) {

            list($definingClass, $referencedFieldName) = $this->getDefiningClass($class, $joinColumn['referencedColumnName']);

            if ( ! $definingClass) {
                throw new \Doctrine\ORM\ORMException(
                    "Column name `".$joinColumn['referencedColumnName']."` referenced for relation from ".
                    $mapping['sourceEntity'] . " towards ". $mapping['targetEntity'] . " does not exist."
                );
            }

            $quotedColumnName       = $this->quoteStrategy->getJoinColumnName($joinColumn, $class, $this->platform);
            $quotedRefColumnName    = $this->quoteStrategy->getReferencedJoinColumnName($joinColumn, $class, $this->platform);

            $primaryKeyColumns[]    = $quotedColumnName;
            $localColumns[]         = $quotedColumnName;
            $foreignColumns[]       = $quotedRefColumnName;

            if ( ! $theJoinTable->hasColumn($quotedColumnName)) {
                // Only add the column to the table if it does not exist already.
                // It might exist already if the foreign key is mapped into a regular
                // property as well.

                $fieldMapping = $definingClass->getFieldMapping($referencedFieldName);

                $columnDef = null;
                if (isset($joinColumn['columnDefinition'])) {
                    $columnDef = $joinColumn['columnDefinition'];
                } else if (isset($fieldMapping['columnDefinition'])) {
                    $columnDef = $fieldMapping['columnDefinition'];
                }
                $columnOptions = array('notnull' => false, 'columnDefinition' => $columnDef);
                if (isset($joinColumn['nullable'])) {
                    $columnOptions['notnull'] = !$joinColumn['nullable'];
                }
                if ($fieldMapping['type'] == "string" && isset($fieldMapping['length'])) {
                    $columnOptions['length'] = $fieldMapping['length'];
                } else if ($fieldMapping['type'] == "decimal") {
                    $columnOptions['scale'] = $fieldMapping['scale'];
                    $columnOptions['precision'] = $fieldMapping['precision'];
                }

                $theJoinTable->addColumn($quotedColumnName, $fieldMapping['type'], $columnOptions);
            }

            if (isset($joinColumn['unique']) && $joinColumn['unique'] == true) {
                $uniqueConstraints[] = array('columns' => array($quotedColumnName));
            }

            if (isset($joinColumn['onDelete'])) {
                $fkOptions['onDelete'] = $joinColumn['onDelete'];
            }
        }

        $theJoinTable->addUnnamedForeignKeyConstraint(
            $foreignTableName, $localColumns, $foreignColumns, $fkOptions
        );
    }

    /**
     * Drops the database schema for the given classes.
     *
     * In any way when an exception is thrown it is supressed since drop was
     * issued for all classes of the schema and some probably just don't exist.
     *
     * @param array $classes
     * @return void
     */
    public function dropSchema(array $classes)
    {
        $dropSchemaSql = $this->getDropSchemaSQL($classes);
        $conn = $this->em->getConnection();

        foreach ($dropSchemaSql as $sql) {
            try {
                $conn->executeQuery($sql);
            } catch(\Exception $e) {

            }
        }
    }

    /**
     * Drops all elements in the database of the current connection.
     *
     * @return void
     */
    public function dropDatabase()
    {
        $dropSchemaSql = $this->getDropDatabaseSQL();
        $conn = $this->em->getConnection();

        foreach ($dropSchemaSql as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Gets the SQL needed to drop the database schema for the connections database.
     *
     * @return array
     */
    public function getDropDatabaseSQL()
    {
        $sm = $this->em->getConnection()->getSchemaManager();
        $schema = $sm->createSchema();

        $visitor = new \Doctrine\DBAL\Schema\Visitor\DropSchemaSqlCollector($this->platform);
        /* @var $schema \Doctrine\DBAL\Schema\Schema */
        $schema->visit($visitor);
        return $visitor->getQueries();
    }

    /**
     * Get SQL to drop the tables defined by the passed classes.
     *
     * @param array $classes
     * @return array
     */
    public function getDropSchemaSQL(array $classes)
    {
        $visitor = new \Doctrine\DBAL\Schema\Visitor\DropSchemaSqlCollector($this->platform);
        $schema = $this->getSchemaFromMetadata($classes);

        $sm = $this->em->getConnection()->getSchemaManager();
        $fullSchema = $sm->createSchema();
        foreach ($fullSchema->getTables() as $table) {
            if ( ! $schema->hasTable($table->getName())) {
                foreach ($table->getForeignKeys() as $foreignKey) {
                    /* @var $foreignKey \Doctrine\DBAL\Schema\ForeignKeyConstraint */
                    if ($schema->hasTable($foreignKey->getForeignTableName())) {
                        $visitor->acceptForeignKey($table, $foreignKey);
                    }
                }
            } else {
                $visitor->acceptTable($table);
                foreach ($table->getForeignKeys() as $foreignKey) {
                    $visitor->acceptForeignKey($table, $foreignKey);
                }
            }
        }

        if ($this->platform->supportsSequences()) {
            foreach ($schema->getSequences() as $sequence) {
                $visitor->acceptSequence($sequence);
            }
            foreach ($schema->getTables() as $table) {
                /* @var $sequence Table */
                if ($table->hasPrimaryKey()) {
                    $columns = $table->getPrimaryKey()->getColumns();
                    if (count($columns) == 1) {
                        $checkSequence = $table->getName() . "_" . $columns[0] . "_seq";
                        if ($fullSchema->hasSequence($checkSequence)) {
                            $visitor->acceptSequence($fullSchema->getSequence($checkSequence));
                        }
                    }
                }
            }
        }

        return $visitor->getQueries();
    }

    /**
     * Updates the database schema of the given classes by comparing the ClassMetadata
     * instances to the current database schema that is inspected. If $saveMode is set
     * to true the command is executed in the Database, else SQL is returned.
     *
     * @param array $classes
     * @param boolean $saveMode
     * @return void
     */
    public function updateSchema(array $classes, $saveMode=false)
    {
        $updateSchemaSql = $this->getUpdateSchemaSql($classes, $saveMode);
        $conn = $this->em->getConnection();

        foreach ($updateSchemaSql as $sql) {
            $conn->executeQuery($sql);
        }
    }

    /**
     * Gets the sequence of SQL statements that need to be performed in order
     * to bring the given class mappings in-synch with the relational schema.
     * If $saveMode is set to true the command is executed in the Database,
     * else SQL is returned.
     *
     * @param array $classes The classes to consider.
     * @param boolean $saveMode True for writing to DB, false for SQL string
     * @return array The sequence of SQL statements.
     */
    public function getUpdateSchemaSql(array $classes, $saveMode=false)
    {
        $sm = $this->em->getConnection()->getSchemaManager();

        $fromSchema = $sm->createSchema();
        $toSchema = $this->getSchemaFromMetadata($classes);

        $comparator = new \Doctrine\DBAL\Schema\Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        if ($saveMode) {
            return $schemaDiff->toSaveSql($this->platform);
        } else {
            return $schemaDiff->toSql($this->platform);
        }
    }
}
