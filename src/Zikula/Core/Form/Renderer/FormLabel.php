<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class FormLabel implements RendererInterface
{
    public function getName()
    {
        return 'form_label';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '<label ';

        foreach($variables['attr'] as $k => $v) {
            $html .= $k . '="' . $v . '" ';;
        }

        $html .= '>' . $variables['label'] . '</label>';

        return $html;
    }
}
