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

namespace Zikula\SettingsModule\Twig;

use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class LocaleRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly PermissionApiInterface $permissionApi,
        private readonly LocaleApiInterface $localeApi
    ) {
    }

    public function renderLocaleSwitcher(): string
    {
        if (!$this->permissionApi->hasPermission('LocaleSwitcher::', '::', ACCESS_OVERVIEW)) {
            return '';
        }

        $locales = $this->localeApi->getSupportedLocaleNames();
        $localeLinks = [];
        /** @var Request $request */
        $request = $this->requestStack->getMainRequest();
        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (Exception) {
            return '';
        }
        $locale = $request->getLocale();
        $selectedRoute = false;
        foreach ($locales as $displayName => $code) {
            if ($locale === $code) {
                $url = $request->getPathInfo();
                $selectedRoute = $url;
            } else {
                $url = $this->router->generate($routeInfo['_route'], $this->filterRouteInfo($routeInfo, $code), UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $localeLinks[$displayName] = $url;
        }
        if (2 > count($localeLinks)) {
            return '';
        }

        $form = $this->formFactory->create()->add('locale', ChoiceType::class, [
            'choices' => $localeLinks,
            'data' => $selectedRoute,
            'attr' => ['class' => 'locale-switcher-block']
        ]);

        return $this->twig->render('@ZikulaSettingsModule/Locale/localeSwitcher.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function filterRouteInfo(array $routeInfo, string $locale): array
    {
        $params = [];
        foreach ($routeInfo as $param => $value) {
            if (0 !== mb_strpos($param, '_')) {
                $params[$param] = $value;
            }
        }
        $params['_locale'] = $locale;

        return $params;
    }
}
