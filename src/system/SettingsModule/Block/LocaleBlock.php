<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Block;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Block to display locale chooser
 */
class LocaleBlock extends AbstractBlockHandler
{
    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
    {
        if ((!$this->hasPermission('LocaleBlock::', "::", ACCESS_OVERVIEW))
        || (!$this->hasPermission('LocaleBlock::bid', "::$properties[bid]", ACCESS_OVERVIEW))) {
            return '';
        }
        $locales = $this->get('zikula_settings_module.locale_api')->getSupportedLocaleNames();
        $localeLinks = [];
        /** @var Request $request */
        $request = $this->get('request_stack')->getMasterRequest();
        try {
            $routeInfo = $this->get('router')->match($request->getPathInfo());
        } catch (\Exception $e) {
            return '';
        }
        $selectedRoute = false;
        foreach ($locales as $displayName => $code) {
            if ($request->getLocale() == $code) {
                $url = $request->getPathInfo();
                $selectedRoute = $url;
            } else {
                $url = $this->get('router')->generate($routeInfo['_route'], $this->filterRouteInfo($routeInfo, $code), UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $localeLinks[$displayName] = $url;
        }
        $form = $this->get('form.factory')->create()->add('locale', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'choices' => $localeLinks,
            'choices_as_values' => true,
            'data' => $selectedRoute,
            'attr' => ['class' => 'locale-switcher-block']
        ]);

        return $this->renderView('@ZikulaSettingsModule/Block/localeBlock.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function filterRouteInfo(array $routeInfo, $locale)
    {
        $params = [];
        foreach ($routeInfo as $param => $value) {
            if (0 !== strpos($param, '_')) {
                $params[$param] = $value;
            }
        }
        $params['_locale'] = $locale;

        return $params;
    }
}
