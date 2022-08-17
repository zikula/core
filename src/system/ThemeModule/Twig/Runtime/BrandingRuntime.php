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

use Exception;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeModule\Engine\Asset;

class BrandingRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly SiteDefinitionInterface $site,
        private readonly Asset $assetHelper
    ) {
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
        return $this->twig->render('@ZikulaThemeModule/Engine/manifest.html.twig');
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

        $assetPath = $this->site->{$accessor}();

        try {
            $imagePath = $this->assetHelper->resolve($assetPath);
        } catch (Exception $exception) {
            // fall back to default
            $assetPath = '@CoreBundle:images/';
            if ('logo' === $imageType) {
                $assetPath .= 'logo_with_title.png';
            } elseif ('mobileLogo' === $imageType) {
                $assetPath .= 'zk-power.png';
            } elseif ('icon' === $imageType) {
                $assetPath .= 'logo.gif';
            }

            $imagePath = $this->assetHelper->resolve($assetPath);
        }

        return $imagePath;
    }
}
