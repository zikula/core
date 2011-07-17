<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldRest implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_rest';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '';
        
        foreach ($form as $child) {
            if (!$child->isRendered()) {
                $html .= $renderer->renderRow(array('form' => $child));
            }
        }
        
        return $html;
    }
}
