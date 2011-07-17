<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldEnctype implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_enctype';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        return $form->get('multipart')? 'enctype="multipart/form-data"' : '';
    }
}
