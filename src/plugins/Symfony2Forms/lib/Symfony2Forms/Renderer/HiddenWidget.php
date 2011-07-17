<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_HiddenWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'hidden_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'hidden';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
