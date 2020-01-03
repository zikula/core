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

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Desc as JmsDesc;
use JMS\TranslationBundle\Annotation\Ignore as JmsIgnore;
use JMS\TranslationBundle\Annotation\Meaning as JmsMeaning;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Translation\FileSourceFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreBundle\Translation\ZikulaPhpFileExtractor;
use Zikula\Core\AbstractBundle;

class PhpFileExtractorTest extends TestCase
{
    public function testExtractController(): void
    {
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new SplFileInfo('/' . __DIR__ . '/Fixture/Controller.php');
        $expected = new MessageCatalogue();

        $message = new Message('text.foo_bar', 'zikula');
        $message->setDesc('Foo bar');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 37));
        $expected->add($message);

        $message = new Message('text.sign_up_successful %name%', 'zikula');
        $message->setDesc('Welcome %name%! Thanks for signing up.');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 44));
        $expected->add($message);

        $message = new Message('button.archive', 'zikula');
        $message->setDesc('Archive Message');
        $message->setMeaning('The verb (to archive), describes an action');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 51));
        $expected->add($message);

        $message = new Message('text.irrelevant_doc_comment', 'baz');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 63));
        $expected->add($message);

        $message = new Message('text.array_method_call', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 68));
        $expected->add($message);

        $message = new Message('text.var.assign %foo%', 'zikula');
        $message->setDesc('The var %foo% should be assigned.');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 74));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('Controller.php'));
    }

    protected function extract(string $file, FileVisitorInterface $extractor = null): MessageCatalogue
    {
        if (!is_file($file = __DIR__ . '/Fixture/' . $file)) {
            throw new RuntimeException(sprintf('The file "%s" does not exist.', $file));
        }
        $fileInfo = new SplFileInfo($file);

        if (null === $extractor) {
            $kernel = $this->getMockBuilder(ZikulaHttpKernelInterface::class)->getMock();
            $kernel
                ->method('getBundles')
                ->willReturnCallback(function () {
                    $bundle = $this->getMockForAbstractClass(
                        AbstractBundle::class, [],
                        '',
                        false,
                        false,
                        true,
                        ['getNamespace']
                    );
                    $bundle
                        ->method('getNamespace')
                        ->willReturn('foo');
//                    $bundle
//                        ->method('getName')
//                        ->willReturn('bar');

                    return [$bundle];
                });
            $extractor = new ZikulaPhpFileExtractor($this->getDocParser(), $kernel);
        }

        $lexer = new Lexer();
        if (class_exists('PhpParser\ParserFactory')) {
            $factory = new ParserFactory();
            $parser = $factory->create(ParserFactory::PREFER_PHP7, $lexer);
//        } else {
//            $parser = new Parser($lexer);
        }

        $ast = $parser->parse(file_get_contents($fileInfo->getPath()));

        $catalogue = new MessageCatalogue();
        $extractor->visitPhpFile($fileInfo, $catalogue, $ast);

        return $catalogue;
    }

    protected function getDocParser(): DocParser
    {
        $docParser = new DocParser();
        $docParser->setImports([
            'desc' => JmsDesc::class,
            'meaning' => JmsMeaning::class,
            'ignore' => JmsIgnore::class,
        ]);
        $docParser->setIgnoreNotImportedAnnotations(true);

        return $docParser;
    }

    protected function getFileSourceFactory(): FileSourceFactory
    {
        return new FileSourceFactory('/');
    }
}
