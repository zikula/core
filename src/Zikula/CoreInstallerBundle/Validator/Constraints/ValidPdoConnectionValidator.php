<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Validator\Constraints;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;

class ValidPdoConnectionValidator extends ConstraintValidator
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function validate($object, Constraint $constraint)
    {
        if ('' === $object['database_host'] || '' === $object['database_name'] || '' === $object['database_user']) {
            $this->context
                ->buildViolation($this->trans('Error! Please enter your database credentials.'))
                ->addViolation()
            ;

            return;
        }

        $connectionParams = [
            'url' => $this->buildDsn($object),
        ];

        try {
            $connection = DriverManager::getConnection($connectionParams, new Configuration());
            if (!$connection->connect()) {
                $this->context
                    ->buildViolation($this->trans('Error! Could not connect to the database. Please check that you have entered the correct database information and try again.'))
                    ->addViolation()
                ;
            } else {
                $tables = $connection->getSchemaManager()->listTableNames();
                if (0 < count($tables)) {
                    $this->context
                        ->buildViolation($this->trans('Error! The database exists and contains tables. Please delete all tables before proceeding or select a new database.'))
                        ->addViolation()
                    ;
                }
            }
        } catch (DBALException $exception) {
            $this->context
                ->buildViolation($this->trans('Error! Could not connect to the database. Please check that you have entered the correct database information and try again.') . ' ' . $exception->getMessage())
                ->addViolation()
            ;
        }
    }

    private function buildDsn($object): string
    {
        $dsn = $object['database_driver'] . '://' . $object['database_user'] . ':' . $object['database_password']
            . '@' . $object['database_host'] . (!empty($object['database_port']) ? ':' . $object['database_port'] : '')
                . '/' . $object['database_name']
                . '?serverVersion=' . ($object['database_server_version'] ?? '5.7') // any value for serverVersion will work (bypasses DBALException)
        ;

        return $dsn;
    }
}
