<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Event;

use Symfony\Contracts\EventDispatcher\Event;
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
     */
    public function formAdd($child, string $type = null, array $options = []): self
    {
        $this->form->add($child, $type, $options);

        return $this;
    }

    public function addTemplate(string $template, array $templateVars = []): self
    {
        if (!in_array($template, $this->templates, true)) {
            $this->templates[] = [$template, $templateVars];
        }

        return $this;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }
}
