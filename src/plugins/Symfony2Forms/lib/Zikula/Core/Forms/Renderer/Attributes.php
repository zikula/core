<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class Attributes implements RendererInterface
{
    public function getName()
    {
        return 'attributes';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = 'id="' . $variables['id'] . '" ';
        $html .= 'name="' . $variables['full_name'] . '" ';
        
        if ($variables['read_only'])
            $html .= 'disabled="disabled" ' ;
        
        if ($variables['required'])
            $html .= 'required="required" ';
        
        if ($variables['max_length'])
            $html .= 'maxlength="' . $variables['max_length'] .'" ';
        
        if ($variables['pattern'])
            $html .= 'pattern="' . $variables['pattern'] .'" ';

        foreach($variables['attr'] as $k => $v) { 
            $html .= $k . '="' . $v . '" ';
        }
        
        return $html;
    }
}
