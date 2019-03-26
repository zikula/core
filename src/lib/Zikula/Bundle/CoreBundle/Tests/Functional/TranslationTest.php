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

namespace Zikula\Bundle\CoreBundle\Tests\Functional;

class TranslationTest extends BaseTestCase
{
    public function testTwigTranslation()
    {
        $client = $this->createClient();
        $client->getContainer()->get('cache_clearer')->clear('');
        $client->request('GET', '/apples/view');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), mb_substr($response, 0, 2000));
        $this->assertEquals("text.apples_remaining_does_not_exist\n\nThere are 5 apples (zikula.en.po)\n", $response->getContent());

        $client->request('GET', '/apples/view/messages');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), mb_substr($response, 0, 2000));
        $this->assertEquals("text.apples_remaining_does_not_exist\n\nThere are 5 apples (messages.en.po)\n", $response->getContent());
    }

    public function testChangedLocale()
    {
        $client = $this->createClient();
        $client->request('GET', '/de/apples/t');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), mb_substr($response, 0, 2000));
        $this->assertEquals("This is in German!\n", $response->getContent());
    }
}
