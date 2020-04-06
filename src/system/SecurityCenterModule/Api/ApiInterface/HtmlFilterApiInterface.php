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

namespace Zikula\SecurityCenterModule\Api\ApiInterface;

interface HtmlFilterApiInterface
{
    public const TAG_NOT_ALLOWED = 0;

    public const TAG_ALLOWED_PLAIN = 1;

    public const TAG_ALLOWED_WITH_ATTRIBUTES = 2;

    /**
     * Filter an html string (or array of strings) and remove disallowed tags
     *
     * @param string|array $value
     * @return string|array
     */
    public function filter($value);
}
