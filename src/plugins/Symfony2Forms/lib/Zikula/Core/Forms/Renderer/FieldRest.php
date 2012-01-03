<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class FieldRest implements RendererInterface
{
    public function getName()
    {
        return 'field_rest';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';
        
        foreach ($form as $child) {
            if (!$child->isRendered()) {
                $html .= $renderer->renderRow(array('form' => $child));
            }
        }
        
        return $html;
    }
}
