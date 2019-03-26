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

namespace Zikula\ThemeModule\Engine\Asset;

interface MergerInterface
{
    /**
     * Merge the assets and publish.
     * @param array $assets
     * @param string $type
     * @return array List of output files
     */
    public function merge(array $assets, $type = 'js');
}
