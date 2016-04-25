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
 * Zikula_View insert function to dynamically get current status/error message
 *
 * This function obtains the last status message posted for this session.
 * The status message exists in one of two session variables: '_ZStatusMsg' for a
 * status message, or '_ZErrorMsg' for an error message. If both a status and an
 * error message exists then the error message is returned.
 *
 * This is is a destructive function - it deletes the two session variables
 * '_ZStatusMsg' and 'erorrmsg' during its operation.
 *
 * Available parameters:
 *   - assign:   If set, the status message is assigned to the corresponding variable instead of printed out
 *   - style, class: If set, the status message is being put in a div tag with the respective attributes
 *   - tag:      You can specify if you would like a span or a div tag
 *
 * Example
 *   {insert name='getstatusmsg'}
 *   {insert name="getstatusmsg" style="color:red;"}
 *   {insert name="getstatusmsg" class="statusmessage" tag="span"}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void
 */
function smarty_insert_getstatusmsg($params, $view)
{

    /**
     * Note: The "assign" parameter is handled by the smarty_core_run_insert_handler()
     * function in lib/vendor/Smarty/internals/core.run_insert_handler.php.
     */

    $params['class'] = (isset($params['class'])) ? $params['class'] : null;
    $params['style'] = (isset($params['style'])) ? $params['style'] : null;
    $params['tag'] = (isset($params['tag'])) ? $params['tag'] : null;

    /**
     * Prepare output.
     */
    $result = '';

    $total_messages = array();

    /**
     * Get session.
     */
    $session = $view->getContainer()->get('session');

    /**
     * Get error messages.
     */
    $messages = $session->getFlashBag()->get(Zikula_Session::MESSAGE_ERROR);

    if (count($messages) > 0) {
        /**
         * Set class for the messages.
         */
        $class = (!is_null($params['class'])) ? $params['class'] : 'alert alert-danger';

        $total_messages = $total_messages + $messages;

        /**
         * Build output of the messages.
         */
        if ((!$params['tag']) || ($params['tag'] != 'span')) {
            $params['tag'] = 'div';
        }

        $result .= '<'.$params['tag'].' class="'.$class.'"';

        if (!is_null($params['style'])) {
            $result .= ' style="'.$params['style'].'"';
        }

        $result .= '>';
        $result .= implode('<hr />', $messages);
        $result .= '</'.$params['tag'].'>';
    }
    /**
     * Get warning messages.
     */
    $messages = $session->getFlashBag()->get(Zikula_Session::MESSAGE_WARNING);

    if (count($messages) > 0) {
        /**
         * Set class for the messages.
         */
        $class = (!is_null($params['class'])) ? $params['class'] : 'alert alert-warning';

        $total_messages = $total_messages + $messages;

        /**
         * Build output of the messages.
         */
        if ((!$params['tag']) || ($params['tag'] != 'span')) {
            $params['tag'] = 'div';
        }

        $result .= '<'.$params['tag'].' class="'.$class.'"';

        if ($params['style']) {
            $result .= ' style="'.$params['style'].'"';
        }

        $result .= '>';
        $result .= implode('<hr />', $messages);
        $result .= '</'.$params['tag'].'>';
    }

    /**
     * Get status messages.
     */
    $messages = $session->getFlashBag()->get(Zikula_Session::MESSAGE_STATUS);

    if (count($messages) > 0) {
        /**
         * Set class for the messages.
         */
        $class = (!is_null($params['class'])) ? $params['class'] : 'alert alert-success';

        $total_messages = $total_messages + $messages;

        /**
         * Build output of the messages.
         */
        if ((!$params['tag']) || ($params['tag'] != 'span')) {
            $params['tag'] = 'div';
        }

        $result .= '<'.$params['tag'].' class="'.$class.'"';

        if ($params['style']) {
            $result .= ' style="'.$params['style'].'"';
        }

        $result .= '>';
        $result .= implode('<hr />', $messages);
        $result .= '</'.$params['tag'].'>';
    }

    if (empty($total_messages)) {
        return;
    }

    return $result;
}
