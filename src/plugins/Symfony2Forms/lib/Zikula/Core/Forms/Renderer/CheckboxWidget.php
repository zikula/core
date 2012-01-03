<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class CheckboxWidget implements RendererInterface
{
    public function getName()
    {
        return 'checkbox_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<input type="checkbox" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer);
        
        if($variables['value']) {
            $html .= ' value="' . $variables['value'] . '"';
        }
        
        if($variables['checked']) {
            $html .= ' checked="checked"';
        }
        
        $html .= ' />';
        
        return $html;
    }
}
