<?php
/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
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
     * (from the template that invoked it).  For convenience the caller's name
     * is also additionally logged in the $event['caller'] although this could
     * be easily derived from the Zikula_View.
     *
     * Subject is the Zikula_View.
     * args[caller] the module who notified of this event.
     * $event->data is the data to be filtered (or not).
     *
     * There is nothing to return.  If the filter decides to
     * run then it should just alter the $event->data property of the
     * event.
     *
     * @param Zikula_Event $event The hookable event.
     *
     * @return void
     */
    public static function filter(Zikula_Event $event)
    {
        $view = $event->getSubject(); // Zikula_View, if needed.
        if (somecontition) {
            return;
        }

        // do the actual filtering (or not)
        $event->data = str_replace('FOO', 'BAR', $this->data);
    }
}
