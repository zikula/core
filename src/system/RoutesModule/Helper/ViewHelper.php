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

namespace Zikula\RoutesModule\Helper;

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
                $enrichedTemplateParameters['jms_i18n_routing'] = $this->configDumper->getConfigurationForHtml('jms_i18n_routing');
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
        DynamicConfigDumper $configDumper,
        ExtensionRepositoryInterface $extensionRepository
    ): void {
        $this->configDumper = $configDumper;
        $this->extensionRepository = $extensionRepository;
    }
}
