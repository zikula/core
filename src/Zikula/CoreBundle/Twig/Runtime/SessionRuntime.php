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

namespace Zikula\Bundle\CoreBundle\Twig\Runtime;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;
use Twig\Extension\RuntimeExtensionInterface;

class SessionRuntime implements RuntimeExtensionInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        SessionInterface $session,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->translator = $translator;
    }

    /**
     * Display flash messages in twig template. Defaults to Bootstrap alert classes.
     *
     * <pre>
     *  {{ showflashes() }}
     *  {{ showflashes({'class': 'custom-class', 'tag': 'span'}) }}
     * </pre>
     */
    public function showFlashes(array $params = []): string
    {
        $result = '';
        $totalMessages = [];
        $messageTypeMap = [
            'error' => 'danger',
            'warning' => 'warning',
            'status' => 'success',
            'danger' => 'danger',
            'success' => 'success',
            'info' => 'info'
        ];

        foreach ($messageTypeMap as $messageType => $bootstrapClass) {
            $messages = $this->session->getFlashBag()->get($messageType);
            if (1 > count($messages)) {
                continue;
            }

            $translatedMessages = [];
            foreach ($messages as $message) {
                $translatedMessages[] = $this->translator->trans(/** @Ignore */$message);
            }

            // set class for the messages
            $class = !empty($params['class']) ? $params['class'] : "alert alert-${bootstrapClass}";
            $totalMessages += $messages;
            // build output of the messages
            if (empty($params['tag']) || ('span' !== $params['tag'])) {
                $params['tag'] = 'div';
            }
            $result .= '<' . $params['tag'] . ' class="' . $class . '"';
            if (!empty($params['style'])) {
                $result .= ' style="' . $params['style'] . '"';
            }
            $result .= '>';
            $result .= implode('<hr />', $translatedMessages);
            $result .= '</' . $params['tag'] . '>';
        }

        if (empty($totalMessages)) {
            return '';
        }

        return $result;
    }
}
