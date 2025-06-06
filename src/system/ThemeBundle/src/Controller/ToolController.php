<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ThemeBundle\Form\Type\MailTestType;

#[Route('/admin/tool')]
#[IsGranted('ROLE_ADMIN')]
class ToolController extends AbstractController
{
    public function __construct(
        private readonly SiteDefinitionInterface $site,
        #[Autowire(param: 'enable_mail_logging')]
        private readonly bool $mailLoggingEnabled
    ) {
    }

    /**
     * This function displays a form to send a test mail.
     */
    #[Route('/testmail', name: 'zikula_theme_tool_testmail')]
    public function test(
        Request $request,
        MailerInterface $mailer,
        RateLimiterFactory $testMailsLimiter,
        LoggerInterface $mailLogger // $mailLogger var name auto-injects the mail channel handler
    ): Response {
        $form = $this->createForm(MailTestType::class, $this->getDataValues());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('test')->isClicked()) {
                $limiter = $testMailsLimiter->create($request->getClientIp());
                if (false === $limiter->consume(1)->isAccepted()) {
                    throw new TooManyRequestsHttpException();
                }

                $formData = $form->getData();
                $html = in_array($formData['messageType'], ['html', 'multipart']) ? true : false;
                try {
                    $email = (new Email())
                        ->from(new Address($formData['adminmail'], $formData['sitename']))
                        ->to(new Address($formData['toAddress'], $formData['toName']))
                        ->subject($formData['subject'])
                        ->text($formData['bodyText'])
                    ;
                    if ($html) {
                        $email->html($formData['bodyHtml']);
                    }
                    $mailer->send($email);
                    if ($this->mailLoggingEnabled) {
                        $mailLogger->info(sprintf('Email sent to %s', $formData['toAddress']), [
                            'in' => __METHOD__,
                        ]);
                    }
                    $this->addFlash('status', 'Done! Message sent.');
                } catch (TransportExceptionInterface $exception) {
                    $mailLogger->error($exception->getMessage(), [
                        'in' => __METHOD__,
                    ]);
                    $this->addFlash('error', $exception->getCode() . ': ' . $exception->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return $this->render('@ZikulaTheme/Tool/testmail.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Returns required data from configuration.
     */
    private function getDataValues(): array
    {
        $parameters = [];
        $parameters['sitename'] = $this->site->getName();
        $parameters['adminmail'] = $this->site->getAdminMail();

        $parameters['fromName'] = $parameters['sitename'];
        $parameters['fromAddress'] = $parameters['adminmail'];

        return $parameters;
    }

    /**
     * Displays the content of {@see phpinfo()}.
     */
    #[Route('/phpinfo', name: 'zikula_theme_tool_phpinfo', methods: ['GET'])]
    public function phpinfo(): Response
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $phpinfo = str_replace(
            'bundle_Zend Optimizer',
            'bundle_Zend_Optimizer',
            preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo)
        );

        return $this->render('@ZikulaTheme/Tool/phpinfo.html.twig', [
            'phpinfo' => $phpinfo,
        ]);
    }
}
