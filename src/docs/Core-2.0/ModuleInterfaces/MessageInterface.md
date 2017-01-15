Message Interface
=================

class:  `\Zikula\UsersModule\MessageModule\MessageModuleInterface`

Modules that want the Core to identify the module as Message-capable must implement a class which
implements this interface and then register that class in the container with the tag `zikula.message_module`.

Please note the legacy module capability setting in `composer.json` is entirely disabled and will not work.

The interface requires:

    /**
     * Get the url to a uid's inbox.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getInboxUrl($uid = null);

    /**
     * Get the count of all or only unread messages owned by the uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @param bool $unreadOnly
     * @return int
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getMessageCount($uid = null, $unreadOnly = false);

    /**
     * Get the url to send a message to the identified uid.
     * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
     * @param null $uid
     * @return string
     * @throws \InvalidArgumentException if provided $uid is not null and invalid
     */
    public function getSendMessageUrl($uid = null);

These methods are used in the Core's twig filters - `messageSendLink`, `messageInboxLink` and `messageCount`.
