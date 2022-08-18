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

namespace Zikula\Bundle\FormExtensionBundle\Event;

use Symfony\Component\Form\FormInterface;

/**
 * This event should not be confused with Symfony's own FormEvents.
 * @see https://symfony.com/doc/current/form/events.html
 * This event is dispatched outside the flow of Symfony's form handling.
 *
 * This event is subclassed twice and dispatched twice in the workflow of a form.
 *   1) FormPostCreatedEvent: After the form is created and before it is `handled`.
 *   2) FormPostValidatedEvent: After the form is submitted and validated.
 */
class FormAwareEvent
{
    private array $templates = [];

    public function __construct(private readonly FormInterface $form)
    {
    }

    public function getFormData(string $prefix = null): mixed
    {
        if (isset($prefix)) {
            return $this->form->get($prefix)->getData();
        }

        return $this->form->getData();
    }

    public function formAdd(FormInterface|string|int $child, string $type = null, array $options = []): self
    {
        $this->form->add($child, $type, $options);

        return $this;
    }

    public function addTemplate(string $template, array $templateVars = []): self
    {
        if (!in_array($template, $this->templates, true)) {
            $this->templates[] = ['view' => $template, 'params' => $templateVars];
        }

        return $this;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }
}
