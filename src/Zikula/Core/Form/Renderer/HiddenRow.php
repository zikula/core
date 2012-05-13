<?php

namespace Zikula\Core\Form\Renderer;

use Symfony\Component\Form\FormView;
use Zikula\Core\Form\RendererInterface;
use Zikula\Core\Form\FormRenderer;

/**
 *
 */
class HiddenRow implements RendererInterface
{
    public function getName()
    {
        return 'hidden_row';
    }
    
    public function render(FormView $form, $variables, FormRenderer $renderer)
    {
        return $renderer->renderWidget(array('form' => $form));
    }
}
