<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
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
abstract class Form_Handler implements Zikula_Translatable
{
    /**
     * Translation domain.
     *
     * @var string
     */
    protected $domain;


    /**
     * Constructor.
     *
     * @param string $domain
     */
    public function __construct($domain=null)
    {
        $this->domain = $domain;
    }

    /**
     * Get translation domain.
     *
     * @return string $this->domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Initialize form handler.
     *
     * Typical use:
     * <code>
     * function initialize(&$view)
     * {
     *   if (!HasAccess) // your access check here
     *      return $view->setErrorMsg('No access');
     *
     *   $id = FormUtil::getPassedValue('id');
     *
     *  $data = ModUtil::apiFunc('MyModule', 'user', 'get',
     *                       array('id' => $id));
     *   if (count($data) == 0)
     *     return $view->setErrorMsg('Unknown data');
     *
     *   $view->assign($data);
     *
     *   return true;
     * }
     * </code>
     *
     * @param Form_View &$view Reference to Form render object.
     *
     * @return bool False in case of initialization errors, otherwise true. If false is returned then the
     * framework assumes that {@link Form_View::setErrorMsg()} has been called with a suitable
     * error message.
     */
    public function initialize(&$view)
    {
        return true;
    }

    /**
     * Command event handler.
     *
     * This event handler is called when a command is issued by the user. Commands are typically something
     * that originates from a {@link Form_Plugin_Button} plugin. The passed args contains different properties
     * depending on the command source, but you should at least find a <var>$args['commandName']</var>
     * value indicating the name of the command. The command name is normally specified by the plugin
     * that initiated the command.
     *
     * @param Form_View &$view Reference to Form render object.
     * @param array       &$args   Arguments of the command.
     *
     * @see    Form_Plugin_Button, Form_Plugin_ImageButton
     * @return void
     */
    public function handleCommand(&$view, &$args)
    {
    }

    /**
     * singular translation for modules.
     *
     * @param string $msg Message.
     *
     * @return string
     */
    public function __($msg)
    {
        return __($this->domain, $msg);
    }

    /**
     * Plural translations for modules.
     *
     * @param string  $m1 Singular.
     * @param string  $m2 Plural.
     * @param integer $n  Count.
     *
     * @return string
     */
    public function _n($m1, $m2, $n)
    {
        return _n($this->domain, $m1, $m2, $n);
    }

    /**
     * Format translations for modules.
     *
     * @param string       $msg   Message.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __f($msg, $param)
    {
        return __f($msg, $param, $this->domain);
    }

    /**
     * Format pural translations for modules.
     *
     * @param string       $m1    Singular.
     * @param string       $m2    Plural.
     * @param integer      $n     Count.
     * @param string|array $param Format parameters.
     *
     * @return string
     */
    public function __fn($m1, $m2, $n, $param)
    {
        return _fn($m1, $m2, $n, $param, $this->domain);
    }
}
