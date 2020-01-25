# Message interface

Modules that want the Core to identify the module as Message-capable must provide a class which implements
`\Zikula\UsersModule\MessageModule\MessageModuleInterface`.

This interface requires:
```php
/**
 * Get the url to a user's inbox.
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getInboxUrl($userId = null): string;

/**
 * Get the count of all or only unread messages owned by the uid.
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getMessageCount($userId = null, bool $unreadOnly = false): int;

/**
 * Get the url to send a message to the identified uid.
 * If uid is undefined, use CurrentUserApi to check loggedIn status and obtain and use the current user's uid
 *
 * @param int|string $userId The user's id or name
 * @throws InvalidArgumentException if provided $userId is not null and invalid
 */
public function getSendMessageUrl($userId = null): string;

/**
 * Return the name of the providing bundle.
 */
public function getBundleName(): string;
```

These methods are used in the Core's twig filters - `messageSendLink`, `messageInboxLink` and `messageCount`.
