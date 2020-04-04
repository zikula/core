<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Bridge\Twig;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;
use Twig\Error\Error as TwigError;
use Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent;
use Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent;
use Zikula\ThemeModule\ThemeEvents;

class EventEnabledTwigEngine extends Environment
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * {@inheritdoc}
     *
     * This overrides the \Twig\Environment::render method in order to dispatch events both before and after rendering the content.
     *
     * It also supports TwigTemplate as name parameter.
     *
     * @throws TwigError if something went wrong like a thrown exception while rendering the template
     */
    public function render($name, array $context = []): string
    {
        $preEvent = new TwigPreRenderEvent($name, $context);
        $this->eventDispatcher->dispatch($preEvent);

        $content = parent::render($preEvent->getTemplateName(), $preEvent->getParameters());

        $postEvent = new TwigPostRenderEvent($content, $preEvent->getTemplateName(), $preEvent->getParameters());
        $this->eventDispatcher->dispatch($postEvent);

        return $postEvent->getContent();
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
