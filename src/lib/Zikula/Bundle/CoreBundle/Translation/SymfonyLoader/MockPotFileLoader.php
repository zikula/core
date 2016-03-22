<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
