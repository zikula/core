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

namespace Zikula\CoreBundle\Site;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class SiteDefinition implements SiteDefinitionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly array $siteData,
        #[Autowire(param: 'kernel.default_locale')]
        private readonly string $defaultLocale
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
        $title = $this->getName();
        $titleScheme = $this->getLocalizedField('page_title_scheme');
        if (!empty($titleScheme)) {
            $title = str_replace('#sitename#', $this->getName(), $titleScheme);
        }

        return $title;
    }

    public function getMetaDescription(): string
    {
        return $this->getLocalizedField('meta_description');
    }

    public function getLogoPath(): ?string
    {
        return '/bundles/core/images/logo_with_title.png';
    }

    public function getMobileLogoPath(): ?string
    {
        return '/bundles/core/images/zk-power.png';
    }

    public function getIconPath(): ?string
    {
        return '/bundles/core/images/icon.png';
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
