<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class ChoiceWidget implements RendererInterface
{
    public function getName()
    {
        return 'choice_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
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
                $html .= $renderer->getRender('choice_options')->render($form, array('options' => $variables['preferred_choices']), $renderer);
                $html .= '<option disabled="disabled">' . $variables['separator'] . '</option>';
            }
            
            $html .= $renderer->getRender('choice_options')->render($form, array('options' => $variables['choices']), $renderer);
            $html .= '</select>';
        }
        
        return $html;
    }
}
