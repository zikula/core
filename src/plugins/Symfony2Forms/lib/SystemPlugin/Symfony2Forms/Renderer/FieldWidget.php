<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class FieldWidget implements RendererInterface
{
    public function getName()
    {
        return 'field_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $errorClass = !empty($variables['errors']) ? 'z-form-error' : '';
        
        if(isset($variables['attr']['class'])) {
            $variables['attr']['class'] .= ' z-form-text ' . $errorClass;
        } else {
            $variables['attr']['class'] = 'z-form-text ' . $errorClass;
        }
        
        $html = '<input type="'
              . (isset($variables['type']) ? $variables['type'] : "text") . '" '
              . 'value="' . $variables['value'] . '" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer)
              . ' />';
        
        return $html;
    }
}
