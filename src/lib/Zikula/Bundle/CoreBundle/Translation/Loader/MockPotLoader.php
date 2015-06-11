<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Translation\Loader;

use JMS\TranslationBundle\Exception\RuntimeException;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\Loader\LoaderInterface;

/**
 * Class MockPotLoader
 *
 * This class only exists so that errors are not created when running the `translation:extract` command where .pot files are already present.
 *
 * @package Zikula\Bundle\CoreBundle\Translation\Loader
 */
class MockPotLoader implements LoaderInterface
{
    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue();
        $catalogue->setLocale($locale);

        return $catalogue;
    }
}