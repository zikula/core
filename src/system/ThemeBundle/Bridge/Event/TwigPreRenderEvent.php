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

class TwigPreRenderEvent
{
    public function __construct(protected string $templateName, protected array $parameters = [])
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

    public function setTemplateName(string $name): void
    {
        $this->templateName = $name;
    }

    public function setParameters(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }
}
