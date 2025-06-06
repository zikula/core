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

namespace Zikula\UsersBundle\Twig\Runtime;

use InvalidArgumentException;
use Nucleos\UserBundle\Model\UserManager;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Helper\ProfileHelper;
use function Symfony\Component\String\s;

class ProfileRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ProfileHelper $profileHelper,
        private readonly UserManager $userManager,
        private readonly TranslatorInterface $translator,
        private readonly bool $displayRegistrationDate = false
    ) {
    }

    /**
     * Whether to show the users registration date or not.
     */
    public function displayRegistrationDate(): bool
    {
        return $this->displayRegistrationDate;
    }

    /**
     * Displays the avatar of a given user.
     *
     * @param int|string $userId The user's id or name
     */
    public function getUserAvatar($userId = 0, array $parameters = []): string
    {
        return $this->profileHelper->getAvatar($userId, $parameters);
    }

    /**
     * Create a link to a users profile from the UID.
     *
     * Examples
     *
     *   Simple version, shows the username
     *   {{ uid|profileLinkByUserId() }}
     *   Simple version, shows username, using class="classname"
     *   {{ uid|profileLinkByUserId(class='classname') }}
     *   Using profile.gif instead of username, no class
     *   {{ uid|profileLinkByUserId(image='images/profile.gif') }}
     */
    public function profileLinkByUserId(
        int $userId,
        string $class = '',
        string $image = '',
        int $maxLength = 0,
        string $title = ''
    ): string {
        if (empty($userId) || 1 > $userId) {
            return (string) $userId;
        }

        return $this->determineProfileLink($userId, null, $class, $image, $maxLength, $title);
    }

    /**
     * Create a link to a users profile from the username.
     *
     * Examples
     *
     *   Simple version, shows the username
     *   {{ username|profileLinkByUserName() }}
     *   Simple version, shows username, using class="classname"
     *   {{ username|profileLinkByUserName(class='classname') }}
     *   Using profile.gif instead of username, no class
     *   {{ username|profileLinkByUserName('image'='images/profile.gif') }}
     */
    public function profileLinkByUserName(
        string $userName,
        string $class = '',
        string $image = '',
        int $maxLength = 0,
        string $title = ''
    ): string {
        if (empty($userName)) {
            return $userName;
        }

        return $this->determineProfileLink(null, $userName, $class, $image, $maxLength, $title);
    }

    /**
     * Internal function used by profileLinkByUserId() and profileLinkByUserName().
     */
    private function determineProfileLink(
        ?int $userId = null,
        ?string $userName = null,
        string $class = '',
        string $imagePath = '',
        int $maxLength = 0,
        string $title = ''
    ): string {
        if (!isset($userId) && !isset($userName)) {
            throw new InvalidArgumentException();
        }
        if (null !== $userId) {
            $user = $this->userManager->findUserBy(['id' => $userId]);
        } else {
            $user = $this->userManager->findUserByUsername($userName);
        }
        if (!$user) {
            return $userId . $userName; // one or the other is empty
        }

        $userDisplayName = $this->profileHelper->getDisplayName($user->getUid());
        if (!$userDisplayName) {
            $userDisplayName = $user->getUname();
        }

        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';

        if (!empty($imagePath)) {
            $show = '<img src="' . htmlspecialchars($imagePath, ENT_QUOTES) . '" alt="' . htmlspecialchars($userDisplayName, ENT_QUOTES) . '" />';
        } elseif (0 < $maxLength) {
            // truncate the user name to $maxLength chars
            $length = mb_strlen($userDisplayName);
            $truncEnd = ($maxLength > $length) ? $length : $maxLength;
            $show = htmlspecialchars(s($userDisplayName)->slice(0, $truncEnd)->toString(), ENT_QUOTES);
        } else {
            $show = htmlspecialchars($userDisplayName, ENT_QUOTES);
        }
        $href = $this->profileHelper->getProfileUrl($user->getUid());
        if ('#' === $href) {
            return $userDisplayName;
        }

        if (empty($title)) {
            $title = $this->translator->trans('Profile') . ': ' . $userDisplayName;
        }

        return '<a' . $class . ' title="' . htmlspecialchars($title, ENT_QUOTES) . '" href="' . $href . '">' . $show . '</a>';
    }
}
