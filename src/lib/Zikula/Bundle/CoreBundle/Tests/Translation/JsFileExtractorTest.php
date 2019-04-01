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

namespace Zikula\Bundle\CoreBundle\Tests\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Translation\FileSourceFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;
use Zikula\Bundle\CoreBundle\Translation\ZikulaJsFileExtractor;

class JsFileExtractorTest extends TestCase
{
    public function testExtractController(): void
    {
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new SplFileInfo('/' . __DIR__ . '/Fixture/Test.js');
        $expected = new MessageCatalogue();

        $message = new Message('My name is %n%', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('%count% apple|%count% apples', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('%count% more apple|%count% more apples', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('%count% %desc% apple|%count% %desc% apples', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('someText|someTexts', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('Hi there!', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('Hi there! (again)', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('Hi there! "Foo"', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('Original Translator!', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('Test.js'));
    }

    protected function extract(string $file, FileVisitorInterface $extractor = null): MessageCatalogue
    {
        if (!is_file($file = __DIR__ . '/Fixture/' . $file)) {
            throw new RuntimeException(sprintf('The file "%s" does not exist.', $file));
        }
        $fileInfo = new SplFileInfo($file);

        if (null === $extractor) {
            $extractor = new ZikulaJsFileExtractor();
        }

        $catalogue = new MessageCatalogue();
        $extractor->visitFile($fileInfo, $catalogue);

        return $catalogue;
    }

    protected function getFileSourceFactory(): FileSourceFactory
    {
        return new FileSourceFactory('/');
    }
}
