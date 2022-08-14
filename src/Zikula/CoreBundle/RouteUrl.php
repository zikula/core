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

class RouteUrl implements UrlInterface
{
    public function __construct(private readonly string $route, private readonly array $args = [], private readonly string $fragment = null)
    {
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
