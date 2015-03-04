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

class CoreInstallerValidator
{
    /**
     * Validate provided database credentials by attempting to establish connection with the database.
     *
     * @param $object
     * @param ExecutionContextInterface $context
     */
    public static function validatePdoConnection($object, ExecutionContextInterface $context)
    {
        try {
            $dbh = new \PDO("$object[database_driver]:host=$object[database_host];dbname=$object[database_name]", $object['database_user'], $object['database_password']);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = ($object['database_driver'] == 'mysql' || $object['database_driver'] == 'mysqli') ?
                "SHOW TABLES FROM `$object[database_name]` LIKE '%'" :
                "SHOW TABLES FROM $object[database_name] LIKE '%'";
            $tables = $dbh->query($sql);
            if (!is_object($tables)) {
                $context->buildViolation(__('Error! Determination existing tables failed.') . ' SQL: ' . $sql)
                        ->addViolation();
            } elseif ($tables->rowCount() > 0) {
                $context->buildViolation(__('Error! The database exists and contains tables. Please delete all tables before proceeding or select a new database.'))
                        ->addViolation();
            }
        } catch (\PDOException $eb) {
            $context->buildViolation(__('Error! Could not connect to the database. Please check that you have entered the correct database information and try again. ' . $eb->getMessage()))
                    ->addViolation();
        }
    }

    /**
     * Validate provided login credentials and login
     *
     * @param $object
     * @param ExecutionContextInterface $context
     */
    public static function validateAndLogin($object, ExecutionContextInterface $context)
    {
        $authenticationInfo = array(
            'login_id' => $object['username'],
            'pass'     => $object['password']
        );
        $authenticationMethod = array(
            'modname' => 'ZikulaUsersModule',
            'method'  => 'uname',
        );
        try {
            $loginResult = \UserUtil::loginUsing($authenticationMethod, $authenticationInfo);
            if (!$loginResult) {
                $context->buildViolation(__('Error! Could not login with provided credentials. Please try again.'))
                    ->addViolation()
                ;
            }
            if (is_array($loginResult)) {
                $granted = \SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN, $loginResult['uid']);
                if (!$granted) {
                    $context->buildViolation(__('Error! You logged in to an account without Admin permissions'))
                        ->addViolation();
                }
            }
        } catch (\Exception $e) {
            $context->buildViolation(__('Error! There was a problem logging in.'))
                ->addViolation()
            ;
        }

    }
}
