<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PdoConnectionValidator
{
    /**
     * Validate provided database credentials by attempting to establish connection with the database.
     *
     * @param $object
     * @param ExecutionContextInterface $context
     */
    public static function validate($object, ExecutionContextInterface $context)
    {
        try {
            $dbh = new \PDO("$object[database_driver]:host=$object[database_host];dbname=$object[database_name]", $object['database_user'], $object['database_password']);
            $sql = ($object['database_driver'] == 'mysql' || $object['database_driver'] == 'mysqli') ?
                "SHOW TABLES FROM `$object[database_name]` LIKE '%'" :
                "SHOW TABLES FROM $object[database_name] LIKE '%'";
            $tables = $dbh->query($sql);
            if ($tables->rowCount() > 0) {
                $context->buildViolation(__('Error! The database exists and contain tables. Please delete all tables before proceeding or select a new database.'))
                    ->addViolation()
                ;
            }
        } catch (\PDOException $eb) {
            $context->buildViolation(__('Error! Could not connect to the database. Please check that you have entered the correct database information and try again. ' . $eb->getMessage()))
                ->addViolation()
            ;
        }
    }
}