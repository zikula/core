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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
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
     * @var ParameterBagInterface
     */
    private $parameterBag;

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
                $enrichedTemplateParameters['jms_i18n_routing'] = [
                    'strategy' => $this->parameterBag->get('jms_i18n_routing.strategy'),
                    'default_locale' => $this->parameterBag->get('jms_i18n_routing.default_locale'),
                    'locales' => $this->parameterBag->get('jms_i18n_routing.locales')
                ];
            } elseif ('edit' === $func) {
                $urlNames = [];
                /** @var ExtensionEntity[] $extensions */
                $extensions = $this->extensionRepository->findBy(['state' => ExtensionConstant::STATE_ACTIVE]);
                foreach ($extensions as $extension) {
                    $urlNames[$extension->getName()] = $extension->getUrl();
                }
                $enrichedTemplateParameters['extensionUrlNames'] = $urlNames;
            }
        }

        return parent::processTemplate($type, $func, $enrichedTemplateParameters, $template);
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        ParameterBagInterface $parameterBag,
        ExtensionRepositoryInterface $extensionRepository
    ): void {
        $this->parameterBag = $parameterBag;
        $this->extensionRepository = $extensionRepository;
    }
}
