<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\HookEvent;

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormInterface;

abstract class FormHookEvent extends HookEvent
{
    /* @var FormInterface */
    private $form;

    /* @var array */
    private $templates = [];

    /* @var mixed */
    private $subject;

    /* @var string */
    private $display;

    final public function setForm(FormInterface $form): self
    {
        $this->form = $form;

        return $this;
    }

    final public function formIsSubmitted(): bool
    {
        return $this->form->isSubmitted();
    }

    /**
     * @return mixed
     */
    final public function getFormData(string $name = null)
    {
        if (isset($name)) {
            return $this->form->get($name)->getData();
        }

        return $this->form->getData();
    }

    /**
     * @param FormInterface|string $child
     */
    final public function formAdd($child, string $type = null, array $options = []): self
    {
        if (($child instanceof FormInterface) && ($child->getConfig()->getMapped() || $child->getConfig()->getAutoInitialize())) {
            throw new InvalidConfigurationException('Hooked child forms must disable `mapped` and `auto_initialize` options.');
        }
        $options['mapped'] = false;
        $options['auto_initialize'] = false;

        $this->form->add($child, $type, $options);

        return $this;
    }

    final public function addTemplate(string $template, array $templateVars = []): self
    {
        if (!in_array($template, $this->templates, true)) {
            $this->templates[] = ['filename' => $template, 'vars' => $templateVars];
        }

        return $this;
    }

    final public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * @param mixed $subject This may be the object, an array, the subject id, etc
     */
    final public function setSubject($subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    final public function getSubject()
    {
        return $this->subject;
    }

    final public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }

    final public function getDisplay(): string
    {
        return $this->display ?? '';
    }
}
