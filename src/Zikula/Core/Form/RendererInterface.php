<?php

namespace Zikula\Core\Form;

use Symfony\Component\Form\FormView;

/**
 *
 */
interface RendererInterface
{
    public function render(FormView $form, $variables, FormRenderer $renderer);

    public function getName();
}
