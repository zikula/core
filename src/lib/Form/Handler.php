<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Base form handler class
 *
 * This is the base class to inherit from when creating your own form handlers.
 *
 * Member variables in a form handler object is persisted accross different page requests. This means
 * a member variable $this->x can be set on one request and on the next request it will still contain
 * the same value.
 *
 * A form handler will be notified of various events that happens during it's life-cycle.
 * When a specific event occurs then the corresponding event handler (class method) will be executed. Handlers
 * are named exactly like their events - this is how the framework knows which methods to call.
 *
 * The list of events is:
 *
 * - <b>initialize</b>: this event fires before any of the events for the plugins and can be used to setup
 *   the form handler. The event handler typically takes care of reading URL variables, access control
 *   and reading of data from the database.
 *
 * - <b>handleCommand</b>: this event is fired by various plugins on the page. Typically it is done by the
 *   Form_Plugin_Button plugin to signal that the user activated a button.
 */
class Form_Handler
{
    /**
     * Initialize form handler
     *
     * Typical use:
     * <code>
     * function initialize(&$render)
     * {
     *   if (!HasAccess) // your access check here
     *      return $render->setErrorMsg('No access');
     *
     *   $id = FormUtil::getPassedValue('id');
     *
     *  $data = ModUtil::apiFunc('MyModule', 'user', 'get',
     *                       array('id' => $id));
     *   if (count($data) == 0)
     *     return $render->setErrorMsg('Unknown data');
     *
     *   $render->assign($data);
     *
     *   return true;
     * }
     * </code>
     *
     * @return bool False in case of initialization errors, otherwise true. If false is returned then the
     * framework assumes that {@link pnFormRender::pnFormSetErrorMsg()} has been called with a suitable
     * error message.
     */
    public function initialize(&$render)
    {
    }

    /**
     * Command event handler
     *
     * This event handler is called when a command is issued by the user. Commands are typically something
     * that originates from a {@link pnFormButton} plugin. The passed args contains different properties
     * depending on the command source, but you should at least find a <var>$args['commandName']</var>
     * value indicating the name of the command. The command name is normally specified by the plugin
     * that initiated the command.
     * @see pnFormButton
     * @see pnFormImageButton
     */
    public function handleCommand(&$render, &$args)
    {
    }
}
