<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

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
