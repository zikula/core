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

namespace Zikula\AdminModule\Helper;

/**
 * Helper function to sort modules.
 */
class AdminLinksHelper
{
    public function sortAdminModsByOrder(iterable $adminLinks = []): iterable
    {
        usort($adminLinks, function (array $a, array $b) {
            if ((int)$a['order'] === (int)$b['order']) {
                return strcmp($a['moduleName'], $b['moduleName']);
            }
            if ((int)$a['order'] > (int)$b['order']) {
                return 1;
            }
            if ((int)$a['order'] < (int)$b['order']) {
                return -1;
            }

            return 0;
        });

        return $adminLinks;
    }
}
