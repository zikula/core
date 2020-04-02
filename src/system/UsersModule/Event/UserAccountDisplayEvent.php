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

namespace Zikula\UsersModule\Event;

/**
 * A UI event that is triggered when a user's account detail is viewed. This allows another module
 * to intercept the display of the user account detail in order to add its own information.
 * To add content to the user account detail, render output and add it to this event.
 *
 * When rendering this events output in a template, simply render the event itself
 * and the magic __toString() method will take care of the rest:
 *
 * {% set userAccountDisplayEvent = dispatchEvent('Zikula\\UsersModule\\Event\\UserAccountDisplayEvent', {userEntity}) %}
 * {{ userAccountDisplayEvent|raw }}
 */
class UserAccountDisplayEvent extends UserEntityEvent
{
    /**
     * @var array
     */
    private $contents = [];

    public function addContent(string $key = null, string $content = ''): void
    {
        $this->contents[$key] = $content;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function getContent(string $key): string
    {
        if (isset($this->contents[$key])) {
            return $this->contents[$key];
        }

        return '';
    }

    public function __toString(): string
    {
        $contents = '';
        foreach ($this->contents as $content) {
            $contents .= $content;
        }

        return $contents;
    }
}
