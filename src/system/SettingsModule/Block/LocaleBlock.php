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

namespace Zikula\SettingsModule\Block;

use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

/**
 * Block to display locale chooser
 */
class LocaleBlock extends AbstractBlockHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('LocaleBlock::', '::', ACCESS_OVERVIEW)
        || (!$this->hasPermission('LocaleBlock::bid', '::' . $properties['bid'], ACCESS_OVERVIEW))) {
            return '';
        }
        $locales = $this->localeApi->getSupportedLocaleNames();
        $localeLinks = [];
        /** @var Request $request */
        $request = $this->requestStack->getMasterRequest();
        try {
            $routeInfo = $this->router->match($request->getPathInfo());
        } catch (Exception $exception) {
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
        $form = $this->formFactory->create()->add('locale', ChoiceType::class, [
            'choices' => $localeLinks,
            'data' => $selectedRoute,
            'attr' => ['class' => 'locale-switcher-block']
        ]);

        return $this->renderView('@ZikulaSettingsModule/Block/localeBlock.html.twig', [
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

    /**
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     */
    public function setLocaleApi(LocaleApiInterface $localeApi): void
    {
        $this->localeApi = $localeApi;
    }
}
