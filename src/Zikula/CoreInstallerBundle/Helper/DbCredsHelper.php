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

class DbCredsHelper
{
    public function buildDatabaseUrl(array $data = [])
    {
        $databaseUrl = $data['database_driver']
            . '://' . $data['database_user'] . ':' . $data['database_password']
            . '@' . $data['database_host'] . (!empty($data['database_port']) ? ':' . $data['database_port'] : '')
            . '/' . $data['database_name']
        ;

        $databaseUrl .= '?charset=UTF8';
        $databaseUrl .= '&serverVersion=5.7'; // any value will work (bypasses DBALException)

        return $databaseUrl;
    }
}
