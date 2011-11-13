<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class HiddenRow implements RendererInterface
{
    public function getName()
    {
        return 'hidden_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        return $renderer->renderWidget(array('form' => $form));
    }
}
