<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
