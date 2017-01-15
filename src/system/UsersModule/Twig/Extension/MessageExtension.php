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

use Zikula\Bundle\CoreBundle\Twig;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Collector\MessageModuleCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class MessageExtension extends \Twig_Extension
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

    /**
     * ProfileExtension constructor.
     * @param UserRepositoryInterface $userRepository
     * @param MessageModuleCollector $messageModuleCollector
     * @param TranslatorInterface $translator
     */
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
            new \Twig_SimpleFilter('messageSendLink', [$this, 'messageSendLink'], ['is_safe' => ['html']])
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('messageInboxLink', [$this, 'messageInboxLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('messageCount', [$this, 'messageCount'])
        ];
    }

    /**
     * Display a link to a uid's inbox
     * @param null $uid defaults to current user
     * @param string $text defaults to 'Inbox'
     * @param bool $urlOnly default false
     * @param string $class
     * @return string
     */
    public function messageInboxLink($uid = null, $urlOnly = false, $text = '', $class = '')
    {
        $url = $this->messageModuleCollector->getSelected()->getInboxUrl($uid);
        if ($urlOnly) {
            return $url;
        }
        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
        $text = !empty($text) ? htmlspecialchars($text, ENT_QUOTES) : $this->translator->__('Inbox');

        return '<a' . $class . ' title="' . $this->translator->__('Messages inbox') . '" href="' . $url . '">' . $text . '</a>';
    }

    /**
     * Display a link to send a message to the uid
     * @param null $uid
     * @param bool $urlOnly
     * @param string $text
     * @param string $class
     * @return string
     */
    public function messageSendLink($uid = null, $urlOnly = false, $text = '', $class = '')
    {
        $url = $this->messageModuleCollector->getSelected()->getSendMessageUrl($uid);
        if ($urlOnly) {
            return $url;
        }
        $class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
        $text = !empty($text) ? htmlspecialchars($text, ENT_QUOTES) : $this->userRepository->find($uid)->getUname();

        return '<a' . $class . ' title="' . $this->translator->__('Send a message to this user') . '" href="' . $url . '">' . $text . '</a>';

    }

    /**
     * Retrieve the total or unread message count for uid
     * @param null $uid
     * @param bool $unreadOnly
     * @return int
     */
    public function messageCount($uid = null, $unreadOnly = false)
    {
        return $this->messageModuleCollector->getSelected()->getMessageCount($uid, $unreadOnly);
    }
}
