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

namespace Zikula\Bundle\FormExtensionBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Zikula\Bundle\FormExtensionBundle\FormTypesChoices;

/**
 * Form type choice event.
 */
class FormTypeChoiceEvent extends Event
{
    public const NAME = 'zikula_form_extension_bundle.form_type_choice_event';

    /**
     * @var FormTypesChoices
     */
    protected $choices;

    public function __construct(FormTypesChoices $choices)
    {
        $this->setChoices($choices);
    }

    public function getChoices(): FormTypesChoices
    {
        return $this->choices;
    }

    public function setChoices(FormTypesChoices $choices): void
    {
        $this->choices = $choices;
    }
}
