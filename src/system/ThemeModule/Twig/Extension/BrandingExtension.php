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

namespace Zikula\ThemeModule\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeModule\Engine\Asset;

class BrandingExtension extends AbstractExtension
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var SiteDefinitionInterface
     */
    private $site;

    /**
     * @var Asset
     */
    private $assetHelper;

    public function __construct(
        Environment $twig,
        SiteDefinitionInterface $site,
        Asset $assetHelper
    ) {
        $this->twig = $twig;
        $this->site = $site;
        $this->assetHelper = $assetHelper;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('siteName', [$this, 'getSiteName']),
            new TwigFunction('siteSlogan', [$this, 'getSiteSlogan']),
            new TwigFunction('siteBranding', [$this, 'getSiteBrandingMarkup'], ['is_safe' => ['html']]),
            new TwigFunction('siteImagePath', [$this, 'getSiteImagePath'])
        ];
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
