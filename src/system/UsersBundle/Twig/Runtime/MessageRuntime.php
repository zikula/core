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

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\UsersBundle\Collector\MessageBundleCollector;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class MessageRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBundleCollector $messageBundleCollector,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Display a link to a user's inbox.
     *
     * @param int|string $userId The user's id or name
     */
    public function messageInboxLink(
        $userId = null,
        bool $urlOnly = false,
        string $text = '',
        string $class = ''
    ): string {
        $url = $this->messageBundleCollector->getSelected()->getInboxUrl($userId);
        if ($urlOnly) {
            return $url;
        }
        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
        $text = !empty($text) ? htmlspecialchars($text, ENT_QUOTES) : $this->translator->trans('Inbox');

        return '<a' . $class . ' title="' . $this->translator->trans('Messages inbox') . '" href="' . $url . '">' . $text . '</a>';
    }

    /**
     * Display a link to send a message to the given user.
     *
     * @param int|string $userId The user's id or name
     */
    public function messageSendLink(
        $userId = null,
        bool $urlOnly = false,
        string $text = '',
        string $class = ''
    ): string {
        $url = $this->messageBundleCollector->getSelected()->getSendMessageUrl($userId);
        if ($urlOnly) {
            return $url;
        }
        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';

        if (!empty($text)) {
            $text = htmlspecialchars($text, ENT_QUOTES);
        } else {
            /** @var UserEntity $user */
            $user = $this->userRepository->find($userId);
            $text = null !== $user ? $user->getUname() : '';
        }

        return '<a' . $class . ' title="' . $this->translator->trans('Send a message to this user') . '" href="' . $url . '">' . $text . '</a>';
    }

    /**
     * Retrieve the total or unread message count for the given user.
     *
     * @param int|string $userId The user's id or name
     */
    public function messageCount($userId = null, bool $unreadOnly = false): int
    {
        return $this->messageBundleCollector->getSelected()->getMessageCount($userId, $unreadOnly);
    }
}
