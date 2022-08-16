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

use Twig\Extension\RuntimeExtensionInterface;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

class SecurityCenterRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly HtmlFilterApiInterface $htmlFilterApi)
    {
    }

    public function safeHtml(string $string): string
    {
        return $this->htmlFilterApi->filter($string);
    }
}
