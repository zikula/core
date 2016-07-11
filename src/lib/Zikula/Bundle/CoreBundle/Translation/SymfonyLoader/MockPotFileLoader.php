<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Translation\SymfonyLoader;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * MockPotFileLoader does nothing but avoid errors on empty cache load
 * when .pot files are present in a bundle
 */
class MockPotFileLoader extends ArrayLoader
{
    /**
     * returns empty MessageCatalogue
     *
     * @param mixed $resource
     * @param string $locale
     * @param string $domain
     * @return MessageCatalogue
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        return new MessageCatalogue($locale);
    }
}
