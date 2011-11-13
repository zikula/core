<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

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
