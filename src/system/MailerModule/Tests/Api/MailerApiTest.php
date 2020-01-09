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

namespace Zikula\MailerModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Swift_Attachment;
use Swift_Events_SimpleEventDispatcher;
use Swift_Mailer;
use Swift_Message;
use Swift_Transport_SpoolTransport;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\IdentityTranslator;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\MailerModule\Api\MailerApi;
use Zikula\MailerModule\Tests\Api\Fixtures\CountableMemorySpool;

/**
 * Class MailerApiTest
 * @see https://www.pmg.com/blog/integration-testing-swift-mailer/
 */
class MailerApiTest extends TestCase
{
    /**
     * @var MailerApiInterface
     */
    private $api;

    /**
     * @var CountableMemorySpool
     */
    private $mailSpool;

    protected function setUp(): void
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
        $transport = new Swift_Transport_SpoolTransport(
            new Swift_Events_SimpleEventDispatcher(),
            $this->mailSpool
        );

        $mailer = new Swift_Mailer($transport);
        $this->api = new MailerApi(true, $kernel, new IdentityTranslator(), new EventDispatcher(), $configDumper, $variableApi, $mailer);
    }

    public function testInstance(): void
    {
        $this->assertInstanceOf(MailerApiInterface::class, $this->api);
    }

    public function testSendMessage(): void
    {
        $message = $this->getMessage();
        $message->setSubject('test subject')
            ->setBody('message body 45678');
        $this->assertTrue($this->api->sendMessage($message));
        $this->assertEquals(1, $this->mailSpool->count());
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $this->assertArrayHasKey('admin@example.com', $spooledMessage->getFrom());
        $this->assertArrayHasKey('foo@bar.com', $spooledMessage->getTo());
        $this->assertEquals('test subject', $spooledMessage->getSubject());
        $this->assertEquals('message body 45678', $spooledMessage->getBody());
    }

    public function testSendMultiple(): void
    {
        $message = $this->getMessage();
        for ($i = 1; $i <= 10; $i++) {
            $message->setSubject('message #' . $i)
                ->setBody('body of message #' . $i);
            $this->assertTrue($this->api->sendMessage($message));
        }
        $this->assertEquals(10, $this->mailSpool->count());
        $spooledMessage = $this->mailSpool->getMessages()[4];
        $this->assertEquals('message #5', $spooledMessage->getSubject());
        $this->assertEquals('body of message #5', $spooledMessage->getBody());
    }

    public function testSendMessageSetSubjectAndBody(): void
    {
        $message = $this->getMessage();
        $this->assertTrue($this->api->sendMessage($message, 'subject 789', 'body 789'));
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $this->assertEquals('subject 789', $spooledMessage->getSubject());
        $this->assertEquals('body 789', $spooledMessage->getBody());
    }

    public function testSendHtml(): void
    {
        $message = $this->getMessage();
        $this->assertTrue($this->api->sendMessage($message, 'subject 123', '<strong>body 123</strong>', '', true));
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $this->assertEquals('<strong>body 123</strong>', $spooledMessage->getBody());
        $this->assertEquals('text/html', $spooledMessage->getContentType());
    }

    public function testSendMultipart(): void
    {
        $message = $this->getMessage();
        $this->assertTrue($this->api->sendMessage($message, 'subject 234', '<strong>body 234</strong>', 'body 234'));
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $this->assertEquals('<strong>body 234</strong>', $spooledMessage->getBody());
        $this->assertEquals('multipart/alternative', $spooledMessage->getContentType());
    }

    public function testSendWithCustomHeaders(): void
    {
        $message = $this->getMessage();
        $message->getHeaders()->addTextHeader('X-ZIKULA-CUSTOM1', '345');
        $this->assertTrue($this->api->sendMessage($message, 'subject 345', 'body 345', '', false, ['X-ZIKULA-CUSTOM2' => '345']));
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $this->assertEquals('X-ZIKULA-CUSTOM1: 345', trim($spooledMessage->getHeaders()->get('X-ZIKULA-CUSTOM1')->toString()));
        $this->assertEquals('X-ZIKULA-CUSTOM2: 345', trim($spooledMessage->getHeaders()->get('X-ZIKULA-CUSTOM2')->toString()));
    }

    public function testSendWithAttachment(): void
    {
        $message = $this->getMessage();
        $initialChildCount = count($message->getChildren());
        $message->attach(Swift_Attachment::fromPath(__DIR__ . '/Fixtures/bar.txt'));
        $filePath = __DIR__ . '/Fixtures/foo.txt';
        $this->assertTrue($this->api->sendMessage($message, 'subject 456', 'body 456', '', false, [], [$filePath]));
        $spooledMessage = $this->mailSpool->getMessages()[0];
        $children = $spooledMessage->getChildren();
        $this->assertCount($initialChildCount + 2, $children);
        foreach ($children as $k => $child) {
            $this->assertEquals('text/plain', $child->getContentType());
            $this->assertInstanceOf('Swift_Mime_Headers_ParameterizedHeader', $child->getHeaders()->get('Content-Disposition'));
        }
        $this->assertEquals('bar.txt', $children[0]->getHeaders()->get('Content-Disposition')->getParameter('filename'));
        $this->assertEquals('foo.txt', $children[1]->getHeaders()->get('Content-Disposition')->getParameter('filename'));
    }

    private function getMessage($from = 'admin@example.com', $to = 'foo@bar.com'): Swift_Message
    {
        $message = new Swift_Message();
        $message->setFrom($from)
            ->setTo($to);

        return $message;
    }
}
