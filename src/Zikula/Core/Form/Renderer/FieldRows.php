<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class FieldRows implements RendererInterface
{
    public function getName()
    {
        return 'field_rows';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = $renderer->renderErrors(array('form' => $form));

        foreach ($form as $child) {
            $html .= $renderer->renderRow(array('form' => $child));
        }

        return $html;
    }
}
