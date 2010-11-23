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

/**
 * Zikula display hook reponse class.
 *
 * Hook handlers should return one of these.
 */
class Zikula_Response_DisplayHook
{
    /**
     * Name.
     * 
     * @var string
     */
    protected $name;

    /**
     * Zikula_View
     * 
     * @var Zikula_View
     */
    protected $view;

    /**
     * Template.
     *
     * @var string
     */
    protected $template;

    /**
     * Constructor.
     *
     * @param string      $name     Name of this response.
     * @param Zikula_View $view     Zikula View instance.
     * @param string      $template Template, in the context of the Zikula_View.
     */
    public function __construct($name, Zikula_View $view, $template)
    {
        $this->name = $name;
        $this->view = $view;
        $this->template = $template;
    }

    /**
     * Get name property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name property.
     *
     * @param string $name Name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * Get Zikula_View.
     *
     * @return Zikula_View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Zikula_View.
     *
     * @return void
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Get template property.
     *
     * Template name.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set template property.
     *
     * @param string $template Template name.
     *
     * @return void
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Render the hook's output.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->view->fetch($this->template);
    }
}