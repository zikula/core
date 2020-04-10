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

namespace Zikula\ThemeModule\Bridge\Event;

class TwigPostRenderEvent
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $content;

    public function __construct(string $content, string $name, array $parameters = [])
    {
        $this->content = $content;
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
