<?php

namespace SystemPlugin\Symfony2Forms\Renderer;

use Symfony\Component\Form\FormView;
use SystemPlugin\Symfony2Forms\RendererInterface;
use SystemPlugin\Symfony2Forms\FormRenderer;

/**
 *
 */
class ChoiceOptions implements RendererInterface
{
    public function getName()
    {
        return 'choice_options';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';
        
        foreach ($variables['options'] as $choice => $label) {
            if($renderer->isChoiceGroup($label)) {
                $html .= '<optgroup label="' . $choice . '">';
                
                foreach ($label as $nestedChoice => $nestedLabel) {
                    $html .= '<option value="' . $nestedChoice .'"'; 
                    
                    if($renderer->isChoiceSelected($form, $nestedChoice)) {
                        $html .= ' selected="selected"';
                    }
                    
                    $html .= '>' . $nestedLabel . '</option>';
                }
                
                $html .= '</optgroup>';
            } else {
               
                $html .= '<option value="' . $choice . '"';
                
                if($renderer->isChoiceSelected($form, $choice)) {
                    $html .= ' selected="selected"';
                }
                
                $html .= '>' . $label . '</option>';
            }
        }
        
        return $html;
    }
}
