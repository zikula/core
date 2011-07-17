<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_CheckboxWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'checkbox_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '<input type="checkbox" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer);
        
        if($variables['value']) {
            $html .= ' value="' . $variables['value'] . '"';
        }
        
        if($variables['checked']) {
            $html .= ' checked="checked"';
        }
        
        $html .= ' />';
        
        return $html;
    }
}
