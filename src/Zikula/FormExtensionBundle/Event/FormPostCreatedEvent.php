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
 * This event allows the subscriber to add children to the FormType within
 * the event. These children should be self-validating (implementing constraints).
 * Added children should also add their own template.
 *
 * Controllers dispatching this event must include the event templates within
 * the form display e.g.
 *   {{ form_start(form) }}
 *   ...
 *   {{ for template in eventVariable.templates }}
 *       {{ include(template.view, template.params, ignore_missing = true) }}
 *   {{ endfor }}
 *   ...
 *   {{ form_end(form) }}
 *
 * The FormPostValidatedEvent MUST be dispatched after this event.
 */
class FormPostCreatedEvent extends FormAwareEvent
{
}
