<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TimeDataCollector.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TimeDataCollector extends DataCollector
{
    protected $kernel;

    public function __construct(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'start_time' => (null !== $this->kernel ? $this->kernel->getStartTime() : $_SERVER['REQUEST_TIME']) * 1000,
            'events'     => array(),
        );
    }

    /**
     * Sets the request events.
     *
     * @param array $event The request events
     */
    public function setEvents(array $events)
    {
        foreach ($events as $event) {
            $event->ensureStopped();
        }

        $this->data['events'] = $events;
    }

    /**
     * Gets the request events.
     *
     * @return array The request events
     */
    public function getEvents()
    {
        return $this->data['events'];
    }

    /**
     * Gets the request elapsed time.
     *
     * @return integer The elapsed time
     */
    public function getTotalTime()
    {
        $values = array_values($this->data['events']);
        $lastEvent = $values[count($values) - 1];

        return $lastEvent->getOrigin() + $lastEvent->getEndTime() - $this->data['start_time'];
    }

    /**
     * Gets the initialization time.
     *
     * This is the time spent until the beginning of the request handling.
     *
     * @return integer The elapsed time
     */
    public function getInitTime()
    {
        return $this->data['events']['section']->getOrigin() - $this->getStartTime();
    }

    /**
     * Gets the request time.
     *
     * @return integer The time
     */
    public function getStartTime()
    {
        return $this->data['start_time'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'time';
    }
}
