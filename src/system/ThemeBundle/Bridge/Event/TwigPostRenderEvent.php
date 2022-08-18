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

namespace Zikula\ThemeBundle\Bridge\Event;

class TwigPostRenderEvent
{
    public function __construct(protected string $content, protected string $templateName, protected array $parameters = [])
    {
    }

    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
