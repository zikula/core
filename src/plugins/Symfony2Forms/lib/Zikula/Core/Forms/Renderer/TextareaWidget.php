<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class TextareaWidget implements RendererInterface
{
    public function getName()
    {
        return 'textarea_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $errorClass = !empty($variables['errors']) ? 'z-form-error' : '';
        
        if(isset($variables['attr']['class'])) {
            $variables['attr']['class'] .= $errorClass;
        } else {
            $variables['attr']['class'] = $errorClass;
        }
        
        $html = '<textarea '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer)
              . '>'
              . $variables['value']
              . '</textarea>';
        
        return $html;
    }
}
