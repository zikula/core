<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class FieldRows implements RendererInterface
{
    public function getName()
    {
        return 'field_rows';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = $renderer->renderErrors(array('form' => $form));
        
        foreach ($form as $child) {
            $html .= $renderer->renderRow(array('form' => $child));
        }
        
        return $html;
    }
}
