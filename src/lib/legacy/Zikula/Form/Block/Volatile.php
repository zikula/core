<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 * @subpackage Zikula_Form_Block
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Volatile block container
 *
 * This block is a hack, a not so elegant solution, to situations where you need to put
 * Zikula_Form_View plugins inside conditional smarty tags like if-then-else and foreach. You can
 * get into problems if you make templates like this:
 * <code>
 *  {foreach from=... item=...}
 *    {formtextinput ...}
 *    {formbutton ...}
 *  {/foreach}
 * </code>
 * This is because the number of plugins on the page may change from one page to another
 * due to changing conditions or amount of items in the foreach loop: on the first page
 * you might have 5 iterations, whereas on postback you suddenly have 6. What should then
 * be done to the missing (or excess) persisted plugin data on postback? The answer is:
 * Zikula_Form_View cannot handle this - your code will break!
 *
 * So you need to tell Zikula_Form_View that the block inside the foreach tags is volatile - Zikula_Form_View
 * should not try to save the state of the plugins inside the foreach loop. This is done
 * with the volatile block:
 * <code>
 *  {formvolatile}
 *  {foreach from=... item=...}
 *    {formtextinput ...}
 *    {formbutton ...}
 *  {/foreach}
 *  {/formvolatile}
 * </code>
 * This disables the ability to persist data in the Zikula_Form_View plugins, but does save you
 * from trouble in some situations.
 *
 * You don't need the volatile block if you can guarantee that the number of elements will
 * be the same always.
 */
class Zikula_Form_Block_Volatile extends Zikula_Form_AbstractPlugin
{
    /**
     * Says that this content is volatile.
     *
     * @var integer
     */
    public $volatile = 1;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view    Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Zikula_View plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    function create(Zikula_Form_View $view, &$params)
    {
    }
}
