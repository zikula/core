<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class FieldRow implements RendererInterface
{
    public function getName()
    {
        return 'field_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        return '<div class="z-formrow">'
              . $renderer->renderLabel(array('form' => $form, 
                                             'label' => isset($variables['label'])? $variables['label'] : null))
              . $renderer->renderWidget(array('form' => $form))
              . $renderer->renderErrors(array('form' => $form))
              . '</div>';
    }
}
