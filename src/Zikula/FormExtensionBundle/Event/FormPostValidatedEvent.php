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

/**
 * FormPostCreatedEvent MUST precede the dispatching of this event.
 *
 * After the form `isSubmitted()` and `isValid()`, dispatch
 * this event with the full $form object in the constructor.
 */
class FormPostValidatedEvent extends FormAwareEvent
{
}
