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

namespace Zikula\GroupsModule\Helper;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Common helper functions and constants.
 */
class CommonHelper
{
    use TranslatorTrait;

    /**
     * Constant value for core type groups.
     */
    public const GTYPE_CORE = 0;

    /**
     * Constant value for public type groups.
     */
    public const GTYPE_PUBLIC = 1;

    /**
     * Constant value for private type groups.
     */
    public const GTYPE_PRIVATE = 2;

    /**
     * Constant value for groups in the Closed state (not accepting members).
     */
    public const STATE_CLOSED = 0;

    /**
     * Constant value for groups in the Open state (accepting members).
     */
    public const STATE_OPEN = 1;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Return the standard set of labels for group types.
     */
    public function gtypeLabels(): array
    {
        static $gtypeLabels;

        if (!isset($gtypeLabels)) {
            $gtypeLabels = [
                self::GTYPE_CORE => $this->trans('Core'),
                self::GTYPE_PUBLIC => $this->trans('Public'),
                self::GTYPE_PRIVATE => $this->trans('Private')
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
                self::STATE_CLOSED => $this->trans('Closed'),
                self::STATE_OPEN => $this->trans('Open')
            ];
        }

        return $stateLabels;
    }
}
