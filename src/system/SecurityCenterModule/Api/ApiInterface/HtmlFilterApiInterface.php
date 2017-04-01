<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Api\ApiInterface;

interface HtmlFilterApiInterface
{
    /**
     * Occurs when a string is passed to HtmlFilterApi and filtered.
     * An instance of Zikula\Core\Event\GenericEvent, the data is the filterable string.
     */
    const HTML_STRING_FILTER = 'htmlfilter.outputfilter';

    /**
     * Filter an html string (or array of strings) and remove disallowed tags
     *
     * @param string|array $value
     * @return string|array
     */
    public function filter($value);
}
