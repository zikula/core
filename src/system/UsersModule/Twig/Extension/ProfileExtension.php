<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Twig\Extension;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Collector\ProfileModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class ProfileExtension extends \Twig_Extension
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

    /**
     * ProfileExtension constructor.
     * @param UserRepositoryInterface $userRepository
     * @param ProfileModuleCollector $profileModuleCollector
     * @param TranslatorInterface $translator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ProfileModuleCollector $profileModuleCollector,
        TranslatorInterface $translator
    ) {
        $this->userRepository = $userRepository;
        $this->profileModuleCollector = $profileModuleCollector;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('profileLinkByUserId', [$this, 'profileLinkByUserId'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('profileLinkByUserName', [$this, 'profileLinkByUserName'], ['is_safe' => ['html']])
        ];
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
     *
     * @param integer $userId    The users uid
     * @param string  $class     The class name for the link (optional)
     * @param string  $image     Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    public function profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0)
    {
        if (empty($userId) || (int)$userId < 1) {
            return $userId;
        }

        return $this->determineProfileLink((int)$userId, null, $class, $image, $maxLength);
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
     *
     * @param string  $userName  The users name
     * @param string  $class     The class name for the link (optional)
     * @param string  $image     Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    public function profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0)
    {
        if (empty($userName)) {
            return $userName;
        }

        return $this->determineProfileLink(null, $userName, $class, $image, $maxLength);
    }

    /**
     * Internal function used by profileLinkByUserId() and profileLinkByUserName().
     *
     * @param integer $userId    The users uid
     * @param string  $userName  The users name
     * @param string  $class     The class name for the link (optional)
     * @param string  $imagePath Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    private function determineProfileLink($userId = null, $userName = null, $class = '', $imagePath = '', $maxLength = 0)
    {
        if (!isset($userId) && !isset($userName)) {
            throw new \InvalidArgumentException();
        }
        if ($userId) {
            $user = $this->userRepository->find($userId);
        } else {
            $user = $this->userRepository->findOneBy(['uname' => $userName]);
        }
        if (!$user) {
            return $userId . $userName; // one or the other is empty
        }

        $userDisplayName = $this->profileModuleCollector->getSelected()->getDisplayName($user->getUid());
        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';

        if (!empty($imagePath)) {
            $show = '<img src="' . htmlspecialchars($imagePath, ENT_QUOTES) . '" alt="' . htmlspecialchars($userDisplayName, ENT_QUOTES) . '" />';
        } elseif ($maxLength > 0) {
            // truncate the user name to $maxLength chars
            $length = strlen($userDisplayName);
            $truncEnd = ($maxLength > $length) ? $length : $maxLength;
            $show  = htmlspecialchars(substr($userDisplayName, 0, $truncEnd), ENT_QUOTES);
        } else {
            $show = htmlspecialchars($userDisplayName, ENT_QUOTES);
        }
        $href = $this->profileModuleCollector->getSelected()->getProfileUrl($user->getUid());

        return '<a' . $class . ' title="' . ($this->translator->__('Profile')) . ': ' . htmlspecialchars($userDisplayName, ENT_QUOTES) . '" href="' . $href . '">' . $show . '</a>';
    }
}
