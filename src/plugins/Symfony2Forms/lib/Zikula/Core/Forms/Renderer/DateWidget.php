<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class DateWidget implements RendererInterface
{
    public function getName()
    {
        return 'date_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';
        
        if ($variables['widget'] == 'single_text') {
            $html .= $renderer->getRender('field_widget')->render($form, $variables, $renderer);
        } else {
            $html .= '<div ' . $renderer->getRender('container_attributes')->render($form, $variables, $renderer) . '>';
            
            $html .= str_replace(array('{{ year }}', '{{ month }}', '{{ day }}'), array(
                            $renderer->renderWidget(array('form' => $form['year'])),
                            $renderer->renderWidget(array('form' => $form['month'])),
                            $renderer->renderWidget(array('form' => $form['day'])),
                        ), $variables['date_pattern']);
            $html .= '</div>';
        }
        
        return $html;
    }
}
