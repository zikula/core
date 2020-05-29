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

namespace Zikula\Bundle\CoreBundle\Site;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\ThemeModule\Engine\ParameterBag;

class SiteDefinition implements SiteDefinitionInterface
{
    use TranslatorTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository,
        ParameterBag $pageVars
    ) {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->extensionRepository = $extensionRepository;
        $this->pageVars = $pageVars;
    }

    public function getName(): string
    {
        return $this->variableApi->getSystemVar('sitename', $this->variableApi->getSystemVar('sitename_en', ''));
    }

    public function getSlogan(): string
    {
        return $this->variableApi->getSystemVar('slogan', $this->variableApi->getSystemVar('slogan_en', ''));
    }

    public function getPageTitle(): string
    {
        $title = $this->pageVars->get('title', $this->variableApi->getSystemVar('defaultpagetitle', ''));
        $titleScheme = $this->variableApi->getSystemVar('pagetitle', '');
        if (!empty($titleScheme) && '%pagetitle%' !== $titleScheme) {
            $title = str_replace(
                ['%pagetitle%', '%sitename%'],
                [$title, $this->getName()],
                $titleScheme
            );

            $moduleDisplayName = '';
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && null !== $request->attributes->get('_controller')) {
                $controllerNameParts = explode('\\', $request->attributes->get('_controller'));
                $extensionName = count($controllerNameParts) > 1 ? $controllerNameParts[0] . $controllerNameParts[1] : '';
                if ('Module' === mb_substr($extensionName, -6)) {
                    $module = $this->extensionRepository->get($extensionName);
                    if (null !== $module) {
                        $moduleDisplayName = $module->getDisplayName();
                    }
                }
            }
            $title = str_replace('%modulename%', $moduleDisplayName, $title);
        }

        return $title;
    }

    public function getMetaDescription(): string
    {
        return $this->variableApi->getSystemVar('defaultmetadescription', '');
    }

    public function getLogoPath(): ?string
    {
        return '@CoreBundle:images/logo_with_title.png';
    }

    public function getMobileLogoPath(): ?string
    {
        return '@CoreBundle:images/zk-power.png';
    }

    public function getIconPath(): ?string
    {
        return '@CoreBundle:images/icon.png';
    }
}
