<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class CheckboxWidget implements RendererInterface
{
    public function getName()
    {
        return 'checkbox_widget';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<input type="checkbox" '
              . $renderer->getRender('attributes')->render($form, $variables, $renderer);

        if($variables['value']) {
            $html .= ' value="' . $variables['value'] . '"';
        }

        if($variables['checked']) {
            $html .= ' checked="checked"';
        }

        $html .= ' />';

        return $html;
    }
}
