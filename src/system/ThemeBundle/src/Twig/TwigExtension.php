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

namespace Zikula\ThemeBundle\Twig;

use Twig\Attribute\AsTwigFunction;
use Twig\Environment;
use Zikula\CoreBundle\Site\SiteDefinitionInterface;

class TwigExtension
{
    public function __construct(
        private readonly Environment $twig,
        private readonly SiteDefinitionInterface $site
    ) {
    }

    /**
     * Returns site definition.
     */
    #[AsTwigFunction('siteDefinition')]
    public function getSiteDefinition(): SiteDefinitionInterface
    {
        return $this->site;
    }

    /**
     * Returns site name.
     */
    #[AsTwigFunction('siteName')]
    public function getSiteName(): string
    {
        return $this->site->getName();
    }

    /**
     * Returns site slogan.
     */
    #[AsTwigFunction('siteSlogan')]
    public function getSiteSlogan(): string
    {
        return $this->site->getSlogan();
    }

    /**
     * Returns site branding markup.
     */
    #[AsTwigFunction('siteBranding', isSafe: ['html'])]
    public function getSiteBrandingMarkup(): string
    {
        return $this->twig->render('@ZikulaTheme/Branding/manifest.html.twig');
    }

    /**
     * Returns site image path.
     */
    #[AsTwigFunction('siteImagePath')]
    public function getSiteImagePath(string $imageType = ''): string
    {
        if (!in_array($imageType, ['logo', 'mobileLogo', 'icon'], true)) {
            $imageType = 'logo';
        }

        $accessor = 'get' . ucfirst($imageType) . 'Path';

        return $this->site->{$accessor}();
    }
}
