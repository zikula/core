<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine\Asset;


interface MergerInterface
{
    /**
     * Merge the assets and publish.
     * @param \Traversable $assets
     * @param string $type
     * @return $this
     */
    public function merge(array $assets, $type = 'js');
}
