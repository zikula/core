<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

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
