<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '<input type="'
              . (isset($variables['type']) ? $variables['type'] : "text") . '" '
              . 'value="' . $variables['value'] . '" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer)
              . ' />';
        
        return $html;
    }
}
