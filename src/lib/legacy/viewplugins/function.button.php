<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Display an core image form submission button using either the <button> or the <input> HTML element.
 *
 * This tag calls the img tag to determine the full path of the image
 * for the src attribute of the img element within the button element, or
 * for the src attribute of the input element.
 *
 * <i>BEWARE: Internt Explorer 6.x does NOT work especially well with <button> tags!</i>
 *
 * Available attributes:
 *  - src       (string)    The file name of the image. The full path of the image
 *                          will be determined by the smarty_function_img function.
 *  - set       (string)    The name of the image set from which to retrieve the
 *                          image file (the name of a subdirectory under /images/icons).
 *  - mode      (string)    if set, the type of HTML element to be used (optional,
 *                          default: button). Values = [button|input]
 *  - type      (string)    if set, the type of button that will be generated
 *                          (optional, default: submit, used only if mode is set to 'button')
 *  - name      (string)    if set, the name of button that will be generated as
 *                          the name attribute on the button or input element
 *                          (optional, default: value of 'type' parameter)
 *  - value     (string)    if set, the value that will be generated as the
 *                          value attribute on the button or input element (optional,
 *                          however should be set if mode is input)
 *  - id        (string)    if set, the value of the id attribute on the button
 *                          or input element (optional)
 *  - class     (string)    if set, the value of the class attribute on the
 *                          button or input element (optional)
 *  - alt       (string)    if set, the value for the alt attribute. If mode is
 *                          'button' then the alt attribute is generated for
 *                          the img element embedded in the button element. If
 *                          mode is 'input' then the alt attribute is generated
 *                          for the input element. (optional)
 *  - title     (string)    if set, the value for the title attribute of the
 *                          button or input element. (optional)
 *  - text      (string)    if set, the button tag surrounds this string
 *  - assign    (string)    If set, the results are assigned to the corresponding
 *                          template variable instead of being returned to the template (optional)
 *
 * Examples:
 *
 * Display a submit button with button_ok.png (a green check mark) from the set of
 * small icons (/images/icons/small) with the <button ...> HTML element.
 *
 * <samp>{button src='button_ok.png' set='small'}</samp>
 *
 * Display a cancel button with button_cancel.png (a red 'X') from the set of
 * extra small icons (/images/icons/extrasmall) with the <button ...> HTML element.
 *
 * <samp>{button src='button_cancel.png' set='extrasmall' type='cancel'}</samp>
 *
 * Display a submit button with button_cancel.png (a red 'X') from the set of
 * medium icons (/images/icons/medium) and a value of
 * 'cancel' with the <input ...> HTML element. The id attribute of the input
 * element is set to 'cancelbutton'.
 *
 * <samp>{button src='button_cancel.png' set='medium' mode='input' value='cancel' id='cancelbutton'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return string The rendered <button ...><img ...></button> or <input ...>
 *                element for the form button
 */
function smarty_function_button($params, Zikula_View $view)
{
    // we're going to make use of pnimg for path searching
    require_once $view->_get_plugin_filepath('function', 'img');

    if (isset($params['src']) && !isset($params['set'])) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['smarty_function_button', 'set']));

        return false;
    }
    $type = isset($params['type'])    ? $params['type'] : 'submit';
    $mode = isset($params['mode'])    ? $params['mode'] : 'button';
    if (isset($params['name'])) {
        $name = ' name="'.DataUtil::formatForDisplay($params['name']).'"';
    } else {
        $name = ' name="'.DataUtil::formatForDisplay($type).'"';
    }
    if (isset($params['value'])) {
        $value = ' value="'.DataUtil::formatForDisplay($params['value']).'"';
    } else {
        $value = '';
    }
    if (isset($params['id'])) {
        $id = ' id="'.DataUtil::formatForDisplay($params['id']).'"';
    } else {
        $id = '';
    }
    $class = '';
    if (isset($params['class'])) {
        $class .= ' class="'.DataUtil::formatForDisplay($params['class']).'"';
    }
    if (isset($params['text'])) {
        $text = ' ' . DataUtil::formatForDisplay($params['text']);
    } else {
        $text = '';
    }

    $title = (isset($params['title']) ? $params['title'] : '');
    $alt = (isset($params['alt']) ? $params['alt'] : '');

    // call the img plugin and work out the src from the assigned template vars
    $img = '';
    $imgsrc = '';
    if (isset($params['src'])) {
        smarty_function_img([
            'assign' => 'buttonsrc',
            'src' => $params['src'],
            'set' => $params['set'],
            'modname' => 'core'
        ], $view);
        $imgvars = $view->get_template_vars('buttonsrc');
        $imgsrc = $imgvars['src'];
        $img = '<img src="'.DataUtil::formatForDisplay($imgsrc).'" alt="'.DataUtil::formatForDisplay($alt).'" />';
    }

    // form the button html
    if ($mode == 'button') {
        $return = '<button'.$id.$class.' type="'.DataUtil::formatForDisplay($type).
        '"'.$name.$value.' title="'.DataUtil::formatForDisplay($title).'">'.$img.$text.'</button>';
    } else {
        $return = '<input'.$id.$class.' type="image"'.$name.$value.' title="'.
        DataUtil::formatForDisplay($title).'" src="'.DataUtil::formatForDisplay($imgsrc).
        '" alt="'.DataUtil::formatForDisplay($alt).'" />';
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $return);
    } else {
        return $return;
    }
}
