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

use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\UrlInterface;

/**
 * An PostValidationFormHookEvent provides an opportunity for a listener to take
 * action on the resultant form data post validation.
 */
abstract class PostValidationFormHookEvent extends HookEvent
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var mixed
     */
    private $formSubject;

    /**
     * @var UrlInterface
     * @todo is this still needed? what is the usecase to pass this to listener?
     */
    private $subjectUrl;

    /**
     * @var string
     */
    private $display;

    /**
     * @param mixed|null $formSubject This may be the object, an array, the subject id, null
     */
    public function __construct(FormInterface $form, $formSubject = null, UrlInterface $subjectUrl = null)
    {
        $this->form = $form;
        $this->formSubject = $formSubject;
        $this->subjectUrl = $subjectUrl;
    }

    /**
     * @return mixed
     */
    public function getFormData(string $name = null)
    {
        if (isset($name)) {
            return $this->form->get($name)->getData();
        }

        return $this->form->getData();
    }

    public function getFormSubject()
    {
        return $this->formSubject;
    }

    public function getSubjectUrl(): ?UrlInterface
    {
        return $this->subjectUrl;
    }

    public function getDisplay(): string
    {
        return $this->display ?? '';
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
    }
}
