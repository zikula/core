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

namespace Zikula\Bundle\HookBundle\HookEvent;

/**
 * A FilterHookEvent id dispatched from either the controller or as a twig filter
 * to modfiy existing data in some way. The injected data is not restricted by
 * type and therefore could be a string, object, integer, etc.
 */
abstract class FilterHookEvent extends HookEvent
{
    private $data;

    public function __construct($data = null)
    {
        $this->setData($data);
    }

    public function getData()
    {
        return $this->data;
    }
}
