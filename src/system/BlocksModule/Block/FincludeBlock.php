<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;

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
