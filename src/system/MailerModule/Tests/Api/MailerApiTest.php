<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Tests\Api;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\MailerModule\Api\MailerApi;
use Zikula\MailerModule\Tests\Api\Fixtures\CountableMemorySpool;

/**
 * Class MailerApiTest
 * @see https://www.pmg.com/blog/integration-testing-swift-mailer/
 */
class MailerApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailerApiInterface
     */
    private $api;

    /**
     * @var CountableMemorySpool
     */
    private $mailSpool;

    /**
     * MailerApiTest setUp.
     */
    public function setUp()
    {
        $kernel = $this->getMockBuilder(ZikulaHttpKernelInterface::class)->getMock();
        $kernel->method('getLogDir')->willReturn('');
        $configDumper = $this->getMockBuilder(DynamicConfigDumper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configDumper->method('getConfiguration')->willReturn([]);
        $variableApi = $this->getMockBuilder(VariableApiInterface::class)->getMock();
        $variableApi->method('getAll')->willReturn([
            'charset' => 'UTF-8',
            'encoding' => '8bit',
            'html' => false,
            'wordwrap' => 50,
            'enableLogging' => false,
        ]);
        $this->mailSpool = new CountableMemorySpool();
        $transport = new \Swift_Transport_SpoolTransport(
            new \Swift_Events_SimpleEventDispatcher(),
            $this->mailSpool
        );

        $mailer = new \Swift_Mailer($transport);
        $this->api = new MailerApi(true, $kernel, new IdentityTranslator(), new EventDispatcher(), $configDumper, $variableApi, $mailer);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(MailerApiInterface::class, $this->api);
    }

    public function testSendMessage()
    {
        $message = new \Swift_Message(
            'test subject',
            'message body 45678'
        );
        $message->setFrom('admin@example.com')
            ->setTo('foo@bar.com');
        $this->assertTrue($this->api->sendMessage($message));
        $this->assertEquals(1, $this->mailSpool->count());
        /** @var \Swift_Message $firstMessage */
        $firstMessage = $this->mailSpool->getMessages()[0];
        $this->assertArrayHasKey('admin@example.com', $firstMessage->getFrom());
        $this->assertArrayHasKey('foo@bar.com', $firstMessage->getTo());
        $this->assertEquals('test subject', $firstMessage->getSubject());
        $this->assertEquals('message body 45678', $firstMessage->getBody());
    }

    public function testSendMultiple()
    {
        $message = new \Swift_Message();
        $message->setFrom('admin@example.com')
            ->setTo('foo@bar.com');
        for ($i = 1; $i <= 10; $i++) {
            $message->setSubject('message #' . $i)
                ->setBody('body of message #' . $i);
            $this->assertTrue($this->api->sendMessage($message));
        }
        $this->assertEquals(10, $this->mailSpool->count());
        /** @var \Swift_Message $message */
        $message = $this->mailSpool->getMessages()[4];
        $this->assertEquals('message #5', $message->getSubject());
        $this->assertEquals('body of message #5', $message->getBody());
    }
}
