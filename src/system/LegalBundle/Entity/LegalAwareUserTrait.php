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

use Doctrine\ORM\Mapping as ORM;

trait LegalAwareUserTrait
{
    #[ORM\Column(nullable: true)]
    private ?\DateTime $privacyPolicyAccepted = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $termsOfUseAccepted = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $tradeConditionsAccepted = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $cancellationRightPolicyAccepted = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $agePolicyAccepted = null;

    public function getPrivacyPolicyAccepted(): ?\DateTime
    {
        return $this->privacyPolicyAccepted;
    }

    public function setPrivacyPolicyAccepted(?\DateTime $privacyPolicyAccepted): self
    {
        $this->privacyPolicyAccepted = $privacyPolicyAccepted;

        return $this;
    }

    public function getTermsOfUseAccepted(): ?\DateTime
    {
        return $this->termsOfUseAccepted;
    }

    public function setTermsOfUseAccepted(?\DateTime $termsOfUseAccepted): self
    {
        $this->termsOfUseAccepted = $termsOfUseAccepted;

        return $this;
    }

    public function getTradeConditionsAccepted(): ?\DateTime
    {
        return $this->tradeConditionsAccepted;
    }

    public function setTradeConditionsAccepted(?\DateTime $tradeConditionsAccepted): self
    {
        $this->tradeConditionsAccepted = $tradeConditionsAccepted;

        return $this;
    }

    public function getCancellationRightPolicyAccepted(): ?\DateTime
    {
        return $this->cancellationRightPolicyAccepted;
    }

    public function setCancellationRightPolicyAccepted(?\DateTime $cancellationRightPolicyAccepted): self
    {
        $this->cancellationRightPolicyAccepted = $cancellationRightPolicyAccepted;

        return $this;
    }

    public function getAgePolicyAccepted(): ?\DateTime
    {
        return $this->agePolicyAccepted;
    }

    public function setAgePolicyAccepted(?\DateTime $agePolicyAccepted): self
    {
        $this->agePolicyAccepted = $agePolicyAccepted;

        return $this;
    }
}
