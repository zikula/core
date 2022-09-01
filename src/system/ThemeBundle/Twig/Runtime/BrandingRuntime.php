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

namespace Zikula\ThemeBundle\Twig\Runtime;

use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;

class BrandingRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly SiteDefinitionInterface $site
    ) {
    }

    /**
     * Returns site definition.
     */
    public function getSiteDefinition(): SiteDefinitionInterface
    {
        return $this->site;
    }

    /**
     * Returns site name.
     */
    public function getSiteName(): string
    {
        return $this->site->getName();
    }

    /**
     * Returns site slogan.
     */
    public function getSiteSlogan(): string
    {
        return $this->site->getSlogan();
    }

    /**
     * Returns site branding markup.
     */
    public function getSiteBrandingMarkup(): string
    {
        return $this->twig->render('@ZikulaTheme/Branding/manifest.html.twig');
    }

    /**
     * Returns site image path.
     */
    public function getSiteImagePath(string $imageType = ''): string
    {
        if (!in_array($imageType, ['logo', 'mobileLogo', 'icon'], true)) {
            $imageType = 'logo';
        }

        $accessor = 'get' . ucfirst($imageType) . 'Path';

        return $this->site->{$accessor}();
    }
}
