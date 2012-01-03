<?php

namespace Zikula\Core\Forms\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Forms\RendererInterface;
use Zikula\Core\Forms\FormRenderer;

/**
 *
 */
class PrototypeRow implements RendererInterface
{
    public function getName()
    {
        return 'prototyp_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<script type="text/html" id="' . $variables['proto_id'] . '">'
              . $renderer->renderRow(array('form' => $form))
              . '</script>';
        
        return $html;
    }
}
