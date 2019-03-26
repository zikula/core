<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Block;

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

    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered block
     */
    public function display(array $properties)
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
        } catch (\Exception $exception) {
            return '';
        }
        $selectedRoute = false;
        foreach ($locales as $displayName => $code) {
            if ($request->getLocale() === $code) {
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

    private function filterRouteInfo(array $routeInfo, $locale)
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
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @required
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     * @param LocaleApiInterface $localeApi
     */
    public function setLocaleApi(LocaleApiInterface $localeApi)
    {
        $this->localeApi = $localeApi;
    }
}
