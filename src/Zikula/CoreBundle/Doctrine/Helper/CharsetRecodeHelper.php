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

namespace Zikula\Bundle\CoreBundle\Doctrine\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\ParameterType;

class CharsetRecodeHelper
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(
        Connection $connection
    ) {
        $this->conn = $connection;
    }

    private function isRequired(): bool
    {
        $driver = $this->conn->getDriver();

        // recoding utf8 to utf8mb4 is only required for mysql
        return $driver instanceof AbstractMySQLDriver;
    }

    public function getCommands(): array
    {
        if (!$this->isRequired()) {
            return [];
        }

        // the following is based on
        // https://dba.stackexchange.com/questions/8239/how-to-easily-convert-utf8-tables-to-utf8mb4-in-mysql-5-5#answer-104866
        $commands = [];

        $this->conn->executeQuery('use information_schema;');

        // database level
        $rows = $this->retrieveCommands('
            SELECT CONCAT("ALTER DATABASE `",table_schema,"` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;") AS _sql 
            FROM `TABLES`
            WHERE table_schema LIKE ?
            GROUP BY table_schema;
        ');
        $commands = array_merge($commands, $rows);

        // table level
        $rows = $this->retrieveCommands('
            SELECT CONCAT("ALTER TABLE `",table_schema,"`.`",table_name,"` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;") AS _sql  
            FROM `TABLES`
            WHERE table_schema LIKE ?
            GROUP BY table_schema, table_name;
        ');
        $commands = array_merge($commands, $rows);

        // column level
        $rows = $this->retrieveCommands('
            SELECT CONCAT("ALTER TABLE `",table_schema,"`.`",table_name, "` CHANGE `",column_name,"` `",column_name,"` ",data_type,"(",character_maximum_length,") CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",IF(is_nullable="YES"," NULL"," NOT NULL"),";") AS _sql 
            FROM `COLUMNS`
            WHERE table_schema LIKE ?
            AND data_type IN (\'varchar\', \'char\');
        ');
        $commands = array_merge($commands, $rows);

        $rows = $this->retrieveCommands('
            SELECT CONCAT("ALTER TABLE `",table_schema,"`.`",table_name, "` CHANGE `",column_name,"` `",column_name,"` ",data_type," CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",IF(is_nullable="YES"," NULL"," NOT NULL"),";") AS _sql 
            FROM `COLUMNS`
            WHERE table_schema LIKE ?
            AND data_type IN (\'text\', \'tinytext\', \'mediumtext\', \'longtext\');
        ');
        $commands = array_merge($commands, $rows);

        $this->conn->executeQuery('use ' . $this->conn->getDatabase() . ';');

        return $commands;
    }

    private function retrieveCommands(string $sql, string $dbName): array
    {
        $result = [];
        $stmt = $this->conn->executeQuery(
            $sql,
            [
                $this->conn->getDatabase()
            ],
            [
                ParameterType::STRING
            ]
        );
        while ($row = $stmt->fetch()) {
            $result[] = $row['_sql'];
        }

        return $result;
    }
}
