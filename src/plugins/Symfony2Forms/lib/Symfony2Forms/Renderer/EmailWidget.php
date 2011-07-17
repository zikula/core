<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_EmailWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'email_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'email';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
