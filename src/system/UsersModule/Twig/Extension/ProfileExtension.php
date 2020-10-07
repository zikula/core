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

namespace Zikula\UsersModule\Twig\Extension;

use InvalidArgumentException;
use function Symfony\Component\String\s;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\UsersModule\Collector\ProfileModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class ProfileExtension extends AbstractExtension
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ProfileModuleCollector
     */
    private $profileModuleCollector;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ProfileModuleCollector $profileModuleCollector,
        TranslatorInterface $translator
    ) {
        $this->userRepository = $userRepository;
        $this->profileModuleCollector = $profileModuleCollector;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('userAvatar', [$this, 'getUserAvatar'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('profileLinkByUserId', [$this, 'profileLinkByUserId'], ['is_safe' => ['html']]),
            new TwigFilter('profileLinkByUserName', [$this, 'profileLinkByUserName'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Displays the avatar of a given user.
     *
     * @param int|string $userId The user's id or name
     */
    public function getUserAvatar($userId = 0, array $parameters = []): string
    {
        return $this->profileModuleCollector->getSelected()->getAvatar($userId, $parameters);
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
        int $userId = null,
        string $userName = null,
        string $class = '',
        string $imagePath = '',
        int $maxLength = 0,
        string $title = ''
    ): string {
        if (!isset($userId) && !isset($userName)) {
            throw new InvalidArgumentException();
        }
        /** @var UserEntity $user */
        if (null !== $userId) {
            $user = $this->userRepository->find($userId);
        } else {
            $user = $this->userRepository->findOneBy(['uname' => $userName]);
        }
        if (!$user) {
            return $userId . $userName; // one or the other is empty
        }

        $userDisplayName = $this->profileModuleCollector->getSelected()->getDisplayName($user->getUid());
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
            $show = htmlspecialchars(s($userDisplayName)->slice(0, $truncEnd)->__toString(), ENT_QUOTES);
        } else {
            $show = htmlspecialchars($userDisplayName, ENT_QUOTES);
        }
        $href = $this->profileModuleCollector->getSelected()->getProfileUrl($user->getUid());
        if ('#' === $href) {
            return $userDisplayName;
        }

        if (empty($title)) {
            $title = $this->translator->trans('Profile') . ': ' . $userDisplayName;
        }

        return '<a' . $class . ' title="' . htmlspecialchars($title, ENT_QUOTES) . '" href="' . $href . '">' . $show . '</a>';
    }
}
