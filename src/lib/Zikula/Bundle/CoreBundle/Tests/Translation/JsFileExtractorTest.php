<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Tests\Translation;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Translation\FileSourceFactory;
use Zikula\Bundle\CoreBundle\Translation\ZikulaJsFileExtractor;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JsFileExtractorTest extends KernelTestCase
{
    public function testExtractController()
    {
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new \SplFileInfo('/' . __DIR__ . '/Fixture/Test.js');
        $expected = new MessageCatalogue();

        $message = new Message('My name is %n%', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $message = new Message('%count% apple|%count% apples', 'zikula_javascript');
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

        $message = new Message('Original Translator!', 'zikula_javascript');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('Test.js'));
    }

    protected function extract($file, FileVisitorInterface $extractor = null)
    {
        if (!is_file($file = __DIR__ . '/Fixture/' . $file)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $file));
        }
        $file = new \SplFileInfo($file);

        if (null === $extractor) {
            $kernel = $this
            ->getMockBuilder('\Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel')
            ->disableOriginalConstructor()
            ->getMock();
            $kernel
            ->method('getBundle')
            ->will($this->returnCallback(function ($bundleName) {
                $bundle = $this
                ->getMockBuilder('Zikula\Core\AbstractBundle')
                ->disableOriginalConstructor()
                ->getMock();
                $bundle
                ->method('getTranslationDomain')
                ->willReturn(strtolower($bundleName));
            
                return $bundle;
            }));
            self::bootKernel();
            
            $extractor = new ZikulaJsFileExtractor($kernel);
        }

        $catalogue = new MessageCatalogue();
        $extractor->visitFile($file, $catalogue);

        return $catalogue;
    }

    protected function getFileSourceFactory()
    {
        return new FileSourceFactory('/');
    }
}

