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

namespace Zikula\RoutesModule\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\ExtensionsModule\Constant as ExtensionConstant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\RoutesModule\Helper\Base\AbstractViewHelper;

/**
 * Utility implementation class for view helper methods.
 */
class ViewHelper extends AbstractViewHelper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    public function processTemplate(
        string $type,
        string $func,
        array $templateParameters = [],
        string $template = ''
    ): Response {
        $enrichedTemplateParameters = $templateParameters;

        if ('route' === $type) {
            if ('view' === $func) {
                $configGroup = 'jms_i18n_routing';
                $dynamicConfig = $this->configDumper->getConfiguration($configGroup);
                $enrichedTemplateParameters[$configGroup] = [
                    'strategy' => $dynamicConfig['strategy'],
                    'default_locale' => $this->container->getParameter($configGroup . '.default_locale'),
                    'locales' => $this->container->getParameter($configGroup . '.locales')
                ];
            } elseif ('edit' === $func) {
                $urlNames = [];
                /** @var ExtensionEntity[] $modules */
                $modules = $this->extensionRepository->findBy(['state' => ExtensionConstant::STATE_ACTIVE]);
                foreach ($modules as $module) {
                    $urlNames[$module->getName()] = $module->getUrl();
                }
                $enrichedTemplateParameters['moduleUrlNames'] = $urlNames;
            }
        }

        return parent::processTemplate($type, $func, $enrichedTemplateParameters, $template);
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        ContainerInterface $container,
        DynamicConfigDumper $configDumper,
        ExtensionRepositoryInterface $extensionRepository
    ): void {
        $this->container = $container;
        $this->configDumper = $configDumper;
        $this->extensionRepository = $extensionRepository;
    }
}
