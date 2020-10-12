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

namespace Zikula\UsersModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\UsersModule\Twig\Runtime\ProfileRuntime;

class ProfileExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('userAvatar', [ProfileRuntime::class, 'getUserAvatar'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('profileLinkByUserId', [ProfileRuntime::class, 'profileLinkByUserId'], ['is_safe' => ['html']]),
            new TwigFilter('profileLinkByUserName', [ProfileRuntime::class, 'profileLinkByUserName'], ['is_safe' => ['html']])
        ];
    }
}
