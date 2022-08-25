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

namespace Zikula\ThemeBundle\Engine\Annotation;

use Attribute;

/**
 * This attribute is used in a controller method like so: #[Theme('admin')]
 * Possible values are:
 *  - 'admin'
 *  - any valid theme bundle name (e.g. 'ZikulaDefaultThemeBundle')
 * @see \Zikula\ThemeBundle\Engine\Engine::changeThemeByAnnotation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Theme
{
    public function __construct(public string $value)
    {
    }
}
