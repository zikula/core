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

namespace Zikula\SecurityCenterModule\Event;

/**
 * Occurs when a string is passed to HtmlFilterApi and filtered.
 */
class FilterHtmlEvent
{
    /**
     * @var string
     */
    private $htmlContent;

    public function __construct(?string $htmlContent = '')
    {
        $this->htmlContent = $htmlContent;
    }

    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    public function setHtmlContent(string $htmlContent): void
    {
        $this->htmlContent = $htmlContent;
    }
}
