<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CategoriesModule\Builder;

/**
 * Class LegacyEntitySelectionBuilder
 * @deprecated remove at Core-2.0
 */
class LegacyEntitySelectionBuilder
{
    /**
     * @param $modname
     * @return array
     * @deprecated
     */
    public static function buildFor($modname)
    {
        // old style 'tables.php' modules (Core 1.2.x--)
        $tables = \ModUtil::dbInfoLoad($modname, '', true);
        $data = [];
        if (is_array($tables) && $tables) {
            foreach ($tables as $k => $v) {
                if (false === strpos($k, '_column') && false === strpos($k, '_db_extra_enable') && false === strpos($k, '_primary_key_column')) {
                    $checkColumns = $k . '_column';
                    if (!isset($tables[$checkColumns])) {
                        continue;
                    }
                }
                if (false === strpos($k, '_column') && false === strpos($k, '_db_extra_enable') && false === strpos($k, '_primary_key_column')) {
                    if (0 === strpos($k, 'z_')) {
                        $k = substr($k, 4);
                    }
                    $data[$k] = $k;
                }
            }
        }
        if (!empty($data)) {
            return $data;
        }

        // Doctrine 1 models (Core 1.3.0 - 1.3.5)
        \DoctrineUtil::loadModels($modname);
        $records = \Doctrine::getLoadedModels();
        $data = [];
        foreach ($records as $recordClass) {
            // remove records from other modules
            if (substr($recordClass, 0, strlen($modname)) != $modname) {
                continue;
            }

            // get table name of remove table prefix
            $tableNameRaw = \Doctrine::getTable($recordClass)->getTableName();
            sscanf($tableNameRaw, \Doctrine_Manager::getInstance()->getAttribute(\Doctrine::ATTR_TBLNAME_FORMAT), $tableName);
            $data[$tableName] = $tableName;
        }
        if (!empty($data)) {
            return $data;
        }

        // (Core-1.3 spec)
        $modinfo = \ModUtil::getInfo(\ModUtil::getIdFromName($modname));
        $osdir   = \DataUtil::formatForOS($modinfo['directory']);
        $entityDirs = [
            "modules/$osdir/Entity/", // Core 1.4.0++
            "modules/$osdir/lib/$osdir/Entity/", // Core 1.3.5--
        ];

        $entities = [];
        foreach ($entityDirs as $entityDir) {
            if (file_exists($entityDir)) {
                $files = scandir($entityDir);
                foreach ($files as $file) {
                    if ('.' != $file && '..' != $file && '.php' === substr($file, -4)) {
                        $entities[] = $file;
                    }
                }
            }
        }

        $data = [];
        foreach ($entities as $entity) {
            $possibleClassNames = [
                $modname . '_Entity_' . substr($entity, 0, strlen($entity) - 4), // Core 1.3.5--
            ];
            $module = \ModUtil::getModule($modname);
            if ($module) {
                $possibleClassNames[] = $module->getNamespace() . '\\Entity\\' . substr($entity, 0, strlen($entity) - 4); // Core 1.4.0++
            }
            foreach ($possibleClassNames as $class) {
                if (class_exists($class)) {
                    $entityName = substr($entity, 0, strlen($entity) - 4);
                    $data[$entityName] = $entityName;
                }
            }
        }

        return $data;
    }
}
