<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View function to get the site's page render time
 *
 * Available parameters:
 *  - assign      if set, the message will be assigned to this variable
 *  - round       if the, the time will be rounded to this number of decimal places
 *                (optional: default 2)
 *
 * Example
 * {pagerendertime} outputs 'Page created in 0.18122792243958 seconds.'
 *
 * {pagerendertime round=2} outputs 'Page created in 0.18 seconds.'
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The page render time in seconds.
 */
function smarty_function_pagerendertime($params, Zikula_View $view)
{
    // show time to render
    if ($view->getContainer()->getParameter('pagerendertime')) {
        // calcultate time to render
        $dbg_totaltime = $view->getContainer()->get('zikula')->getUptime();

        $round = isset($params['round']) ? $params['round'] : 7;
        $dbg_totaltime = round($dbg_totaltime, $round);

        if (isset($params['assign'])) {
            $view->assign('rendertime', $dbg_totaltime);
        } else {
            // load language files
            $message = '<div class="z-pagerendertime" style="text-align:center;">' . __f('Page generated in %s seconds.', $dbg_totaltime) . '</div>';

            return $message;
        }
    }
}
