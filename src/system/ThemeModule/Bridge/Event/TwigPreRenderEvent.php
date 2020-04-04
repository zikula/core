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

namespace Zikula\ThemeModule\Bridge\Event;

class TwigPreRenderEvent
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var array
     */
    protected $parameters;

    public function __construct(string $name, array $parameters = [])
    {
        $this->templateName = $name;
        $this->parameters = $parameters;
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
