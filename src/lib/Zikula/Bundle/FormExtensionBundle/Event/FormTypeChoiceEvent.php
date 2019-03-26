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
    const NAME = 'zikula_form_extension_bundle.form_type_choice_event';

    /**
     * @var FormTypesChoices
     */
    protected $choices;

    /**
     * FormTypeChoiceEvent constructor.
     *
     * @param FormTypesChoices $choices
     */
    public function __construct(FormTypesChoices $choices)
    {
        $this->setChoices($choices);
    }

    /**
     * Returns the choices.
     *
     * @return FormTypesChoices
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Returns the choices.
     *
     * @param FormTypesChoices $choices
     */
    public function setChoices(FormTypesChoices $choices)
    {
        $this->choices = $choices;
    }
}
