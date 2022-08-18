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

namespace Zikula\ProfileModule\Helper;

class GravatarHelper
{
    /**
     * Returns the URL to a gravatar image.
     *
     * @see http://en.gravatar.com/site/implement/images/php/
     */
    public function getGravatarUrl(string $emailAddress = '', array $parameters = []): string
    {
        $url = 'https://secure.gravatar.com/avatar/';
        $url .= md5(mb_strtolower(trim($emailAddress))) . '.jpg';

        $url .= '?s=' . (isset($parameters['size']) ? (int) $parameters['size'] : 80);
        $url .= '&amp;d=' . ($parameters['imageset'] ?? 'mm');
        $url .= '&amp;r=' . ($parameters['rating'] ?? 'g');

        return $url;
    }
}
