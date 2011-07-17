<?php

use Symfony\Component\Form\FormView;

/**
 *
 */
class SystemPlugin_Symfony2Forms_Renderer_ChoiceWidget implements SystemPlugin_Symfony2Forms_RendererInterface
{
    public function getName()
    {
        return 'choice_widget';
    }
    
    public function render(FormView $form, $variables, SystemPlugin_Symfony2Forms_FormRenderer $renderer)
    {
        $html = '';
        
        if ($variables['expanded']) {
            $html .= '<div ' . $renderer->getRender('container_attributes')->render($form, $variables, $renderer) . '>';
            
            foreach($form as $child) {
                $html .= $renderer->renderWidget(array('form' => $child));
                $html .= $renderer->renderLabel(array('form' => $child));
            }
            
            $html .= '</div>';
        } else {
            $html .= '<select ';
            $html .= $renderer->getRender('attributes')->render($form, $variables, $renderer);
            
            if ($variables['multiple']) {
                $html .= ' multiple="multiple"';
            }
            
            $html .= '>';
            
            if (null !== $variables['empty_value']) {
                $html .= '<option value="">' . $variables['empty_value'] . '</option>';
            }
            
            if (count($variables['preferred_choices']) > 0) {
                $html .= $renderer->getRender('choice_options')->render(null, array('options' => $variables['preferred_choices']), $renderer);
                $html .= '<option disabled="disabled">' . $variables['separator'] . '</option>';
            }
            
            $html .= $renderer->getRender('choice_options')->render(null, array('options' => $choices), $renderer);
            $html .= '</select>';
        }
        
        return $html;
    }
}
