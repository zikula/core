<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_HiddenRow implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'hidden_row';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        return $renderer->renderWidget(array('form' => $form));
    }
}
