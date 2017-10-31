<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DoctrineUtil helper class.
 *
 * @deprecated
 */
class DoctrineUtil
{
    /**
     * Hydrate an array of values.
     *
     * @see Zikula_Doctrine_Hydrator_SingleScalarArray
     */
    const HYDRATE_SINGLE_SCALAR_ARRAY = "SingleScalarArray";

    /**
     * Constructor.
     *
     * @throws Exception DoctrineUtil can't be instanciated directly
     */
    public function __construct()
    {
        throw new Exception(__f('Static class %s cannot be instanciated', get_class($this)));
    }

    /**
     * Create Tables from models for given module.
     *
     * @param string $modname Module name
     * @param string $path    Optional force path to Model directory (used by plugins)
     *
     * @return void
     */
    public static function createTablesFromModels($modname, $path = null)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $modname = (isset($modname) ? strtolower((string)$modname) : '');
        $modinfo = ModUtil::getInfoFromName($modname);
        $osdir = DataUtil::formatForOS($modinfo['directory']);
        $base = ModUtil::TYPE_MODULE == $modinfo['type'] ? 'modules' : 'system';
        $dm = Doctrine_Manager::getInstance();
        $save = $dm->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);
        $dm->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
        $path = (is_null($path)) ? "$base/$osdir/lib/$osdir/Model" : "$base/$osdir/$path";
        Doctrine_Core::createTablesFromModels(realpath($path));
        $dm->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, $save);
    }

    /**
     * Aggressively load models.
     *
     * This helper is required because we are using PEAR naming standards with
     * our own autoloading.  Doctrine's model loading doesn't take this into
     * account in non agressive modes.
     *
     * In general, this method is NOT required.
     *
     * @param string $modname Module name to load models for
     *
     * @return void
     */
    public static function loadModels($modname)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $modname = (isset($modname) ? strtolower((string)$modname) : '');
        $modinfo = ModUtil::getInfoFromName($modname);
        $osdir = DataUtil::formatForOS($modinfo['directory']);
        $base = ModUtil::TYPE_MODULE == $modinfo['type'] ? 'modules' : 'system';
        $dm = Doctrine_Manager::getInstance();
        $save = $dm->getAttribute(Doctrine_Core::ATTR_MODEL_LOADING);
        $dm->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
        $path = "$base/$osdir/lib/$osdir/Model";

        // prevent exception when model folder does not exist
        if (file_exists($path)) {
            Doctrine_Core::loadModels(realpath($path));
        }

        $dm->setAttribute(Doctrine::ATTR_MODEL_LOADING, $save);
    }

    /**
     * Clear result cache.
     *
     * @return void
     */
    public static function clearResultCache()
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!(System::getVar('CACHE_ENABLE') && System::getVar('CACHE_RESULT'))) {
            return;
        }

        $driver = Doctrine_Manager::getInstance()->getCurrentConnection()->getAttribute(Doctrine_Core::ATTR_RESULT_CACHE);
        $driver->deleteByPrefix($driver->getOption('prefix'));
    }

    /**
     * Clear query cache.
     *
     * @return void
     */
    public static function clearQueryCache()
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!System::getVar('CACHE_ENABLE')) {
            return;
        }

        $driver = Doctrine_Manager::getInstance()->getCurrentConnection()->getAttribute(Doctrine_Core::ATTR_QUERY_CACHE);
        $driver->deleteByPrefix($driver->getOption('prefix'));
    }

    /**
     * Use result cache.
     *
     * @param Doctrine_Query $query Doctrine query object
     *
     * @return Doctrine_Query
     */
    public static function useResultsCache($query)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        if (!System::getVar('CACHE_ENABLE')) {
            return $query;
        }

        return $query->useResultsCache(true);
    }

    /**
     * Decorates table name with prefix.
     *
     * @param string $tableName Table name
     *
     * @return string decorated table name
     */
    public static function decorateTableName($tableName)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        return Doctrine_Manager::connection()->formatter->getTableName($tableName);
    }

    /**
     * Create table.
     *
     * @param string $tableName Table name
     * @param array  $columns   Column array
     * @param array  $options   Options
     *
     * @return void
     */
    public static function createTable($tableName, array $columns, array $options = [])
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->createTable($tableName, $columns, $options);
    }

    /**
     * Drop table.
     *
     * @param string $tableName Table name
     *
     * @return void
     */
    public static function dropTable($tableName)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->dropTable($tableName);
    }

    /**
     * Rename a table.
     *
     * @param string  $oldTableName Old table name
     * @param string  $newTableName New table name
     * @param boolean $check        Validate request only, default: false
     *
     * @return void
     */
    public static function renameTable($oldTableName, $newTableName, $check = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $oldTableName = self::decorateTableName($oldTableName);
        $newTableName = self::decorateTableName($newTableName);
        Doctrine_Manager::connection()->export->alterTable($oldTableName, ['name' => $newTableName], $check);
    }

    /**
     * Add a column to table.
     *
     * @param string  $tableName  Table name
     * @param string  $columnName Column name
     * @param array   $options    Options
     * @param boolean $check      Verifies request only, default: false
     *
     * @return void
     */
    public static function createColumn($tableName, $columnName, $options = [], $check = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->alterTable($tableName, ['add' => [$columnName => $options]], $check);
    }

    /**
     * Drop column from table.
     *
     * @param string  $tableName  Table name
     * @param string  $columnName Column name
     * @param boolean $check      Verifies request only, default: false
     *
     * @return void
     */
    public static function dropColumn($tableName, $columnName, $check = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->alterTable($tableName, ['remove' => [$columnName => []]], $check);
    }

    /**
     *  Rename column in table.
     *
     * @param string  $tableName     Table name
     * @param string  $oldColumnName Old column name
     * @param string  $newColumnName New column name
     * @param boolean $check         Verifies request only, default: false
     *
     * @return void
     */
    public static function renameColumn($tableName, $oldColumnName, $newColumnName, $check = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        $columnList = Doctrine_Manager::connection()->import->listTableColumns($tableName);
        if (isset($columnList[$oldColumnName])) {
            $coldef = $columnList[$oldColumnName];

            if ('string' == $coldef['type'] && in_array('clob', $coldef['alltypes'])) {
                // fix detection for TEXT fields
                $coldef['type'] = 'clob';
                $coldef['length'] = 65532;
            } elseif ('integer' == $coldef['type'] && in_array('boolean', $coldef['alltypes'])) {
                // fix detection for BOOLEAN fields
                $coldef['type'] = 'boolean';
                $coldef['length'] = null;
            }

            Doctrine_Manager::connection()->export->alterTable($tableName,
                ['rename' => [$oldColumnName => ['name' => $newColumnName, 'definition' => $coldef]]], $check);
        }
    }

    /**
     * Modify a column.
     *
     * @param string  $tableName  Table name
     * @param string  $columnName Column name
     * @param array   $options    Column options
     * @param boolean $check      Verifies request only, default: false
     *
     * @return void
     */
    public static function alterColumn($tableName, $columnName, $column = [], $check = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $options = [];
        $options = $column['options'];
        $options['type'] = $column['type'];
        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->alterTable($tableName, ['change' => [$columnName => ['definition' => $options]]], $check);
    }

    /**
     * Create index.
     *
     * @param string $tableName  Table name
     * @param string $index      Index name
     * @param array  $definition Definition
     *
     * @return void
     */
    public static function createIndex($tableName, $index, array $definition)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->createIndex($tableName, $indexName, $definition);
    }

    /**
     * Drop index.
     *
     * @param string $tableName Table name
     * @param string $indexName Index name
     *
     * @return void
     */
    public static function dropIndex($tableName, $indexName)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->dropIndex($tableName, $indexName);
    }

    /**
     * Create constraint.
     *
     * @param string $tableName      Table name
     * @param string $constraintName Constraint name
     * @param array  $definition     Definition
     *
     * @return void
     */
    public static function createConstraint($tableName, $constraintName, array $definition)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->createConstraint($tableName, $constraintName, $definition);
    }

    /**
     * Drop constraint.
     *
     * @param string $tableName      Table name
     * @param string $constraintName Constraint name
     * @param array  $definition     Definition
     *
     * @return void
     */
    public static function dropConstraint($tableName, $constraintName, array $definition)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->dropConstraint($tableName, $constraintName, isset($definition['primary']) && $definition['primary']);
    }

    /**
     * Create foreign key.
     *
     * @param string $tableName  Table name
     * @param array  $definition Definition
     *
     * @return void
     */
    public static function createForeignKey($tableName, array $definition)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->createForeignKey($tableName, $definition);
    }

    /**
     * Drop Foreign Key.
     *
     * @param string $tableName  Table name
     * @param array  $definition Definition
     *
     * @return void
     */
    public static function dropForeignKey($tableName, array $definition)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $tableName = self::decorateTableName($tableName);
        Doctrine_Manager::connection()->export->dropForeignKey($tableName, $definition['name']);
    }

    /**
     * Change database table using Doctrine dictionary method.
     *
     * Please note this method does not handle column renaming.  Renames should
     * be handled by first calling this method with $dropColums = false so that data
     * can then be copied to the new columns, before calling the method again with
     * $dropColumns = true to cleanup the old columns.
     *
     * @param string  $className   Class name
     * @param boolean $dropColumns Drops unused columns (default=false)
     *
     * @throws InvalidArgumentException If $className does not exist
     *
     * @return boolean
     */
    public static function changeTable($className, $dropColumns = false)
    {
        @trigger_error('DoctrineUtil is deprecated. please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $connection = Doctrine_Manager::connection();

        if (!class_exists($className)) {
            throw new InvalidArgumentException(__f('Class %s cannot be found.', $className));
        }

        $reflection = new ReflectionClass($className);
        $model = $reflection->newInstance();
        $modelTable = $model->getTable();
        $modelColumns = $modelTable->getColumns();
        $tableName = $modelTable->getTableName();

        $schemaColumns = $connection->import->listTableColumns($tableName);

        // first round - create any missing columns
        foreach ($modelColumns as $key => $columnDefinition) {
            if (isset($schemaColumns[$key])) {
                continue;
            }
            $alterTableDefinition = ['add' => [$key => $columnDefinition]];
            try {
                $connection->export->alterTable($tableName, $alterTableDefinition);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // second round - alter table structures to match new tables definition.
        foreach ($modelColumns as $key => $columnDefinition) {
            $alterTableDefinition = ['change' => [$key => ['definition' => $columnDefinition]]];
            try {
                $connection->export->alterTable($tableName, $alterTableDefinition);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // third round - removes non existing columns in the model.
        if ($dropColumns) {
            foreach (array_keys($schemaColumns) as $key) {
                if (isset($modelColumns[$key])) {
                    continue;
                }
                $alterTableDefinition = ['remove' => [$key => []]];
                try {
                    $connection->export->alterTable($tableName, $alterTableDefinition);
                } catch (Exception $e) {
                    return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
                }
            }
        }

        // drop all indexes
        $schemaIndexes = $connection->import->listTableIndexes($tableName);
        foreach ($schemaIndexes as $index) {
            try {
                $connection->export->dropIndex($tableName, $index);
            } catch (Exception $e) {
                return LogUtil::registerError(__('Error! Table update failed.') . ' ' . $e->getMessage());
            }
        }

        // create additional indexes
        $modelIndexes = $modelTable->getOption('indexes');
        if ($modelIndexes) {
            foreach ($modelIndexes as $indexName => $indexDefinition) {
                $connection->export->createIndex($tableName, $indexName, $indexDefinition); //['fields' => $indexDefinition]);
            }
        }

        return true;
    }
}
