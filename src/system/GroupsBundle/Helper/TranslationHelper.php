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

namespace Zikula\GroupsBundle\Helper;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsBundle\GroupsConstant;

class TranslationHelper
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Return the standard set of labels for group types.
     */
    public function gtypeLabels(): array
    {
        static $gtypeLabels;

        if (!isset($gtypeLabels)) {
            $gtypeLabels = [
                GroupsConstant::GTYPE_CORE => $this->translator->trans('Core'),
                GroupsConstant::GTYPE_PUBLIC => $this->translator->trans('Public'),
                GroupsConstant::GTYPE_PRIVATE => $this->translator->trans('Private'),
            ];
        }

        return $gtypeLabels;
    }

    /**
     * Return the standard set of labels for group states.
     */
    public function stateLabels(): array
    {
        static $stateLabels;

        if (!isset($stateLabels)) {
            $stateLabels = [
                GroupsConstant::STATE_CLOSED => $this->translator->trans('Closed'),
                GroupsConstant::STATE_OPEN => $this->translator->trans('Open'),
            ];
        }

        return $stateLabels;
    }
}
