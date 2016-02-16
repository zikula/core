<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Twig\Extension;

use Symfony\Component\Translation\TranslatorInterface;

class ExtensionsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ExtensionsExtension constructor.
     * @param $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulaextensionsmodule';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('stateLabel', [$this, 'stateLabel'], ['is_safe' => ['html']]),
        ];
    }

    public function stateLabel($state)
    {
        switch ($state) {
            case \ModUtil::STATE_INACTIVE:
                $status = $this->translator->__('Inactive');
                $statusclass = "warning";
                break;
            case \ModUtil::STATE_ACTIVE:
                $status = $this->translator->__('Active');
                $statusclass = "success";
                break;
            case \ModUtil::STATE_MISSING:
                $status = $this->translator->__('Files missing');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_UPGRADED:
                $status = $this->translator->__('New version');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_INVALID:
                $status = $this->translator->__('Invalid structure');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_NOTALLOWED:
                $status = $this->translator->__('Not allowed');
                $statusclass = "danger";
                break;
            case \ModUtil::STATE_UNINITIALISED:
            default:
                if ($state > 10) {
                    $status = $this->translator->__('Incompatible');
                    $statusclass = "info";
                } else {
                    $status = $this->translator->__('Not installed');
                    $statusclass = "primary";
                }
                break;
        }

        return '<span class="label label-' . $statusclass . '">' . $status . '</span>';
    }
}
