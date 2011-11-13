<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class PrototypeRow implements RendererInterface
{
    public function getName()
    {
        return 'prototyp_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<script type="text/html" id="' . $variables['proto_id'] . '">'
              . $renderer->renderRow(array('form' => $form))
              . '</script>';
        
        return $html;
    }
}
