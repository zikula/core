<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Common\Collection\Collectible;

/**
 * Class PendingContentCollectible
 */
class PendingContentCollectible
{
    /**
     * Pending item type.
     *
     * @var string
     */
    protected $type;

    /**
     * Pending item description.
     *
     * @var string
     */
    protected $description;

    /**
     * Number of pending items.
     *
     * @var integer
     */
    protected $number;

    /**
     * Route id.
     *
     * @var string
     */
    protected $route;

    /**
     * Arguments for route.
     *
     * @var array
     */
    protected $args;

    /**
     * Constructor.
     *
     * @param string  $type        Type of collectible item
     * @param string  $description Description of aggregate
     * @param integer $number      Number of items in aggregate
     * @param string  $route       Route id (to view action)
     * @param array   $args        Arguments for method
     */
    public function __construct($type, $description, $number, $route, array $args = [])
    {
        $this->type = (string)$type;
        $this->description = (string)$description;
        $this->number = (int)$number;
        $this->route = (string)$route;
        $this->args = $args;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get number.
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get route.
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get method call args.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }
}
