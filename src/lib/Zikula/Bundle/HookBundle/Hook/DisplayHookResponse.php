<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

/**
 * Zikula display hook response class.
 *
 * Hook handlers should return one of these.
 */
class DisplayHookResponse
{
    /**
     * @var string the area name
     */
    protected $area;

    /**
     * @var string The response content
     */
    protected $content;

    /**
     * Constructor.
     *
     * @param string $area Name of this response
     * @param string $content Response content
     */
    public function __construct($area, $content)
    {
        $this->content = $content;
        $this->area = $area;
    }

    /**
     * Get area property.
     *
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Get the response content.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->content;
    }
}
