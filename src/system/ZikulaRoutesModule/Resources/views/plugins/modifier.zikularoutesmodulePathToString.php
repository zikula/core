<?php

function smarty_modifier_zikularoutesmodulePathToString($path, \Zikula\RoutesModule\Entity\RouteEntity $route)
{
    $options = $route->getOptions();

    $prefix = '';
    if (isset($options['i18n_prefix'])) {
        $prefix = '/' . $options['i18n_prefix'];
    }
    if (!isset($options['i18n']) || $options['i18n']) {
        $languages = ZLanguage::getInstalledLanguages();
        $isRequiredLangParam = ZLanguage::isRequiredLangParam();
        if (!$isRequiredLangParam) {
            $defaultLanguage = System::getVar('language_i18n');
            unset($languages[array_search($defaultLanguage, $languages)]);
        }
        if (count($languages) > 0) {
            $prefix = ($isRequiredLangParam ? "/{" : "{/") . implode('|', $languages) . "}";
        }
    }

    $prefix = \DataUtil::formatForDisplay($prefix);
    $path = \DataUtil::formatForDisplay($route->getPathWithBundlePrefix());
    $container = \ServiceUtil::getManager();

    $path = preg_replace_callback('#%(.*?)%#', function ($matches) use ($container) {
        return "<abbr title=\"" . \DataUtil::formatForDisplay($matches[0]) . "\">" . \DataUtil::formatForDisplay($container->getParameter($matches[1])) . "</abbr>";
    }, $path);

    return "$prefix<strong>$path</strong>";
}
