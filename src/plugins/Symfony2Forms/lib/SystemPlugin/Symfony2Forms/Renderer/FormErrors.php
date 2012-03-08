<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;

/**
 *
 */
class FormErrors extends FieldErrors
{
    public function getName()
    {
        return 'form_errors';
    }
    
    protected function getDivClassName()
    {
        return 'z-form-validationSummary';
    }
}
