<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class UrlWidget implements RendererInterface
{
    public function getName()
    {
        return 'url_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        if(!isset($variables['type'])) {
            $variables['type'] = 'url';
        }
        
        return $renderer->getRender('field_widget')->render($form, $variables, $renderer);
    }
}
