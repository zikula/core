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
use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Translation\FileSourceFactory;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Zikula\Bundle\CoreBundle\Translation\ZikulaPhpFileExtractor;

class PhpFileExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractController()
    {
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new \SplFileInfo('/' . __DIR__ . '/Fixture/Controller.php');
        $expected = new MessageCatalogue();

        $message = new Message('text.foo_bar', 'zikula');
        $message->setDesc('Foo bar');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 36));
        $expected->add($message);

        $message = new Message('text.sign_up_successful %name%', 'zikula');
        $message->setDesc('Welcome %name%! Thanks for signing up.');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 43));
        $expected->add($message);

        $message = new Message('button.archive', 'zikula');
        $message->setDesc('Archive Message');
        $message->setMeaning('The verb (to archive), describes an action');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 50));
        $expected->add($message);

        $message = new Message('text.irrelevant_doc_comment', 'baz');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 62));
        $expected->add($message);

        $message = new Message('text.array_method_call', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 67));
        $expected->add($message);

        $message = new Message('text.var.assign %foo%', 'zikula');
        $message->setDesc('The var %foo% should be assigned.');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 73));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('Controller.php'));
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
                ->method('getBundles')
                ->will($this->returnCallback(function () {
                    $bundle = $this
                        ->getMockBuilder('Zikula\Core\AbstractBundle')
                        ->disableOriginalConstructor()
                        ->getMock();
                    $bundle
                        ->method('getNamespace')
                        ->willReturn('foo');
                    $bundle
                        ->method('getName')
                        ->willReturn('bar');

                    return [$bundle];
                }));
            $extractor = new ZikulaPhpFileExtractor($this->getDocParser(), $kernel);
        }

        $lexer = new Lexer();
        if (class_exists('PhpParser\ParserFactory')) {
            $factory = new ParserFactory();
            $parser = $factory->create(ParserFactory::PREFER_PHP7, $lexer);
        } else {
            $parser = new Parser($lexer);
        }

        $ast = $parser->parse(file_get_contents($file));

        $catalogue = new MessageCatalogue();
        $extractor->visitPhpFile($file, $catalogue, $ast);

        return $catalogue;
    }

    protected function getDocParser()
    {
        $docParser = new DocParser();
        $docParser->setImports([
            'desc' => 'JMS\TranslationBundle\Annotation\Desc',
            'meaning' => 'JMS\TranslationBundle\Annotation\Meaning',
            'ignore' => 'JMS\TranslationBundle\Annotation\Ignore',
        ]);
        $docParser->setIgnoreNotImportedAnnotations(true);

        return $docParser;
    }

    protected function getFileSourceFactory()
    {
        return new FileSourceFactory('/');
    }
}
