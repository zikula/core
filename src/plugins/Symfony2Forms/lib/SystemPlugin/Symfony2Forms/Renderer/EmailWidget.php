<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class EmailWidget implements RendererInterface
{
    public function getName()
    {
        return 'email_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'email';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
