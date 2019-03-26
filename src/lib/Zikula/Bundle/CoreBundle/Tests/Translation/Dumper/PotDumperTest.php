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

namespace Zikula\Bundle\CoreBundle\Tests\Translation\Dumper;

use JMS\TranslationBundle\Exception\InvalidArgumentException;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use Zikula\Bundle\CoreBundle\Translation\Dumper\PotDumper;

class PotDumperTest extends \PHPUnit\Framework\TestCase
{
    public function testDumpStructureWithoutPrettyPrint()
    {
        $catalogue = new MessageCatalogue();
        $catalogue->setLocale('fr');
        $catalogue->add(new Message('foo.bar.baz'));

        $dumper = $this->getDumper();
        $dumper->setPrettyPrint(false);
        $expected = preg_split('/\r\n|\r|\n/', $this->getOutput('messages'));
        $dump = preg_split('/\r\n|\r|\n/', $dumper->dump($catalogue, 'messages'));

        $this->assertEquals($expected, $dump);
    }

    protected function getDumper()
    {
        return new PotDumper(false);
    }

    protected function getOutput($key)
    {
        if (!is_file($file = __DIR__ . '/pot/' . $key . '.pot')) {
            throw new InvalidArgumentException(sprintf('There is no output for key "%s".', $key));
        }

        return file_get_contents($file);
    }
}
