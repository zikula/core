<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
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
     */
    private $uid;

    /**
     * User Name: Primary user display name.
     *
     * @ZikulaAssert\ValidUname()
     * @ORM\Column(type="string", length=25)
     */
    private $uname;

    /**
     * E-mail Address: For user notifications.
     *
     * @ZikulaAssert\ValidEmail()
     * @ORM\Column(type="string", length=60)
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
     * @Assert\DateTime()
     * @ORM\Column(type="utcdatetime")
     */
    private $approved_date;

    /**
     * The uid of the user account that approved the request to register a new account.
     * If this is the same as the user account's uid, then moderation was not in use at the time the request for a new account was made.
     * If this is -1, the the user account that approved the request has since been deleted. If this is 0, the user account has not yet been approved.
     *
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer")
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
     * @Assert\DateTime()
     * @ORM\Column(type="utcdatetime")
     */
    private $user_regdate;

    /**
     * Last Login Date/Time: Date/time user last successfully logged into the site.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time. SEE WARNING under approved_date, above.
     *
     * @Assert\DateTime()
     * @ORM\Column(type="utcdatetime")
     */
    private $lastlogin;

    /**
     * User's timezone, as supported by PHP (listed at http://us2.php.net/manual/en/timezones.php), and as expressed by the Olson tz database.
     * Optional, if blank then the system default timezone should be used. [FUTURE USE]
     *
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=30)
     */
    private $tz;

    /**
     * The user's chosen locale for i18n purposes, as defined by gettext, POSIX, and the Common Locale Data Repository;
     * Optional, if blank then the system default locale should be used.
     *
     * @Assert\Type(type="string")
     * @ORM\Column(type="string", length=5)
     */
    private $locale;

    /**
     * Additional attributes of this user
     *
     * @ORM\OneToMany(targetEntity="UserAttributeEntity",
     *                mappedBy="user",
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;

    /**
     * @ORM\ManyToMany(targetEntity="Zikula\GroupsModule\Entity\GroupEntity", inversedBy="users", indexBy="gid")
     * @ORM\JoinTable(name="group_membership",
     *      joinColumns={@ORM\JoinColumn(name="uid", referencedColumnName="uid")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="gid", referencedColumnName="gid")}
     *      )
     **/
    private $groups;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->uname = '';
        $this->email = '';
        $this->activated = 0;
        $this->approved_date = new \DateTime("1970-01-01 00:00:00");
        $this->approved_by = 0;
        $this->user_regdate = new \DateTime("1970-01-01 00:00:00");
        $this->lastlogin = new \DateTime("1970-01-01 00:00:00");
        $this->tz = '';
        $this->locale = '';

        $this->attributes = new ArrayCollection();
        $this->groups = new ArrayCollection();
    }

    /**
     * get the uid of the user
     *
     * @return integer the user's uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * set the uid for the user
     *
     * @param integer $uid the user's uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * get the uname of the user
     *
     * @return string the user's uname
     */
    public function getUname()
    {
        return $this->uname;
    }

    /**
     * get the username of the user
     *
     * @return string the user's name
     */
    public function getUsername()
    {
        return $this->getUname();
    }

    /**
     * set the uname for the user
     *
     * @param string $uname the user's uname
     */
    public function setUname($uname)
    {
        $this->uname = $uname;
    }

    /**
     * get the email of the user
     *
     * @return string the user's email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * set the email for the user
     *
     * @param string $email the user's email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * get the activation status of the user
     *
     * @return integer the user's activation status
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * set the activation status for the user
     *
     * @param integer $activated the user's activation status
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
    }

    /**
     * get the approved date of the user
     *
     * @return \DateTime the user's approved date
     */
    public function getApproved_Date()
    {
        return $this->approved_date;
    }

    /**
     * set the approved date for the user
     *
     * @param \DateTime $approved_date the user's approved date
     */
    public function setApproved_Date($approved_date)
    {
        if ($approved_date instanceof \DateTime) {
            $this->approved_date = $approved_date;
        } else {
            // assume $approved_date is a string.
            $this->approved_date = new \DateTime($approved_date);
        }
    }

    /**
     * get the user id who approved the user
     *
     * @return integer the user's id who approved the user
     */
    public function getApproved_By()
    {
        return $this->approved_by;
    }

    /**
     * set the user id who approved the user
     *
     * @param integer $approved_by the user's id who approved the user
     */
    public function setApproved_By($approved_by)
    {
        $this->approved_by = $approved_by;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->approved_by != 0;
    }

    /**
     * get the regdate of the user
     *
     * @return \DateTime the user's regdate
     */
    public function getUser_Regdate()
    {
        return $this->user_regdate;
    }

    /**
     * set the regdate for the user
     *
     * @param \DateTime $user_regdate the user's regdate
     */
    public function setUser_Regdate($user_regdate)
    {
        if ($user_regdate instanceof \DateTime) {
            $this->user_regdate = $user_regdate;
        } else {
            // assume $user_regdate is a string
            $this->user_regdate = new \DateTime($user_regdate);
        }
    }

    /**
     * get the last login of the user
     *
     * @return \DateTime the user's last login
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * set the last login for the user
     *
     * @param \DateTime $lastlogin the user's last login
     */
    public function setLastlogin($lastlogin)
    {
        if ($lastlogin instanceof \DateTime) {
            $this->lastlogin = $lastlogin;
        } else {
            // assume $lastlogin is a string
            $this->lastlogin = new \DateTime($lastlogin);
        }
    }

    /**
     * get the tz of the user
     *
     * @return string the user's tz
     */
    public function getTz()
    {
        return $this->tz;
    }

    /**
     * set the tz for the user
     *
     * @param string $tz the user's tz
     */
    public function setTz($tz)
    {
        $this->tz = $tz;
    }

    /**
     * get the locale of the user
     *
     * @return string the user's locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * set the locale for the user
     *
     * @param string $locale the user's locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * get the attributes of the user
     *
     * @return PersistentCollection|ArrayCollection UserAttributeEntity[] of the user's attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttributeValue($name)
    {
        return $this->getAttributes()->get($name)->getValue();
    }

    /**
     * set the attributes for the user
     *
     * @param UserAttributeEntity $attributes the attributes for the user
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * set a single attribute for the user
     *
     * @param $name string attribute name
     * @param $value string attribute value
     */
    public function setAttribute($name, $value)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new UserAttributeEntity($this, $name, $value);
        }
    }

    /**
     * delete a single attribute of the user
     *
     * @param $name string attribute name
     */
    public function delAttribute($name)
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return $this->attributes->containsKey($name);
    }

    /**
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups(ArrayCollection $groups)
    {
        $this->groups = $groups;
    }

    /**
     * UserEntity is the 'Owning side'
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#owning-and-inverse-side-on-a-manytomany-association
     * @param GroupEntity $group
     */
    public function addGroup(GroupEntity $group)
    {
        $group->addUser($this);
        $this->groups[] = $group;
    }

    public function removeGroup(GroupEntity $group)
    {
        $group->removeUser($this);
        $this->groups->removeElement($group);
    }

    public function removeGroups()
    {
        /** @var GroupEntity $group */
        foreach ($this->groups as $group) {
            $group->removeUser($this);
        }
        $this->groups->clear();
    }

    /**
     * Callback function used to validate the activated value
     * @return array
     */
    public static function getActivatedValues()
    {
        return [
            UsersConstant::ACTIVATED_ACTIVE,
            UsersConstant::ACTIVATED_INACTIVE,
            UsersConstant::ACTIVATED_PENDING_DELETE,
            UsersConstant::ACTIVATED_PENDING_REG
        ];
    }
}
