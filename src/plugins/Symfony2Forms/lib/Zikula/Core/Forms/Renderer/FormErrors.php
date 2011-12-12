<?php

namespace Zikula\Core\Forms\Renderer;

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
