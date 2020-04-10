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

namespace Zikula\Bundle\CoreBundle;

use InvalidArgumentException;

/**
 * RouteUrl class.
 */
class RouteUrl implements UrlInterface
{
    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $args;

    /**
     * @var string
     */
    private $fragment;

    public function __construct(string $route, array $args = [], string $fragment = null)
    {
        $this->route = $route;
        $this->args = $args;
        $this->fragment = $fragment;
    }

    public function getLanguage(): ?string
    {
        return $this->args['_locale'] ?? null;
    }

    public function setLanguage(string $lang): void
    {
        $this->args['_locale'] = $lang;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Factory method to create instance and set Route simultaneously.
     *
     * @throws InvalidArgumentException
     */
    public static function createFromRoute(string $route, array $args = [], string $fragment = ''): self
    {
        if (empty($route)) {
            throw new InvalidArgumentException('No route given in RouteUrl.');
        }

        return new self($route, $args, $fragment);
    }

    public function toArray(): array
    {
        return [
            'route' => $this->route,
            'args' => $this->args,
            'fragment' => $this->fragment
        ];
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}
