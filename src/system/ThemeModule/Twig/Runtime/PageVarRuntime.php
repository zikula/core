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

namespace Zikula\ThemeModule\Twig\Runtime;

use InvalidArgumentException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

class PageVarRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ParameterBag $pageVars
    ) {
    }

    /**
     * Zikula imposes no restriction on page variable names.
     * Typical usage is to set `title` `meta.charset` `lang` etc.
     * array values are set using `.` in the `$name` string (e.g. `meta.charset`)
     */
    public function pageSetVar(string $name, string $value): void
    {
        if (empty($name) || empty($value)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $this->pageVars->set($name, $value);
    }

    public function pageGetVar(string $name, string $default = '')
    {
        if (empty($name)) {
            throw new InvalidArgumentException($this->translator->trans('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->pageVars->get($name, $default);
    }
}
