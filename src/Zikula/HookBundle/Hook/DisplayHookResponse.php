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

namespace Zikula\Bundle\HookBundle\Hook;

/**
 * @deprecated remove at Core 4.0.0
 *
 * Hook handlers should return one of these.
 */
class DisplayHookResponse
{
    /**
     * @var string The area name
     */
    protected $area;

    /**
     * @var string The response content
     */
    protected $content;

    public function __construct(string $area, string $content)
    {
        $this->content = $content;
        $this->area = $area;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
