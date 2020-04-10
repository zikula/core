<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFilter;
use Zikula\RoutesModule\Entity\RouteEntity;
use Zikula\RoutesModule\Helper\PathBuilderHelper;
use Zikula\RoutesModule\Twig\Base\AbstractTwigExtension;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

/**
 * Twig extension implementation class.
 */
class TwigExtension extends AbstractTwigExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var PathBuilderHelper
     */
    private $pathBuilderHelper;

    public function getFunctions()
    {
        return [];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('zikularoutesmodule_listEntry', [$this, 'getListEntry']), // from base class
            new TwigFilter('zikularoutesmodule_formattedTitle', [$this, 'getFormattedEntityTitle']), // from base class
            new TwigFilter('zikularoutesmodule_arrayToString', [$this, 'displayArrayAsString'], ['is_safe' => ['html']]),
            new TwigFilter('zikularoutesmodule_pathToString', [$this, 'displayPathAsString'], ['is_safe' => ['html']])
        ];
    }

    /**
     * The zikularoutesmodule_arrayToString filter displays the content of a given array.
     * Example:
     *    {{ route.defaults|zikularoutesmodule_arrayToString }}
     */
    public function displayArrayAsString(array $input = []): string
    {
        return '<pre>' . print_r($input, true) . '</pre>';
    }

    /**
     * The zikularoutesmodule_pathToString filter displays a route's path.
     * Example:
     *    {{ route.path|zikularoutesmodule_pathToString(route) }}
     */
    public function displayPathAsString(string $path, RouteEntity $route): string
    {
        $prefix = '';
        $translationPrefix = $route->getTranslationPrefix();
        if (!empty($translationPrefix)) {
            $prefix = '/' . $translationPrefix;
        }

        if ($route->getTranslatable()) {
            $languages = $this->localeApi->getSupportedLocales();
            $isRequiredLangParameter = $this->variableApi->getSystemVar('languageurl', 0);
            if (!$isRequiredLangParameter) {
                $defaultLanguage = $this->variableApi->getSystemVar('locale');
                unset($languages[array_search($defaultLanguage, $languages, true)]);
            }
            if (count($languages) > 0) {
                $prefix = ($isRequiredLangParameter ? '/' : '{/') . implode('|', $languages) . ($isRequiredLangParameter ? '' : '}');
            }
        }

        $prefix = htmlspecialchars($prefix);
        $path = htmlspecialchars(
            $this->pathBuilderHelper->getPathWithBundlePrefix($route)
        );

        $container = $this->container;
        $path = preg_replace_callback('#%(.*?)%#', static function ($matches) use ($container) {
            return '<abbr title="' . htmlspecialchars($matches[0]) . '">'
                . htmlspecialchars($container->getParameter($matches[1]))
                . '</abbr>'
            ;
        }, $path);

        $defaults = $route->getDefaults();
        $requirements = $route->getRequirements();
        $path = preg_replace_callback('#{(.*?)}#', function ($matches) use ($defaults, $requirements) {
            $title = '';
            if (isset($defaults[$matches[1]])) {
                $title .= $this->trans('Default: %value%', ['%value%' => htmlspecialchars($defaults[$matches[1]])]);
            }
            if (isset($requirements[$matches[1]])) {
                if ('' !== $title) {
                    $title .= ' | ';
                }
                $title .= $this->trans('Requirement: %value%', ['%value%' => htmlspecialchars($requirements[$matches[1]])]);
            }
            if ('' === $title) {
                return $matches[0];
            }

            return '<abbr title="' . $title . '">' . $matches[0] . '</abbr>';
        }, $path);

        return $prefix . '<strong>' . $path . '</strong>';
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        ContainerInterface $container,
        LocaleApiInterface $localeApi,
        PathBuilderHelper $pathBuilderHelper
    ): void {
        $this->container = $container;
        $this->localeApi = $localeApi;
        $this->pathBuilderHelper = $pathBuilderHelper;
    }
}
