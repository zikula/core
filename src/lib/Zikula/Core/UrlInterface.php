<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core;

/**
 * UrlInterface class.
 */
interface UrlInterface
{
    public function getLanguage();

    public function getFragment();

    public function getArgs();

    public function serialize();

    public function toArray();
}
