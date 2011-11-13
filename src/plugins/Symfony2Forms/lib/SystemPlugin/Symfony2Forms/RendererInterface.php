<?php

namespace SystemPlugin\Symfony2Forms;

use Symfony\Component\Form\FormView;

/**
 *
 */
interface RendererInterface
{
    public function render(FormView $form, $variables, FormRenderer $renderer);
    
    public function getName();
}
