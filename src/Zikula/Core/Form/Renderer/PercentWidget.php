<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class PercentWidget implements RendererInterface
{
    public function getName()
    {
        return 'percent_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'text';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer) . ' %';
    }
}
