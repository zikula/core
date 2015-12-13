<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\BlockControllerInterface;

abstract class AbstractBlockController extends AbstractController implements BlockControllerInterface
{
    /**
     * Modify the block content.
     * @param Request $request
     * @return string
     */
    public function modify(Request $request, $content)
    {
        return $request->request->get('content', '');
    }

    /**
     * Display the block content.
     * @param array|string $content
     * @return array|string
     */
    public function display($content)
    {
        return $content;
    }

    /**
     * Get the type of the block (e.g. the 'name').
     * @return string
     */
    public function getType()
    {
        // default to the ClassName without the `Block` suffix
        // note: This string is intentionally left untranslated.
        $fqCn = get_class($this);
        $pos = strrpos($fqCn, '\\');

        return substr($fqCn, $pos + 1, -5);
    }
}
