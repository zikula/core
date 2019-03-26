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

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Block\Form\Type\FincludeBlockType;

/**
 * Block to display the contents of a file given a path.
 */
class FincludeBlock extends AbstractBlockHandler
{
    const FILETYPE_HTML = 0;

    const FILETYPE_TEXT = 1;

    const FILETYPE_PHP = 2;

    public function display(array $properties)
    {
        if (!$this->hasPermission('fincludeblock::', "{$properties[title]}::", ACCESS_READ)) {
            return '';
        }

        switch ($properties['typo']) {
            case self::FILETYPE_HTML:
                return file_get_contents($properties['filo']);
                break;
            case self::FILETYPE_TEXT:
                return htmlspecialchars(file_get_contents($properties['filo']));
                break;
            case self::FILETYPE_PHP:
                ob_start();
                include $properties['filo'];

                return ob_get_clean();
                break;
            default:
                return '';
        }
    }

    public function getFormClassName()
    {
        return FincludeBlockType::class;
    }

    public function getType()
    {
        return $this->__('File Include');
    }
}
