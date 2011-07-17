<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldRows implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_rows';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = $renderer->renderErrors(array('form' => $form));
        
        foreach ($form as $child) {
            $html .= $renderer->renderRow(array('form' => $child));
        }
        
        return $html;
    }
}
