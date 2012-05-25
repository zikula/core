<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

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
