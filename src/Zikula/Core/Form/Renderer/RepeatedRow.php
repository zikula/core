<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class RepeatedRow implements RendererInterface
{
    public function getName()
    {
        return 'repeated_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        return $renderer->getRender('field_rows')->render($form, $variables, $renderer);
    }
}
