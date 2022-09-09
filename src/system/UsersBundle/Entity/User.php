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

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\GroupsBundle\Entity\Group;
use Zikula\UsersBundle\Repository\UserRepository;
use Zikula\UsersBundle\UsersConstant;
use Zikula\UsersBundle\Validator\Constraints as ZikulaAssert;

/**
 * Main user entity.
 * Stores core information about each user account.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[
    ORM\Index(fields: ['uname'], name: 'uname'),
    ORM\Index(fields: ['email'], name: 'email')
]
#[ZikulaAssert\ValidUserFields]
class User
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $uid;

    /**
     * Username: Primary user display name.
     */
    #[ORM\Column(length: 25)]
    #[Assert\Length(min: 1, max: 25)]
    #[ZikulaAssert\ValidUname]
    private string $uname;

    /**
     * E-mail address: For user notifications.
     */
    #[ORM\Column(length: 60)]
    #[Assert\Length(min: 1, max: 60)]
    #[ZikulaAssert\ValidEmail]
    private string $email;

    /**
     * Account State: The user's current state, see \Zikula\UsersBundle\Constant::ACTIVATED_* for defined constants.
     * A state represented by a negative integer means that the user's account is in a pending state, and should not yet be considered a "real" user account.
     * For example, user accounts pending the completion of the registration process (because either moderation, e-mail verification, or both are in use)
     * will have a negative integer representing their state. If the user's registration request expires before it the process is completed, or if the administrator
     * denies the request for an new account, the user account record will be deleted.
     * When this deletion happens, it will be assumed by the system that no external module has yet interacted with the user account record,
     * because its state never progressed beyond its pending state, and therefore normal events may not be triggered
     * (although it is possible that events regarding the pending account may be triggered).
     */
    #[Assert\Choice(choices: UsersConstant::ACTIVATED_OPTIONS)]
    #[ORM\Column(type: Types::SMALLINT)]
    private int $activated;

    /**
     * Account Approved Date/Time: The date and time the user's registration request was approved through the moderation process.
     * If the moderation process was not in effect at the time the user made a registration request, then this will be the date and time of the registration request.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time (unless one or the other happens to be UTC).
     * WARNING: The date and time related functions available in SQL on many RDBMS servers are highly dependent on the database server's timezone setting.
     * All parameters to these functions are treated as if the dates and times they represent are in the time zone that is set in the database server's settings.
     * Use of date/time functions in SQL queries should be avoided if at all possible. PHP functions using UTC as the base time zone should be used instead.
     * If SQL date/time functions must be used, then care should be taken to ensure that either the function is time zone neutral,
     * or that the function and its relationship to time zone settings is completely understood.
     */
    #[ORM\Column(name: 'approved_date', type: 'utcdatetime')]
    private DateTimeInterface $approvedDate;

    /**
     * The uid of the user account that approved the request to register a new account.
     * If this is the same as the user account's uid, then moderation was not in use at the time the request for a new account was made.
     * If this is -1, the the user account that approved the request has since been deleted. If this is 0, the user account has not yet been approved.
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
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time.
     * See WARNING under approvedDate above.
     */
    #[ORM\Column(name: 'user_regdate', type: 'utcdatetime')]
    private DateTimeInterface $registrationDate;

    /**
     * Last Login Date/Time: Date/time user last successfully logged into the site.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time.
     * See WARNING under approvedDate above.
     */
    #[ORM\Column(name: 'lastlogin', type: 'utcdatetime')]
    private DateTimeInterface $lastLogin;

    /**
     * User's timezone, as supported by PHP (listed at http://us2.php.net/manual/en/timezones.php), and as expressed by the Olson tz database.
     * Optional, if blank then the system default timezone should be used. [FUTURE USE]
     */
    #[ORM\Column(length: 30)]
    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(min: 1, max: 30)
    ])]
    private string $tz;

    /**
     * The user's chosen locale for i18n purposes, as defined by gettext, POSIX, and the Common Locale Data Repository;
     * Optional, if blank then the system default locale should be used.
     */
    #[ORM\Column(length: 5)]
    #[Assert\AtLeastOneOf([
        new Assert\Blank(),
        new Assert\Length(min: 1, max: 5)
    ])]
    private string $locale;

    /**
     * Additional attributes of this user
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAttribute::class, cascade: ['all'], orphanRemoval: true, indexBy: 'name')]
    private Collection $attributes;

    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'users', indexBy: 'gid')]
    #[ORM\JoinTable(name: 'group_membership')]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
    #[ORM\InverseJoinColumn(name: 'gid', referencedColumnName: 'gid')]
    /** @var Group[] */
    private Collection $groups;

    public function __construct()
    {
        $this->uname = '';
        $this->email = '';
        $this->activated = 0;
        $utcTZ = new \DateTimeZone('UTC');
        $this->approvedDate = new DateTime('1970-01-01 00:00:00', $utcTZ);
        $this->approvedBy = 0;
        $this->registrationDate = new DateTime('1970-01-01 00:00:00', $utcTZ);
        $this->lastLogin = new DateTime('1970-01-01 00:00:00', $utcTZ);
        $this->tz = '';
        $this->locale = '';

        $this->attributes = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getUname(): string
    {
        return $this->uname;
    }

    public function getUsername(): string
    {
        return $this->getUname();
    }

    public function setUname(string $uname): self
    {
        $this->uname = $uname;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getApprovedDate(): DateTimeInterface
    {
        return $this->approvedDate;
    }

    public function setApprovedDate(string|DateTimeInterface $approvedDate): self
    {
        if ($approvedDate instanceof DateTimeInterface) {
            $this->approvedDate = $approvedDate;
        } else {
            $this->approvedDate = new DateTime($approvedDate);
        }

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

    public function getRegistrationDate(): DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(string|DateTimeInterface $registrationDate): self
    {
        if ($registrationDate instanceof DateTimeInterface) {
            $this->registrationDate = $registrationDate;
        } else {
            $this->registrationDate = new DateTime($registrationDate);
        }

        return $this;
    }

    public function getLastLogin(): DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(string|DateTimeInterface $lastLogin): self
    {
        if ($lastLogin instanceof DateTimeInterface) {
            $this->lastLogin = $lastLogin;
        } else {
            $this->lastLogin = new DateTime($lastLogin);
        }

        return $this;
    }

    public function getTz(): string
    {
        return $this->tz;
    }

    public function setTz(string $tz): self
    {
        $this->tz = $tz;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function getAttributeValue(string $name): string
    {
        return $this->getAttributes()->offsetExists($name) ? $this->getAttributes()->get($name)->getValue() : '';
    }

    public function setAttributes(ArrayCollection $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): self
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new UserAttribute($this, $name, $value);
        }

        return $this;
    }

    public function delAttribute(string $name): self
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }

        return $this;
    }

    public function hasAttribute(string $name): bool
    {
        return $this->attributes->containsKey($name);
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(ArrayCollection $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    public function addGroup(Group $group): self
    {
        $group->addUser($this);
        $this->groups[] = $group;

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        $group->removeUser($this);
        $this->groups->removeElement($group);

        return $this;
    }

    public function removeGroups(): self
    {
        /** @var Group $group */
        foreach ($this->groups as $group) {
            $group->removeUser($this);
        }
        $this->groups->clear();

        return $this;
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }
}