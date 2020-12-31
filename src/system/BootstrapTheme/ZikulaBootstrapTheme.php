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

namespace Zikula\BootstrapTheme;

use Zikula\ExtensionsModule\AbstractCoreTheme;

trigger_deprecation('zikula/bootstrap-theme', '3.1', 'The "%s" class is deprecated. Use "%s" instead.', ZikulaBootstrapTheme::class, 'ZikulaDefaultTheme');

/**
 * @deprecated remove at Core-4.0.0
 * @see \Zikula\DefaultTheme\ZikulaDefaultTheme
 */
class ZikulaBootstrapTheme extends AbstractCoreTheme
{
}
