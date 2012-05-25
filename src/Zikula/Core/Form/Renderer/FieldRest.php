<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class FieldRest implements RendererInterface
{
    public function getName()
    {
        return 'field_rest';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';

        foreach ($form as $child) {
            if (!$child->isRendered()) {
                $html .= $renderer->renderRow(array('form' => $child));
            }
        }

        return $html;
    }
}
