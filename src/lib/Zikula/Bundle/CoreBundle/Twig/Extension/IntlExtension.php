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

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Timezones;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IntlExtension extends AbstractExtension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('countryName', [$this, 'countryName']),
            new TwigFilter('currencyName', [$this, 'currencyName']),
            new TwigFilter('languageName', [$this, 'languageName']),
            new TwigFilter('localeName', [$this, 'localeName']),
            new TwigFilter('timezoneName', [$this, 'timezoneName'])
        ];
    }

    public function countryName(string $code, string $displayLocale = null): string
    {
        return Countries::getName($code, $this->getDisplayLocale($displayLocale));
    }

    public function currencyName(string $code, string $displayLocale = null): string
    {
        return Currencies::getName($code, $this->getDisplayLocale($displayLocale));
    }

    public function languageName(string $code, string $displayLocale = null): string
    {
        return Languages::getName($code, $this->getDisplayLocale($displayLocale));
    }

    public function localeName(string $code, string $displayLocale = null): string
    {
        return Locales::getName($code, $this->getDisplayLocale($displayLocale));
    }

    public function timezoneName(string $code, string $displayLocale = null): string
    {
        return Timezones::getName($code, $this->getDisplayLocale($displayLocale));
    }

    private function getDisplayLocale(string $displayLocale = null): string
    {
        return $displayLocale ?? $this->requestStack->getCurrentRequest()->getLocale();
    }
}
