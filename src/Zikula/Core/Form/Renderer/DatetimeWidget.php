<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class DatetimeWidget implements RendererInterface
{
    public function getName()
    {
        return 'datetime_widget';
    }

    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        $html = '';

        if ($variables['widget'] == 'single_text') {
            $html .= $renderer->getRender('field_widget')->render($form, $variables, $renderer);
        } else {
            $html .= '<div ' . $renderer->getRender('container_attributes')->render($form, $variables, $renderer) . '>';

            $html .= $renderer->renderWidget(array('form' => $form['date']));
            $html .= ' ' . $renderer->renderWidget(array('form' => $form['time']));
            $html .= '</div>';
        }

        return $html;
    }
}
