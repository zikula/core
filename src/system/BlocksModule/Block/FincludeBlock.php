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

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Block\Form\Type\FincludeBlockType;

/**
 * Block to display the contents of a file given a path.
 */
class FincludeBlock extends AbstractBlockHandler
{
    public const FILETYPE_HTML = 0;

    public const FILETYPE_TEXT = 1;

    public const FILETYPE_PHP = 2;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('fincludeblock::', $properties['title'] . '::', ACCESS_READ)) {
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

    public function getFormClassName(): string
    {
        return FincludeBlockType::class;
    }

    public function getType(): string
    {
        return $this->trans('File Include');
    }
}
