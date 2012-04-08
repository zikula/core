<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class RadioWidget implements RendererInterface
{
    public function getName()
    {
        return 'radio_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<input type="radio" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer)
              . ' value="' . $variables['value'] . '"';
        
        if($variables['checked']) {
            $html .= ' checked="checked"';
        }
        
        $html .= ' />';
        
        return $html;
    }
}
