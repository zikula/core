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

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\Core\Event\GenericEvent;

class EventDispatcherExtension extends AbstractExtension
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * EventDispatcherExtension constructor.
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('dispatchEvent', [$this, 'dispatchEvent']),
        ];
    }

    /**
     * @param string $name
     * @param GenericEvent|null $providedEvent
     * @param null $subject
     * @param array $arguments
     * @param null $data
     * @return mixed
     */
    public function dispatchEvent($name, GenericEvent $providedEvent = null, $subject = null, array $arguments = [], $data = null)
    {
        $event = $providedEvent ?? new GenericEvent($subject, $arguments, $data);
        $this->dispatcher->dispatch($name, $event);

        return $event->getData();
    }
}
