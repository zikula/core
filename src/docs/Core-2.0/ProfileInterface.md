Profile Interface
=================

class:  `\Zikula\UsersModule\ProfileModule\ProfileModuleInterface`

Modules that want the Core to identiy the module as Profile-capable must implement a class which
implements this interface and then register that class in the container with the tag `zikula.profile_module`.

Please note the legacy module capability setting in `composer.json` is entirely disabled and will not work.

The interface requires:

    /**
     * Display a module-defined user display name (e.g. set by the user) or display the uname as defined by the UserModule
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getDisplayName($uid = null);

    /**
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getProfileUrl($uid = null);

These methods are used in the Core's twig filters - `profileLinkByUserId` and `profileLinkByUserName`
