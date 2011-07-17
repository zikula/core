<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldRow implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_row';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        return '<div>'
              . $renderer->renderLabel(array('form' => $form, 
                                             'label' => isset($variables['label'])? $variables['label'] : null))
              . $renderer->renderErrors(array('form' => $form))
              . $renderer->renderWidget(array('form' => $form))
              . '</div>';
    }
}
