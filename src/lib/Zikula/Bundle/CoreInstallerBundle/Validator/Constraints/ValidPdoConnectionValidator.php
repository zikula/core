<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidPdoConnectionValidator extends ConstraintValidator
{
    public function validate($object, Constraint $constraint)
    {
        if ($object['database_host'] == '' || $object['database_name'] == '' || $object['database_user'] == '') {
            $this->context->buildViolation(__('Error! Please enter your database credentials.'))
                ->addViolation();

            return;
        }

        try {
            $dbh = new \PDO("$object[database_driver]:host=$object[database_host];dbname=$object[database_name]", $object['database_user'], $object['database_password']);
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $sql = ($object['database_driver'] == 'mysql' || $object['database_driver'] == 'mysqli') ?
                "SHOW TABLES FROM `$object[database_name]` LIKE '%'" :
                "SHOW TABLES FROM $object[database_name] LIKE '%'";
            $tables = $dbh->query($sql);
            if (!is_object($tables)) {
                $this->context->buildViolation(__('Error! Determination existing tables failed.') . ' SQL: ' . $sql)
                    ->addViolation();
            } elseif ($tables->rowCount() > 0) {
                $this->context->buildViolation(__('Error! The database exists and contains tables. Please delete all tables before proceeding or select a new database.'))
                    ->addViolation();
            }
        } catch (\PDOException $eb) {
            $this->context->buildViolation(__('Error! Could not connect to the database. Please check that you have entered the correct database information and try again. ' . $eb->getMessage()))
                ->addViolation();
        }
    }
}
