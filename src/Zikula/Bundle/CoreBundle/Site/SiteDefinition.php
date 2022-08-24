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

namespace Zikula\Bundle\CoreBundle\Site;

use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\ThemeBundle\Engine\ParameterBag;

class SiteDefinition implements SiteDefinitionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly array $siteData,
        private readonly string $defaultLocale,
        private readonly ParameterBag $pageVars
    ) {
    }

    public function getName(): string
    {
        return $this->getLocalizedField('sitename');
    }

    public function getSlogan(): string
    {
        return $this->getLocalizedField('slogan');
    }

    public function getPageTitle(): string
    {
        $titleScheme = $this->getLocalizedField('page_title');
        $title = $this->pageVars->get('title', $titleScheme);
        if (!empty($titleScheme)) {
            $title = str_replace(['#pagetitle#', '#sitename#'], [$title, $this->getName()], $titleScheme);
        }

        return $title;
    }

    public function getMetaDescription(): string
    {
        return $this->getLocalizedField('meta_description');
    }

    public function getLogoPath(): ?string
    {
        return '@CoreBundle:images/logo_with_title.png';
    }

    public function getMobileLogoPath(): ?string
    {
        return '@CoreBundle:images/zk-power.png';
    }

    public function getIconPath(): ?string
    {
        return '@CoreBundle:images/icon.png';
    }

    public function getStartController(): ?array
    {
        return $this->getLocalizedField('start_controller');
    }

    public function getAdminMail(): ?string
    {
        return $this->getLocalizedField('admin_mail');
    }

    private function getLocale(): string
    {
        return $this->requestStack->getCurrentRequest()->getLocale();
    }

    private function getLocalizedField(string $fieldName): string|array
    {
        $locale = $this->getLocale();
        if (array_key_exists($locale, $this->siteData) && isset($this->siteData[$locale][$fieldName])) {
            return $this->siteData[$locale][$fieldName];
        }
        if (array_key_exists($this->defaultLocale, $this->siteData) && isset($this->siteData[$this->defaultLocale][$fieldName])) {
            return $this->siteData[$this->defaultLocale][$fieldName];
        }

        $firstSiteData = array_values($this->siteData)[0];

        return $firstSiteData[$fieldName] ?? '';
    }
}
