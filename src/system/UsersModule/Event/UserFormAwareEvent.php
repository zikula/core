<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;

class UserFormAwareEvent extends Event
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var array
     */
    private $templates = [];

    /**
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return mixed
     */
    public function getFormData()
    {
        return $this->form->getData();
    }

    /**
     * @param FormInterface|string|int $child
     * @param string|null $type
     * @param array $options
     * @return self
     */
    public function formAdd($child, $type = null, array $options = [])
    {
        $this->form->add($child, $type, $options);

        return $this;
    }

    /**
     * @param string $template
     * @param array $templateVars
     * @return self
     */
    public function addTemplate($template, $templateVars = [])
    {
        if (!in_array($template, $this->templates)) {
            $this->templates[] = [$template, $templateVars];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }
}
