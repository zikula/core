<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Component\HttpKernel\Debug;

/**
 * Stopwatch provides a way to profile code.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Stopwatch
{
    private $waiting;
    private $sections;
    private $events;
    private $origin;

    /**
     * Starts a new section.
     */
    public function startSection()
    {
        if ($this->events) {
            $this->start('section.child', 'section');
            $this->waiting[] = array($this->events, $this->origin);
            $this->events = array();
        }

        $this->origin = microtime(true) * 1000;

        $this->start('section');
    }

    /**
     * Stops the last started section.
     *
     * @param string $id The identifier of the section
     */
    public function stopSection($id)
    {
        $event = $this->stop('section');

        if ($id) {
            $this->sections[$id] = $this->events;
        }

        if ($this->waiting) {
            list($this->events, $this->origin) = array_pop($this->waiting);
            $this->stop('section.child');
        } else {
            $this->origin = null;
            $this->events = array();
        }
    }

    /**
     * Starts an event.
     *
     * @param string $name     The event name
     * @param string $category The event category
     *
     * @return StopwatchEvent A StopwatchEvent instance
     */
    public function start($name, $category = null)
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = new StopwatchEvent($this->origin, $category);
        }
        $this->events[$name]->start();

        return $this->events[$name];
    }

    /**
     * Stops an event.
     *
     * @param string $name The event name
     *
     * @return StopwatchEvent A StopwatchEvent instance
     */
    public function stop($name)
    {
        if (!isset($this->events[$name])) {
            throw new \LogicException(sprintf('Event "%s" is not started.', $name));
        }

        $this->events[$name]->stop();

        return $this->events[$name];
    }

    /**
     * Stops then restart an event.
     *
     * @param string $name The event name
     *
     * @return StopwatchEvent A StopwatchEvent instance
     */
    public function lap($name)
    {
        $this->stop($name);

        return $this->start($name);
    }

    /**
     * Gets all events for a given section.
     *
     * @param string $id A section identifier
     *
     * @return StopwatchEvent[] An array of StopwatchEvent instances
     */
    public function getSectionEvents($id)
    {
        return isset($this->sections[$id]) ? $this->sections[$id] : array();
    }
}
