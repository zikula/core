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

/**
 * An PreHandleRequestFormHookEvent provides an opportunity for a listener to add
 * children to an Form object and also add a template to display added children.
 */
abstract class PreHandleRequestFormHookEvent extends HookEvent
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var array
     */
    protected $templates = [];

    public function setForm(FormInterface $form): self
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @Todo do we really want/need this?
     * @return mixed
     */
    public function getFormData()
    {
        return $this->form->getData();
    }

    /**
     * @param FormInterface|string $child
     */
    public function formAdd($child, string $type = null, array $options = []): self
    {
        if (($child instanceof FormInterface) && ($child->getConfig()->getMapped() || $child->getConfig()->getAutoInitialize())) {
            throw new InvalidConfigurationException('Hooked child forms must disable `mapped` and `auto_initialize` options.');
        }
        $options['mapped'] = false;
        $options['auto_initialize'] = false;

        $this->form->add($child, $type, $options);

        return $this;
    }

    public function addTemplate(string $template, array $templateVars = []): self
    {
        if (!in_array($template, $this->templates, true)) {
            $this->templates[] = ['filename' => $template, 'vars' => $templateVars];
        }

        return $this;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }
}
