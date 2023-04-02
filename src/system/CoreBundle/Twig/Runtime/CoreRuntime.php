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

namespace Zikula\CoreBundle\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class CoreRuntime implements RuntimeExtensionInterface
{
    /**
     * Protect a given mail address by finding the text 'x@y' and replacing
     * it with HTML entities. This provides protection against email harvesters.
     */
    public function protectMailAddress(string $string): string
    {
        $string = preg_replace_callback(
            '/(.)@(.)/s',
            static function ($m) {
                return '&#' . sprintf('%03d', ord($m[1])) . ';&#064;&#' . sprintf('%03d', ord($m[2])) . ';';
            },
            $string
        );

        return $string;
    }
}
