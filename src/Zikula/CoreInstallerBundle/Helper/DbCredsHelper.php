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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;

class DbCredsHelper
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function writeDatabaseDsn(array $data = []): bool
    {
        if (!isset($data['database_driver'], $data['database_host'], $data['database_user'], $data['database_name'])) {
            throw new \InvalidArgumentException('Database connection credentials must be set');
        }

        $vars = [
            'DATABASE_USER' => $data['database_user'],
            'DATABASE_PWD' => $data['database_password'],
            'DATABASE_NAME' => $data['database_name'],
            'DATABASE_URL' => '!' . $data['database_driver']
                . '://$DATABASE_USER:$DATABASE_PWD'
                . '@' . $data['database_host'] . (!empty($data['database_port']) ? ':' . $data['database_port'] : '')
                . '/$DATABASE_NAME?serverVersion=' . ($data['database_server_version'] ?? '5.7') // any value for serverVersion will work (bypasses DBALException)
        ];

        try {
            (new LocalDotEnvHelper($this->projectDir))->writeLocalEnvVars($vars);
        } catch (IOExceptionInterface $exception) {
            return false;
        }

        return true;
    }
}
