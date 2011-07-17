<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
interface SystemPlugin_Symfony2Forms_RendererInterface
{
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer);
    
    public function getName();
}
