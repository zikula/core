<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FormWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'form_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '<div ' . $renderer->getRender('container_attributes')->render($form, $variables, $renderer) . '>'
              . $renderer->getRender('field_rows')->render($form, $variables, $renderer)
              . $renderer->renderRest(array('form' => $form))
              . '</div>';
        
        return $html;
    }
}
