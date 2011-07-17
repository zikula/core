<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_FieldErrors implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'field_errors';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '';
        
        if ($variables['errors']) {
            $html .= '<ul>';
            
            foreach ($variables['errors'] as $error) {
                $html .= '<li>' . $error->getMessageTemplate() . '</li>'; //TODO: $error->getMessageParameters()
            }
            
            $html .= '</ul>';
        }
        
        return $html;
    }
}
