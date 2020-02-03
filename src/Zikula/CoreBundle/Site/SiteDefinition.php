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

namespace Zikula\Bundle\CoreBundle\Site;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

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

    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        VariableApiInterface $variableApi,
        ExtensionRepositoryInterface $extensionRepository
    ) {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->variableApi = $variableApi;
        $this->extensionRepository = $extensionRepository;
    }

    public function getTitle(): string
    {
        $title = $this->variableApi->getSystemVar('defaultpagetitle');
        $titleScheme = $this->variableApi->getSystemVar('pagetitle', '');
        if (!empty($titleScheme) && '%pagetitle%' !== $titleScheme) {
            $title = str_replace(
                ['%pagetitle%', '%sitename%'],
                [$title, $this->variableApi->getSystemVar('sitename', '')],
                $titleScheme
            );

            $moduleDisplayName = '';
            $request = $this->requestStack->getCurrentRequest();
            if (null !== $request && null !== $request->attributes->get('_controller')) {
                $controllerNameParts = explode('\\', $request->attributes->get('_controller'));
                $bundleName = count($controllerNameParts) > 1 ? $controllerNameParts[0] . $controllerNameParts[1] : '';
                if ('Module' === mb_substr($bundleName, -6)) {
                    $module = $this->extensionRepository->get($bundleName);
                    if (null !== $module) {
                        $moduleDisplayName = $module->getDisplayName();
                    }
                }
            }
            $title = str_replace('%modulename%', $moduleDisplayName, $title);
        }

        return $title;
    }

    public function getDescription(): string
    {
        return $this->variableApi->getSystemVar('defaultmetadescription');
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
        return '@CoreBundle:images/logo.gif';
    }
}
