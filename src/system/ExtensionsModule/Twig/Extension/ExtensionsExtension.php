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

namespace Zikula\ExtensionsModule\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class ExtensionsExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('stateLabel', [$this, 'stateLabel'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('isCoreModule', ['ZikulaKernel', 'isCoreModule'])
        ];
    }

    public function stateLabel(ExtensionEntity $extensionEntity, array $upgradedExtensions = null): string
    {
        switch ($extensionEntity->getState()) {
            case Constant::STATE_INACTIVE:
                $status = $this->translator->trans('Inactive');
                $statusclass = 'warning';
                break;
            case Constant::STATE_ACTIVE:
                $status = $this->translator->trans('Active');
                $statusclass = 'success';
                break;
            case Constant::STATE_MISSING:
                $status = $this->translator->trans('Files missing');
                $statusclass = 'danger';
                break;
            case Constant::STATE_UPGRADED:
                $status = $this->translator->trans('New version');
                $statusclass = 'danger';
                break;
            case Constant::STATE_INVALID:
                $status = $this->translator->trans('Invalid structure');
                $statusclass = 'danger';
                break;
            case Constant::STATE_NOTALLOWED:
                $status = $this->translator->trans('Not allowed');
                $statusclass = 'danger';
                break;
            case Constant::STATE_UNINITIALISED:
            default:
                if ($extensionEntity->getState() > 10) {
                    $status = $this->translator->trans('Incompatible');
                    $statusclass = 'info';
                } else {
                    $status = $this->translator->trans('Not installed');
                    $statusclass = 'primary';
                }
                break;
        }

        $newVersionString = (Constant::STATE_UPGRADED === $extensionEntity->getState()) ? '&nbsp;<span class="badge badge-warning">' . $upgradedExtensions[$extensionEntity->getName()] . '</span>' : null;

        return '<span class="badge badge-' . $statusclass . '">' . $status . '</span>' . $newVersionString;
    }
}
