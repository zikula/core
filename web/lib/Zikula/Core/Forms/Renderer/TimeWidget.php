<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class TimeWidget implements RendererInterface
{
    public function getName()
    {
        return 'time_widget';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';
        
        if ($variables['widget'] == 'single_text') {
            $html .= $renderer->getRender('field_widget')->render($form, $variables, $renderer);
        } else {
            $html .= '<div ' . $renderer->getRender('container_attributes')->render($form, $variables, $renderer) . '>';
            
            $html .= $renderer->renderWidget(array('form' => $form['hour'], 'variables' => array('attr' => array('size' => 1))));
            $html .= ':' . $renderer->renderWidget(array('form' => $form['minute'], 'variables' => array('attr' => array('size' => 1))));
            
            if($variables['with_seconds']) {
                $html .= ':' . $renderer->renderWidget(array('form' => $form['second'], 'variables' => array('attr' => array('size' => 1))));
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
}
