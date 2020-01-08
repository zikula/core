<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFilter;
use Zikula\RoutesModule\Entity\RouteEntity;
use Zikula\RoutesModule\Twig\Base\AbstractTwigExtension;
use Zikula\SettingsModule\Api\LocaleApi;

/**
 * Twig extension implementation class.
 */
class TwigExtension extends AbstractTwigExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

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

        $container = $this->container;

        if ($route->getTranslatable()) {
            $languages = $container->get(LocaleApi::class)->getSupportedLocales();
            // TODO migrate this legacy call
            $isRequiredLangParam = true;//ZLanguage::isRequiredLangParam();
            if (!$isRequiredLangParam) {
                $defaultLanguage = $this->variableApi->getSystemVar('language_i18n');
                unset($languages[array_search($defaultLanguage, $languages, true)]);
            }
            if (count($languages) > 0) {
                $prefix = ($isRequiredLangParam ? '/' : '{/') . implode('|', $languages) . ($isRequiredLangParam ? '' : '}');
            }
        }

        $prefix = htmlspecialchars($prefix);
        $path = htmlspecialchars(
            $container->get('zikula_routes_module.path_builder_helper')
                ->getPathWithBundlePrefix($route)
        );

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
                $title .= $this->trans('Default: %s', ['%s' => htmlspecialchars($defaults[$matches[1]])]);
            }
            if (isset($requirements[$matches[1]])) {
                if ('' !== $title) {
                    $title .= ' | ';
                }
                $title .= $this->trans('Requirement: %s', ['%s' => htmlspecialchars($requirements[$matches[1]])]);
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
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
