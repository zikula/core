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

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\UsersModule\Collector\MessageModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class MessageExtension extends AbstractExtension
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var MessageModuleCollector
     */
    private $messageModuleCollector;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        UserRepositoryInterface $userRepository,
        MessageModuleCollector $messageModuleCollector,
        TranslatorInterface $translator
    ) {
        $this->userRepository = $userRepository;
        $this->messageModuleCollector = $messageModuleCollector;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('messageSendLink', [$this, 'messageSendLink'], ['is_safe' => ['html']])
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('messageInboxLink', [$this, 'messageInboxLink'], ['is_safe' => ['html']]),
            new TwigFunction('messageCount', [$this, 'messageCount'])
        ];
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
        $url = $this->messageModuleCollector->getSelected()->getInboxUrl($userId);
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
        $url = $this->messageModuleCollector->getSelected()->getSendMessageUrl($userId);
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
    public function messageCount(
        $userId = null,
        bool $unreadOnly = false
    ): int {
        return $this->messageModuleCollector->getSelected()->getMessageCount($userId, $unreadOnly);
    }
}
