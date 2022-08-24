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

namespace Zikula\ZAuthBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** @deprected */
class LegacyRegistrationExtension extends AbstractExtension
{
    public function __construct(
        private readonly bool $mailVerificationRequired,
        private readonly bool $usePasswordStrengthMeter
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('mailVerificationRequired', [$this, 'mailVerificationRequired']),
            new TwigFunction('usePasswordStrengthMeter', [$this, 'usePasswordStrengthMeter']),
        ];
    }

    public function mailVerificationRequired(): bool
    {
        return $this->mailVerificationRequired;
    }

    public function usePasswordStrengthMeter(): bool
    {
        return $this->usePasswordStrengthMeter;
    }
}
