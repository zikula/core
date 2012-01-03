<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class HiddenWidget implements RendererInterface
{
    public function getName()
    {
        return 'hidden_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'hidden';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
