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

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Error\Error as TwigError;
use Twig\Template as TwigTemplate;
use Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent;
use Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent;
use Zikula\ThemeModule\ThemeEvents;

class EventEnabledTwigEngine extends TwigEngine
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @required
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     *
     * This overrides the TwigEngine::render method in order to dispatch events both before and after rendering the content.
     *
     * It also supports TwigTemplate as name parameter.
     *
     * @throws TwigError if something went wrong like a thrown exception while rendering the template
     */
    public function render($name, array $parameters = [])
    {
        $preEvent = new TwigPreRenderEvent($name, $parameters);
        $this->eventDispatcher->dispatch(ThemeEvents::PRE_RENDER, $preEvent);

        $content = parent::render($preEvent->getTemplateName(), $preEvent->getParameters());

        $postEvent = new TwigPostRenderEvent($content, $preEvent->getTemplateName(), $preEvent->getParameters());
        $this->eventDispatcher->dispatch(ThemeEvents::POST_RENDER, $postEvent);

        return $postEvent->getContent();
    }
}
