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

namespace Zikula\BlocksModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;
use Zikula\BlocksModule\Collectible\PendingContentCollectible;
use Zikula\BlocksModule\Twig\Runtime\BlocksRuntime;

class BlocksExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('showblockposition', [BlocksRuntime::class, 'showBlockPosition'], ['is_safe' => ['html']]),
            new TwigFunction('showblock', [BlocksRuntime::class, 'showBlock'], ['is_safe' => ['html']]),
            new TwigFunction('positionavailable', [BlocksRuntime::class, 'isPositionAvailable']),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('pendingContentCollectible', function ($obj) { return $obj instanceof PendingContentCollectible; }),
        ];
    }
}
