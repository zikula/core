<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Functional;

class TranslationTest extends BaseTestCase
{
    public function testTwigTranslation()
    {
        $client = $this->createClient();
        $client->request('GET', '/apples/view');
        $response = $client->getResponse();

        $translator = $client->getContainer()->get('translator.default');
        var_dump($translator->getMessages());

        $this->assertEquals(200, $response->getStatusCode(), substr($response, 0, 2000));
        $this->assertEquals("text.apples_remaining_does_not_exist\n\nThere are 5 apples", $response->getContent());
    }
}
