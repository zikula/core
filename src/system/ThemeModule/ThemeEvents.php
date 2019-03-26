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

namespace Zikula\ThemeModule;

class ThemeEvents
{
    /**
     * Occurs immediately before twig theme engine renders a template.
     * subject is \Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent
     */
    const PRE_RENDER = 'theme.pre_render';

    /**
     * Occurs immediately after twig theme engine renders a template.
     * subject is \Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent
     */
    const POST_RENDER = 'theme.post_render';
}
