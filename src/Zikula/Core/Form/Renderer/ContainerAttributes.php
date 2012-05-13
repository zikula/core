<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class ContainerAttributes implements RendererInterface
{
    public function getName()
    {
        return 'container_attributes';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = 'id="' . $variables['id'] . '" ';

        foreach($variables['attr'] as $k => $v) { 
            $html .= $k . '="' . $v . '" ';
        }
        
        return $html;
    }
}
