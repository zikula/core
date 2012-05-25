<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class FieldEnctype implements RendererInterface
{
    public function getName()
    {
        return 'field_enctype';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        return $form->get('multipart')? 'enctype="multipart/form-data"' : '';
    }
}
