<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldLabel implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_label';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '<label for="' . $variables['id'] . '" ';
        
        foreach($variables['attr'] as $k => $v) { 
            $html .= $k . '="' . $v . '" ';;
        }
        
        $html .= '>' . $variables['label'] . '</label>';
        
        return $html;
    }
}
