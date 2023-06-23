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

namespace Zikula\LegalBundle\Entity;

interface LegalAwareUserInterface
{
    public function getPrivacyPolicyAccepted(): ?\DateTime;

    public function setPrivacyPolicyAccepted(?\DateTime $accepted): self;

    public function getTermsOfUseAccepted(): ?\DateTime;

    public function setTermsOfUseAccepted(?\DateTime $accepted): self;

    public function getTradeConditionsAccepted(): ?\DateTime;

    public function setTradeConditionsAccepted(?\DateTime $accepted): self;

    public function getCancellationRightPolicyAccepted(): ?\DateTime;

    public function setCancellationRightPolicyAccepted(?\DateTime $accepted): self;

    public function getAgePolicyAccepted(): ?\DateTime;

    public function setAgePolicyAccepted(?\DateTime $accepted): self;
}
