<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Twig\Extension;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class ExtensionsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ExtensionsExtension constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('stateLabel', [$this, 'stateLabel'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('isCoreModule', ['ZikulaKernel', 'isCoreModule']),
        ];
    }

    public function stateLabel(ExtensionEntity $extensionEntity, array $upgradedExtensions = null)
    {
        switch ($extensionEntity->getState()) {
            case Constant::STATE_INACTIVE:
                $status = $this->translator->__('Inactive');
                $statusclass = "warning";
                break;
            case Constant::STATE_ACTIVE:
                $status = $this->translator->__('Active');
                $statusclass = "success";
                break;
            case Constant::STATE_MISSING:
                $status = $this->translator->__('Files missing');
                $statusclass = "danger";
                break;
            case Constant::STATE_UPGRADED:
                $status = $this->translator->__('New version');
                $statusclass = "danger";
                break;
            case Constant::STATE_INVALID:
                $status = $this->translator->__('Invalid structure');
                $statusclass = "danger";
                break;
            case Constant::STATE_NOTALLOWED:
                $status = $this->translator->__('Not allowed');
                $statusclass = "danger";
                break;
            case Constant::STATE_UNINITIALISED:
            default:
                if ($extensionEntity->getState() > 10) {
                    $status = $this->translator->__('Incompatible');
                    $statusclass = "info";
                } else {
                    $status = $this->translator->__('Not installed');
                    $statusclass = "primary";
                }
                break;
        }

        $newVersionString = (Constant::STATE_UPGRADED == $extensionEntity->getState()) ? '&nbsp;<span class="label label-warning">' . $upgradedExtensions[$extensionEntity->getName()] . '</span>' : null;

        return '<span class="label label-' . $statusclass . '">' . $status . '</span>' . $newVersionString;
    }
}
