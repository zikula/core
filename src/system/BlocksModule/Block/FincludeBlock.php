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

namespace Zikula\BlocksModule\Block;

use Zikula\Core\AbstractBlockHandler;

/**
 * Block to display the contents of a file given a path.
 */
class FincludeBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        if (!$this->hasPermission('fincludeblock::', "$properties[title]::", ACCESS_READ)) {
            return '';
        }

        switch ($properties['typo']) {
            case 0: // Html
                return file_get_contents($properties['filo']);
                break;
            case 1: // Text
                return htmlspecialchars(file_get_contents($properties['filo']));
                break;
            case 2: // PHP
                ob_start();
                include \DataUtil::formatForOS($properties['filo']);
                return ob_get_clean();
                break;
            default:
                return '';
        }
    }

    public function getFormClassName()
    {
        return 'Zikula\BlocksModule\Block\Form\Type\FincludeBlockType';
    }

    public function getType()
    {
        return $this->__("File Include");
    }
}
