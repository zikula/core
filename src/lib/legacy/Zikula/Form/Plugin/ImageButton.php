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
 * Image button.
 *
 * This button works like a normal {@link Zikula_Form_Plugin_Button} with the exception
 * that it displays a clickable image instead of a text button. It further
 * more returns the X and Y coordinate of the click position in the image.
 *
 * The command event arguments contains four elements:
 * - commandName: command name
 * - commandArgument: command argument
 * - posX: X position of click
 * - posY: Y position of click
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_ImageButton extends Zikula_Form_Plugin_Button
{
    /**
     * Image URL.
     *
     * The URL pointing to the image for the button.
     *
     * @var string
     */
    public $imageUrl;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    /**
     * Render event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return string The rendered output
     */
    public function render(Zikula_Form_View $view)
    {
        $idHtml = $this->getIdHtml();

        $fullName = $this->id . '_' . $this->commandName;

        $onclickHtml = '';
        if ($this->confirmMessage != null) {
            $msg = $view->translateForDisplay($this->confirmMessage) . '?';
            $onclickHtml = " onclick=\"return confirm('$msg');\"";
        }

        $text = $view->translateForDisplay($this->text);
        $imageUrl = $this->imageUrl;

        $attributes = $this->renderAttributes($view);

        $result = "<input{$idHtml} name=\"{$fullName}\" type=\"image\" alt=\"{$text}\" value=\"{$text}\" src=\"{$imageUrl}\"{$onclickHtml}{$attributes} />";

        return $result;
    }

    /**
     * Decode event handler for actions that generate a postback event.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     *
     * @return boolean
     */
    public function decodePostBackEvent(Zikula_Form_View $view)
    {
        $fullNameX = $this->id . '_' . $this->commandName . '_x';
        $fullNameY = $this->id . '_' . $this->commandName . '_y';

        if (isset($_POST[$fullNameX])) {
            $args = [
                'commandName' => $this->commandName,
                'commandArgument' => $this->commandArgument,
                'posX' => (int)$_POST[$fullNameX],
                'posY' => (int)$_POST[$fullNameY]
            ];
            if (!empty($this->onCommand)) {
                if ($view->raiseEvent($this->onCommand, $args) === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
