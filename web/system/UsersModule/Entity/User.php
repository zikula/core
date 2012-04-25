<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace UsersModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;

/**
 * User entity class.
 *
 * We use annotations to define the entity mappings to database (see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html).
 *
 * @ORM\Entity
 * @ORM\Table(name="users",indexes={@ORM\index(name="uname",columns={"uname"}), @ORM\index(name="email",columns={"email"})})
 *
 * Main Users table.
 * Stores core information about each user account.
 */
class User extends EntityAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * User ID: Primary user identifier
     */
    private $uid;

    /**
     * @ORM\Column(type="string", length=25)
     *
     * User Name: Primary user display name, primary log in identifier.
     */
    private $uname;

    /**
     * @ORM\Column(type="string", length=60)
     *
     * E-mail Address: Secondary log in identifier, user notifications.
     * For pending registrations awaiting e-mail address verification, this will be an empty string, and the email address for the account will be found in the users_verifychg table.
     * ("Regular" user accounts may also have e-mail addresses pending verification in the users_verifychg table, however those are the result of a request to change the account's address.)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=138)
     *
     * Password: User's password for logging in.
     * This value is salted and hashed. The salt is stored in this field, delimited from the hash with a dollar sign character ($).
     * The hash algorithm is stored as a numeric code in the hash_method field. This field may be blank in instances
     * where the user registered with an alternative authentication module (e.g., OpenID) and did not also establish a password for his web site account.
     */
    private $pass;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * Password reminder: Set during registration or password changes, to remind the user what his password is.
     * This field may be blank if pass is blank.
     */
    private $passreminder;

    /**
     * @ORM\Column(type="smallint")
     *
     * Account State: The user's current state, see UsersConstant::ACTIVE_* for defined constants.
     * A state represented by a negative integer means that the user's account is in a pending state, and should not yet be considered a "real" user account.
     * For example, user accounts pending the completion of the registration process (because either moderation, e-mail verification, or both are in use)
     * will have a negative integer representing their state. If the user's registration request expires before it the process is completed, or if the administrator
     * denies the request for an new account, the user account record will be deleted.
     * When this deletion happens, it will be assumed by the system that no external module has yet interacted with the user account record,
     * because its state never progressed beyond its pending state, and therefore normal hooks/events may not be triggered
     * (although it is possible that events regarding the pending account may be triggered).
     */
    private $activated;

    /**
     * @ORM\Column(type="datetime")
     *
     * Account Approved Date/Time: The date and time the user's registration request was approved through the moderation process.
     * If the moderation process was not in effect at the time the user made a registration request, then this will be the date and time of the registration request.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time (unless one or the other happens to be UTC).
     * WARNING: The date and time related functions available in SQL on many RDBMS servers are highly dependent on the database server's timezone setting.
     * All parameters to these functions are treated as if the dates and times they represent are in the time zone that is set in the database server's settings.
     * Use of date/time functions in SQL queries should be avoided if at all possible. PHP functions using UTC as the base time zone should be used instead.
     * If SQL date/time functions must be used, then care should be taken to ensure that either the function is time zone neutral,
     * or that the function and its relationship to time zone settings is completely understood.
     */
    private $approved_date;

    /**
     * @ORM\Column(type="integer")
     *
     * The uid of the user account that approved the request to register a new account.
     * If this is the same as the user account's uid, then moderation was not in use at the time the request for a new account was made.
     * If this is -1, the the user account that approved the request has since been deleted. If this is 0, the user account has not yet been approved.
     */
    private $approved_by;

    /**
     * @ORM\Column(type="datetime")
     *
     * Registration Date/Time: Date/time the user account was registered.
     * For users not pending the completion of the registration process, this is the date and time the user account completed the process.
     * For example, if registrations are moderated, then this is the date and time the registration request was approved.
     * If registration e-mail addresses must be verified, then this is the date and time the user completed the verification process.
     * If both moderation and verification are in use, then this is the later of those two dates.
     * If neither is in use, then this is simply the date and time the user's registration request was made.
     * If the user account's activated state is "pending registration" (implying that either moderation, verification, or both are in use)
     * then this will be the date and time the user made the registration request UNTIL the registration process is complete, and then it is updated as above.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time. SEE WARNING under approved_date, above.
     */
    private $user_regdate;

    /**
     * @ORM\Column(type="datetime")
     *
     * Last Login Date/Time: Date/time user last successfully logged into the site.
     * NOTE: This is stored as an SQL datetime, using the UTC time zone. The date/time is NEITHER server local time nor user local time. SEE WARNING under approved_date, above.
     */
    private $lastlogin;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * User's Theme: The name (identifier) of the per-user theme the user would like to use while viewing the site, when user theme switching is enabled.
     */
    private $theme;

    /**
     * @ORM\Column(type="smallint")
     *
     * User-defined Block On?: Whether the custom user-defined block is displayed or not (1 == true == displayed)
     */
    private $ublockon;

    /**
     * @ORM\Column(type="text")
     *
     * User-defined Block: Custom user-defined block content.
     */
    private $ublock;

    /**
     * @ORM\Column(type="string", length=30)
     *
     * User's timezone, as supported by PHP (listed at http://us2.php.net/manual/en/timezones.php), and as expressed by the Olson tz database.
     * Optional, if blank then the system default timezone should be used. [FUTURE USE]
     */
    private $tz;

    /**
     * @ORM\Column(type="string", length=5)
     *
     * The user's chosen locale for i18n purposes, as defined by gettext, POSIX, and the Common Locale Data Repository;
     * Optional, if blank then the system default locale should be used. [FUTURE USE]
     */
    private $locale;
    
    /**
     * @ORM\OneToMany(targetEntity="UserAttribute", 
     *                mappedBy="user", 
     *                cascade={"all"},
     *                orphanRemoval=true,
     *                indexBy="name")
     */
    private $attributes;


    /**
     * constructor
     */
    public function __construct()
    {
        $this->uname = '';
        $this->email = '';
        $this->pass = '';
        $this->passreminder = '';
        $this->activated = 0;
        $this->approved_date = new \DateTime("1970-01-01 00:00:00");
        $this->approved_by = 0;
        $this->user_regdate = new \DateTime("1970-01-01 00:00:00");
        $this->lastlogin = new \DateTime("1970-01-01 00:00:00");
        $this->theme = '';
        $this->ublockon = 0;
        $this->ublock = '';
        $this->tz = '';
        $this->locale = '';
        
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * get the password of the user
     *
     * @return string the user's password
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * set the password for the user
     *
     * @param string $pass the user's password
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * get the password reminder of the user
     *
     * @return string the user's password reminder
     */
    public function getPassreminder()
    {
        return $this->passreminder;
    }

    /**
     * set the password reminder for the user
     *
     * @param string $passreminder the user's password reminder
     */
    public function setPassreminder($passreminder)
    {
        $this->passreminder = $passreminder;
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
     * @return datetime the user's approved date
     */
    public function getApproved_Date()
    {
        return $this->approved_date;
    }

    /**
     * set the approved date for the user
     *
     * @param datetime $approved date the user's approved_date
     */
    public function setApproved_Date($approved_date)
    {
        $this->approved_date = new \DateTime($approved_date);
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
     * get the regdate of the user
     *
     * @return datetime the user's regdate
     */
    public function getUser_Regdate()
    {
        return $this->user_regdate;
    }

    /**
     * set the regdate for the user
     *
     * @param datetime $user_regdate the user's regdate
     */
    public function setUser_Regdate($user_regdate)
    {
        $this->user_regdate = new \DateTime($user_regdate);
    }

    /**
     * get the last login of the user
     *
     * @return datetime the user's last login
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * set the last login for the user
     *
     * @param datetime $lastlogin the user's last login
     */
    public function setLastlogin($lastlogin)
    {
        $this->lastlogin = new \DateTime($lastlogin);
    }

    /**
     * get the theme of the user
     *
     * @return string the user's theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * set the theme for the user
     *
     * @param string $theme the user's theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * get the ublockon of the user
     *
     * @return integer the user's ublockon
     */
    public function getUblockon()
    {
        return $this->ublockon;
    }

    /**
     * set the ublockon for the user
     *
     * @param integer $ublockon the user's ublockon
     */
    public function setUblockon($ublockon)
    {
        $this->ublockon = $ublockon;
    }

    /**
     * get the ublock of the user
     *
     * @return string the user's ublock
     */
    public function getUblock()
    {
        return $this->ublock;
    }

    /**
     * set the ublock for the user
     *
     * @param string $ublock the user's ublock
     */
    public function setUblock($ublock)
    {
        $this->ublock = $ublock;
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
     * @return UsersModule\Entity\UserAttribute the user's attributes
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * set the attributes for the user
     *
     * @param UsersModule\Entity\UserAttribute $attributes the attributes for the user
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
            $this->attributes[$name] = new UserAttribute($this, $name, $value);
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
}
