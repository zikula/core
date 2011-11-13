<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class FieldLabel implements RendererInterface
{
    public function getName()
    {
        return 'field_label';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<label for="' . $variables['id'] . '" ';
        
        foreach($variables['attr'] as $k => $v) { 
            $html .= $k . '="' . $v . '" ';;
        }
        
        $html .= '>' . $variables['label'] . '</label>';
        
        return $html;
    }
}
