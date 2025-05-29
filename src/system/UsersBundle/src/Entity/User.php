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

namespace Zikula\UsersBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nucleos\UserBundle\Model\User as BaseUser;
use Nucleos\UserBundle\Validator\Constraints\Pattern as PasswordPattern;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\CoreBundle\Doctrine\DBAL\CustomTypes;
use Zikula\LegalBundle\Entity\LegalAwareUserInterface;
use Zikula\LegalBundle\Entity\LegalAwareUserTrait;
use Zikula\UsersBundle\Repository\UserRepository;
use Zikula\UsersBundle\UsersConstant;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'nucleos_user__user')]
#[
    ORM\Index(fields: ['username'], name: 'username'),
    ORM\Index(fields: ['email'], name: 'email')
]
class User extends BaseUser implements LegalAwareUserInterface
{
    use LegalAwareUserTrait;
    use UserAttributesTrait;

    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NoSuspiciousCharacters]
    protected ?string $username = null;

    #[Assert\Length(min: 12)]
    #[Assert\PasswordStrength]
    #[PasswordPattern(minUpper: 1, minLower: 1, minNumeric: 1, minSpecial: 1)]
    protected ?string $plainPassword = null;

    #[ORM\ManyToMany(targetEntity: Group::class)]
    #[ORM\JoinTable(name: 'nucleos_user__user_group')]
    #[ORM\JoinColumn(name: 'user_id')]
    #[ORM\InverseJoinColumn(name: 'group_id')]
    protected Collection $groups;

    /**
     * Account State: The user's current state, see \Zikula\UsersBundle\UsersConstant::ACTIVATED_* for defined constants.
     * A state represented by a negative integer means that the user's account is in a pending state, and should not yet be considered a "real" user account.
     * For example, user accounts pending the completion of the registration process (because either moderation, e-mail verification, or both are in use)
     * will have a negative integer representing their state. If the user's registration request expires before it the process is completed, or if the administrator
     * denies the request for a new account, the user account record will be deleted.
     * When this deletion happens, it will be assumed by the system that no external bundle has yet interacted with the user account record,
     * because its state never progressed beyond its pending state, and therefore normal hooks/events may not be triggered
     * (although it is possible that events regarding the pending account may be triggered).
     */
    #[Assert\Choice(callback: 'getActivatedValues')] // TODO replace by enum
    #[ORM\Column]
    private int $activated;

    /**
     * Account Approved Date/Time: The date and time the user's registration request was approved through the moderation process.
     * If the moderation process was not in effect at the time the user made a registration request, then this will be the date and time of the registration request.
     */
    #[ORM\Column(name: 'approved_date', type: CustomTypes::DATETIME_UTC)]
    private \DateTimeInterface $approvedDate;

    /**
     * The uid of the user account that approved the request to register a new account.
     * If this is the same as the user account's uid, then moderation was not in use at the time the request for a new account was made.
     * If this is -1, the user account that approved the request has since been deleted. If this is 0, the user account has not yet been approved.
     */
    #[ORM\Column(name: 'approved_by')]
    private int $approvedBy;

    /**
     * Registration Date/Time: Date/time the user account was registered.
     * For users not pending the completion of the registration process, this is the date and time the user account completed the process.
     * For example, if registrations are moderated, then this is the date and time the registration request was approved.
     * If registration e-mail addresses must be verified, then this is the date and time the user completed the verification process.
     * If both moderation and verification are in use, then this is the later of those two dates.
     * If neither is in use, then this is simply the date and time the user's registration request was made.
     * If the user account's activated state is "pending registration" (implying that either moderation, verification, or both are in use)
     * then this will be the date and time the user made the registration request UNTIL the registration process is complete, and then it is updated as above.
     */
    #[ORM\Column(name: 'user_regdate', type: CustomTypes::DATETIME_UTC)]
    private \DateTimeInterface $registrationDate;

    /**
     * Additional attributes of this user
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAttribute::class, cascade: ['all'], orphanRemoval: true, indexBy: 'name')]
    private Collection $attributes;

    public function __construct()
    {
        parent::__construct();

        $utcTZ = new \DateTimeZone('UTC');
        $this->approvedDate = new \DateTime('1970-01-01 00:00:00', $utcTZ);
        $this->approvedBy = 0;
        $this->registrationDate = new \DateTime('1970-01-01 00:00:00', $utcTZ);

        $this->attributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getActivated(): int
    {
        return $this->activated;
    }

    public function setActivated(int $activated): self
    {
        $this->activated = $activated;

        return $this;
    }

    public function getApprovedDate(): \DateTimeInterface
    {
        if (null !== $this->getTimezone()) {
            $this->approvedDate->setTimeZone(new \DateTimeZone($this->getTimezone()));
        }

        return $this->approvedDate;
    }

    public function setApprovedDate(string|\DateTimeInterface $approvedDate): self
    {
        if ($approvedDate instanceof \DateTimeInterface) {
            $this->approvedDate = $approvedDate;
        } else {
            $this->approvedDate = new \DateTime($approvedDate);
        }
        $this->setTimezone($this->approvedDate->getTimeZone()->getName());

        return $this;
    }

    public function getApprovedBy(): int
    {
        return $this->approvedBy;
    }

    public function setApprovedBy(int $approvedBy): self
    {
        $this->approvedBy = $approvedBy;

        return $this;
    }

    public function isApproved(): bool
    {
        return 0 !== $this->approvedBy;
    }

    public function getRegistrationDate(): \DateTimeInterface
    {
        if (null !== $this->getTimezone()) {
            $this->registrationDate->setTimeZone(new \DateTimeZone($this->getTimezone()));
        }

        return $this->registrationDate;
    }

    public function setRegistrationDate(string|\DateTimeInterface $registrationDate): self
    {
        if ($registrationDate instanceof \DateTimeInterface) {
            $this->registrationDate = $registrationDate;
        } else {
            $this->registrationDate = new \DateTime($registrationDate);
        }
        $this->setTimezone($this->registrationDate->getTimeZone()->getName());

        return $this;
    }

    /**
     * Callback function used to validate the activated value.
     */
    public static function getActivatedValues(): array
    {
        return [
            UsersConstant::ACTIVATED_ACTIVE,
            UsersConstant::ACTIVATED_INACTIVE,
            UsersConstant::ACTIVATED_PENDING_DELETE,
            UsersConstant::ACTIVATED_PENDING_REG,
        ];
    }
}
