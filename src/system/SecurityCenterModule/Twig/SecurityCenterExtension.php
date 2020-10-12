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

namespace Zikula\SecurityCenterModule\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SecurityCenterExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('safeHtml', [$this, 'safeHtml'], ['is_safe' => ['html']])
        ];
    }
}
