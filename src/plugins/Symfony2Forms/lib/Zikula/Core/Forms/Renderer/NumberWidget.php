<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class NumberWidget implements RendererInterface
{
    public function getName()
    {
        return 'number_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'text';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
