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

use JMS\TranslationBundle\Translation\FileSourceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use JMS\TranslationBundle\Twig\RemovingNodeVisitor;
use JMS\TranslationBundle\Twig\DefaultApplyingNodeVisitor;
use JMS\TranslationBundle\Exception\RuntimeException;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Bridge\Twig\Extension\TranslationExtension as SymfonyTranslationExtension;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Twig\TranslationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Zikula\Bundle\CoreBundle\Translation\ZikulaTwigFileExtractor;
use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;
use Zikula\Bundle\CoreBundle\Twig\Extension\GettextExtension;
use Zikula\Common\Translator\IdentityTranslator as ZikulaIdentityTranslator;
use Zikula\ThemeModule\Engine\ParameterBag;
use Zikula\ThemeModule\Twig\Extension\AssetExtension;
use Zikula\ThemeModule\Twig\Extension\PageVarExtension;

class TwigFileExtractorTest extends KernelTestCase
{
    public function testExtractSimpleTemplate()
    {
        $expected = new MessageCatalogue();
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new \SplFileInfo('/' . __DIR__ . '/Fixture/simple_template.html.twig'); // extra slash in path is necessary :(

        $message = new Message('text1', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 1));
        $expected->add($message);

        $message = new Message('text2 %s', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 3));
        $expected->add($message);

        $message = new Message('text3|text3s', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 5));
        $expected->add($message);

        $message = new Message('text4 %s|text4s %s', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 7));
        $expected->add($message);

        $message = new Message('text5', 'my_domain');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 9));
        $expected->add($message);

        $message = new Message('text6 %s', 'my_domain');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 11));
        $expected->add($message);

        $message = new Message('text7|text7s', 'my_domain');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 13));
        $expected->add($message);

        $message = new Message('text8 %s|text8s %s', 'my_domain');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 15));
        $expected->add($message);

        $message = new Message('bar', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 17));
        $expected->add($message);

        $message = new Message('foo', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 19));
        $expected->add($message);

        $message = new Message('foo is foo', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 19));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('simple_template.html.twig'));
    }

    public function testExtractDeleteTemplate()
    {
        $expected = new MessageCatalogue();
        $fileSourceFactory = $this->getFileSourceFactory();
        $fixtureSplInfo = new \SplFileInfo('/' . __DIR__ . '/Fixture/delete.html.twig'); // extra slash in path is necessary :(

        $message = new Message('Delete block position', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 9));
        $expected->add($message);

        $message = new Message("Delete block position", 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 10));
        $expected->add($message);

        $message = new Message('Do you really want to delete position \'%name%\'?', 'zikula');
        $message->addSource($fileSourceFactory->create($fixtureSplInfo, 13));
        $expected->add($message);

        $this->assertEquals($expected, $this->extract('delete.html.twig'));
    }

    private function extract($file, ZikulaTwigFileExtractor $extractor = null)
    {
        if (!is_file($file = __DIR__ . '/Fixture/' . $file)) {
            throw new RuntimeException(sprintf('The file "%s" does not exist.', $file));
        }
        $kernel = $this
            ->getMockBuilder('\Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel')
            ->disableOriginalConstructor()
            ->getMock();
        $kernel
            ->method('getBundle')
            ->will($this->returnCallback(function($bundleName) {
                $bundle = $this
                    ->getMockBuilder('Zikula\Core\AbstractBundle')
                    ->disableOriginalConstructor()
                    ->getMock();
                $bundle
                    ->method('getTranslationDomain')
                    ->willReturn(strtolower($bundleName));

                return $bundle;
            }));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $assetExtension = $this->getMockBuilder(AssetExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $env = new \Twig_Environment();
        $zikulaTranslator = new ZikulaIdentityTranslator(new MessageSelector());
        $env->addExtension(new SymfonyTranslationExtension($zikulaTranslator));
        $env->addExtension(new TranslationExtension($zikulaTranslator, true));
        $env->addExtension(new RoutingExtension(new UrlGenerator(new RouteCollection(), new RequestContext())));
        $env->addExtension(new FormExtension(new TwigRenderer(new TwigRendererEngine())));
        $env->addExtension(new GettextExtension($zikulaTranslator, $kernel));
        $env->addExtension(new PageVarExtension($zikulaTranslator, new ParameterBag(), $logger, $assetExtension));
        self::bootKernel();
        $env->addExtension(new CoreExtension($zikulaTranslator));

        foreach ($env->getNodeVisitors() as $visitor) {
            if ($visitor instanceof DefaultApplyingNodeVisitor) {
                $visitor->setEnabled(false);
            }
            if ($visitor instanceof RemovingNodeVisitor) {
                $visitor->setEnabled(false);
            }
        }

        if (null === $extractor) {
            $extractor = new ZikulaTwigFileExtractor($env, $kernel);
        }

        $ast = $env->parse($env->tokenize(file_get_contents($file), $file));

        $catalogue = new MessageCatalogue();
        $extractor->visitTwigFile(new SplFileInfo($file, 'Fixture/', 'Fixture/' . basename($file)), $catalogue, $ast);

        return $catalogue;
    }

    protected function getFileSourceFactory()
    {
        return new FileSourceFactory('/');
    }
}
