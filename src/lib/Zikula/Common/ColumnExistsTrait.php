<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common;

use Doctrine\DBAL\Connection;

trait ColumnExistsTrait
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

    private function columnExists(string $tableName, string $columnName): bool
    {
        $sm = $this->conn->getSchemaManager();
        $existingColumns = $sm->listTableColumns($tableName);
        foreach ($existingColumns as $existingColumn) {
            if ($existingColumn->getName() === $columnName) {
                return true;
            }
        }

        return false;
    }
}
