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

namespace Zikula\UsersModule\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints as ZikulaAssert;

/**
 * User entity class.
 *
 * @ORM\Entity(repositoryClass="Zikula\UsersModule\Entity\Repository\UserRepository")
 * @ORM\Table(name="users",indexes={@ORM\Index(name="uname",columns={"uname"}), @ORM\Index(name="email",columns={"email"})})
 *
 * @ZikulaAssert\ValidUserFields()
 *
 * Main Users table.
 * Stores core information about each user account.
 */
class UserEntity extends EntityAccess
{
    /**
     * User ID: Primary user identifier
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $uid;

    /**
     * User Name: Primary user display name.
     *
     * @ORM\Column(type="string", length=25)
     * @Assert\Length(min="0", max="25", allowEmptyString="false")
     * @ZikulaAssert\ValidUname()
     * @var string
     */
    private $uname;

    /**
     * E-mail Address: For user notifications.
     *
     * @ORM\Column(type="string", length=60)
     * @Assert\Length(min="0", max="60", allowEmptyString="false")
     * @ZikulaAssert\ValidEmail()
     * @var string
     */
    private $email;

    /**
     * Account State: The user's current state, see \Zikula\UsersModule\Constant::ACTIVATED_* for defined constants.
     * A state represented by a negative integer means that the user's account is in a pending state, and should not yet be considered a "real" user account.
     * For example, user accounts pending the completion of the registration process (because either moderation, e-mail verification, or both are in use)
     * will have a negative integer representing their state. If the user's registration request expires before it the process is completed, or if the administrator
     * denies the request for an new account, the user account record will be deleted.
     * When this deletion happens, it will be assumed by the system that no external module has yet interacted with the user account record,
     * because its state never progressed beyond its pending state, and therefore normal hooks/events may not be triggered
     * (although it is possible that events regarding the pending account may be triggered).
     *
     * @Assert\Choice(callback = "getActivatedValues")
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $activated;

    /**
     * Account Approved Date/Time: The date and time the user's registration request was approved through the moderation process.
     * If the moderation process was not in effect at the time the user made a registration request, then this will be the date and time of the registration request.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time (unless one or the other happens to be UTC).
     * WARNING: The date and time related functions available in SQL on many RDBMS servers are highly dependent on the database server's timezone setting.
     * All parameters to these functions are treated as if the dates and times they represent are in the time zone that is set in the database server's settings.
     * Use of date/time functions in SQL queries should be avoided if at all possible. PHP functions using UTC as the base time zone should be used instead.
     * If SQL date/time functions must be used, then care should be taken to ensure that either the function is time zone neutral,
     * or that the function and its relationship to time zone settings is completely understood.
     *
     * @ORM\Column(type="utcdatetime")
     * @Assert\DateTime()
     * @var DateTime
     */
    private $approved_date;

    /**
     * The uid of the user account that approved the request to register a new account.
     * If this is the same as the user account's uid, then moderation was not in use at the time the request for a new account was made.
     * If this is -1, the the user account that approved the request has since been deleted. If this is 0, the user account has not yet been approved.
     *
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     * @var int
     */
    private $approved_by;

    /**
     * Registration Date/Time: Date/time the user account was registered.
     * For users not pending the completion of the registration process, this is the date and time the user account completed the process.
     * For example, if registrations are moderated, then this is the date and time the registration request was approved.
     * If registration e-mail addresses must be verified, then this is the date and time the user completed the verification process.
     * If both moderation and verification are in use, then this is the later of those two dates.
     * If neither is in use, then this is simply the date and time the user's registration request was made.
     * If the user account's activated state is "pending registration" (implying that either moderation, verification, or both are in use)
     * then this will be the date and time the user made the registration request UNTIL the registration process is complete, and then it is updated as above.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time. SEE WARNING under approved_date, above.
     *
     * @ORM\Column(type="utcdatetime")
     * @Assert\DateTime()
     * @var DateTime
     */
    private $user_regdate;

    /**
     * Last Login Date/Time: Date/time user last successfully logged into the site.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time. SEE WARNING under approved_date, above.
     *
     * @ORM\Column(type="utcdatetime")
     * @Assert\DateTime()
     * @var DateTime
     */
    private $lastlogin;

    /**
     * User's timezone, as supported by PHP (listed at http://us2.php.net/manual/en/timezones.php), and as expressed by the Olson tz database.
     * Optional, if blank then the system default timezone should be used. [FUTURE USE]
     *
     * @ORM\Column(type="string", length=30)
     * @Assert\Type(type="string")
     * @Assert\Length(min="0", max="30", allowEmptyString="true")
     * @var string
     */
    private $tz;

    /**
     * The user's chosen locale for i18n purposes, as defined by gettext, POSIX, and the Common Locale Data Repository;
     * Optional, if blank then the system default locale should be used.
     *
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=5)
     * @Assert\Length(min="0", max="5", allowEmptyString="true")
     * @var string
     */
    private $locale;

    /**
     * Additional attributes of this user
     *
     * @ORM\OneToMany(targetEntity="UserAttributeEntity",
     *                mappedBy="user",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name"
     * )
     */
    private $attributes;

    /**
     * @ORM\ManyToMany(targetEntity="Zikula\GroupsModule\Entity\GroupEntity", inversedBy="users", indexBy="gid")
     * @ORM\JoinTable(name="group_membership",
     *      joinColumns={@ORM\JoinColumn(name="uid", referencedColumnName="uid")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="gid", referencedColumnName="gid")}
     * )
     */
    private $groups;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->uname = '';
        $this->email = '';
        $this->activated = 0;
        $this->approved_date = new DateTime('1970-01-01 00:00:00');
        $this->approved_by = 0;
        $this->user_regdate = new DateTime('1970-01-01 00:00:00');
        $this->lastlogin = new DateTime('1970-01-01 00:00:00');
        $this->tz = '';
        $this->locale = '';

        $this->attributes = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    public function getUname(): string
    {
        return $this->uname;
    }

    public function getUsername(): string
    {
        return $this->getUname();
    }

    public function setUname(string $uname): void
    {
        $this->uname = $uname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getActivated(): int
    {
        return $this->activated;
    }

    public function setActivated(int $activated): void
    {
        $this->activated = $activated;
    }

    public function getApproved_Date(): DateTime
    {
        return $this->approved_date;
    }

    /**
     * @param string|DateTime $approved_date the user's approved date
     */
    public function setApproved_Date($approved_date): void
    {
        if ($approved_date instanceof DateTime) {
            $this->approved_date = $approved_date;
        } else {
            $this->approved_date = new DateTime($approved_date);
        }
    }

    public function getApproved_By(): int
    {
        return $this->approved_by;
    }

    public function setApproved_By(int $approved_by): void
    {
        $this->approved_by = $approved_by;
    }

    public function isApproved(): bool
    {
        return 0 !== $this->approved_by;
    }

    public function getUser_Regdate(): DateTime
    {
        return $this->user_regdate;
    }

    /**
     * @param string|DateTime $user_regdate the user's regdate
     */
    public function setUser_Regdate($user_regdate): void
    {
        if ($user_regdate instanceof DateTime) {
            $this->user_regdate = $user_regdate;
        } else {
            $this->user_regdate = new DateTime($user_regdate);
        }
    }

    public function getLastlogin(): DateTime
    {
        return $this->lastlogin;
    }

    /**
     * @param string DateTime $lastlogin the user's last login
     */
    public function setLastlogin($lastlogin): void
    {
        if ($lastlogin instanceof DateTime) {
            $this->lastlogin = $lastlogin;
        } else {
            $this->lastlogin = new DateTime($lastlogin);
        }
    }

    public function getTz(): string
    {
        return $this->tz;
    }

    public function setTz(string $tz): void
    {
        $this->tz = $tz;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function getAttributeValue(string $name): string
    {
        return $this->getAttributes()->offsetExists($name) ? $this->getAttributes()->get($name)->getValue() : '';
    }

    public function setAttributes(ArrayCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new UserAttributeEntity($this, $name, $value);
        }
    }

    public function delAttribute(string $name): void
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }
    }

    public function hasAttribute(string $name): bool
    {
        return $this->attributes->containsKey($name);
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function setGroups(ArrayCollection $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * UserEntity is the 'Owning side'
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#owning-and-inverse-side-on-a-manytomany-association
     */
    public function addGroup(GroupEntity $group): void
    {
        $group->addUser($this);
        $this->groups[] = $group;
    }

    public function removeGroup(GroupEntity $group): void
    {
        $group->removeUser($this);
        $this->groups->removeElement($group);
    }

    public function removeGroups(): void
    {
        /** @var GroupEntity $group */
        foreach ($this->groups as $group) {
            $group->removeUser($this);
        }
        $this->groups->clear();
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
            UsersConstant::ACTIVATED_PENDING_REG
        ];
    }
}
