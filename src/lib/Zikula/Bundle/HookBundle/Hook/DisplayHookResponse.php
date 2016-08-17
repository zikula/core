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

use Zikula_View;

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
     * @param string|Zikula_View $content Response content (or Zikula View instance @deprecated)
     * @param string $template Template, in the context of the Zikula_View. @deprecated argument
     *   The third argument in the method will be removed in Core-2.0
     */
    public function __construct($area, $content, $template = null)
    {
        if (is_object($content) && ($content instanceof Zikula_View) && !empty($template)) {
            // This is a BC layer to allow old construction methods to work.
            // remove this if condition in Core-2.0
            $content = $content->fetch($template);
        }
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
