<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 * @package ZikulaExamples_Hooks
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Example filter hook.
 *
 * Filters can be either in static or services (instanciated objects), it
 * doesn't matter.  Normally there would be just one filter per area in it's
 * own separate group/area, and not be part of the view/edit/delete bundle (group).
 *
 * Thexe handlers have no need particularly of persistence, so can be grouped into
 * a single static 'util' class for simplicity.  Alternatively, if they are complex
 * and potentially work together with other compenents they could be a service and
 * be subclasses of Zikula_HookHandler.  This is simply a matter of preference.
 *
 * This file contains a mix of real and pseudocode to 'give you the gist' of
 * how this should be implemented.  It's not intended to be a copy and paste
 * example.
 */
class Example_HookFilters
{
    /**
     * Filter hook.
     *
     * The filter receives the Zikula_View as the subject
     * (from the template that invoked it).
     *
     * Subject is the Zikula_View.
     * $hook->data is the data to be filtered (or not).
     *
     * There is nothing to return.  If the filter decides to
     * run then it should just alter the $hook->data property of the
     * event.
     *
     * @param Zikula_Hook $hook The hookable event.
     *
     * @return void
     */
    public function filter(Zikula_FilterHook $hook)
    {
        if ($this->data == 'somecondition') {
            return;
        }

        // do the actual filtering (or not)
        $hook->data = str_replace('FOO', 'BAR', $this->data);
    }
}
