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

namespace Zikula\Bundle\HookBundle\FormAwareHook;

use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Core\UrlInterface;

class FormAwareResponse extends Hook
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
     */
    private $subjectUrl;

    /**
     * @param mixed|null $formSubject This may be the object, an array, the subject id, null
     */
    public function __construct(FormInterface $form, $formSubject = null, UrlInterface $subjectUrl = null)
    {
        $this->form = $form;
        $this->formSubject = $formSubject;
        $this->subjectUrl = $subjectUrl;
    }

    public function getFormData(string $name = null): array
    {
        if (isset($name)) {
            return $this->form->get($name)->getData();
        }

        return $this->form->getData();
    }

    /**
     * @return mixed
     */
    public function getFormSubject()
    {
        return $this->formSubject;
    }

    public function getSubjectUrl(): ?UrlInterface
    {
        return $this->subjectUrl;
    }
}
