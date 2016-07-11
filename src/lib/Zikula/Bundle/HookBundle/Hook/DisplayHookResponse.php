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
     * @var string The rendered response
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param string $area Name of this response.
     * @param string|Zikula_View $view string or Zikula View instance.
     * @param string $template Template, in the context of the Zikula_View. @deprecated argument
     *   The third argument in the method will be removed in Core-2.0.
     */
    public function __construct($area, $view, $template = null)
    {
        $this->area = $area;
        if (is_object($view) && ($view instanceof Zikula_View) && !empty($template)) {
            // This is a BC layer to allow old construction methods to work.
            // remove this check in Core-2.0 and simply set the view
            $this->view = $view->fetch($template);
        } else {
            $this->view = $view;
        }
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
     * Render the hook's output.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->view;
    }
}
