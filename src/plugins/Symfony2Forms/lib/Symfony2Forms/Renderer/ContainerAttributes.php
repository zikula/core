<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_ContainerAttributes implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'container_attributes';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = 'id="' . $variables['id'] . '" ';

        foreach($variables['attr'] as $k => $v) { 
            $html .= $k . '="' . $v . '" ';
        }
        
        return $html;
    }
}
