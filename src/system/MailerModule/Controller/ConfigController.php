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

namespace Zikula\MailerModule\Controller;

use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Site\SiteDefinitionInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Form\Type\MailTransportConfigType;
use Zikula\MailerModule\Form\Type\TestType;
use Zikula\MailerModule\Helper\MailTransportHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 *
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaMailerModule/Config/config.html.twig")
     */
    public function config(
        Request $request,
        MailTransportHelper $mailTransportHelper
    ): array {
        $form = $this->createForm(
            MailTransportConfigType::class,
            $this->getVars()
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                if ($this->transportConfigChanged($formData)) {
                    if (true === $mailTransportHelper->handleFormData($formData)) {
                        $this->addFlash('status', 'Done! Mailer Transport Config updated.');
                    } else {
                        $this->addFlash('error', $this->trans('Cannot write to %file%.', ['%file%' => '\.env.local']));
                    }
                    unset($formData['mailer_key'], $formData['save'], $formData['cancel']);
                    $this->setVars($formData);
                }
                $this->setVar('enableLogging', $formData['enableLogging']);
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    private function transportConfigChanged(array $formData): bool
    {
        $transportVars = ['transport', 'mailer_id', 'host', 'port', 'customParameters'];
        foreach ($transportVars as $transportVar) {
            if ($formData[$transportVar] !== $this->getVar($transportVar)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/test")
     * @Theme("admin")
     * @Template("@ZikulaMailerModule/Config/test.html.twig")
     *
     * This function displays a form to send a test mail.
     */
    public function test(
        Request $request,
        VariableApiInterface $variableApi,
        MailerInterface $mailer,
        RateLimiterFactory $testMailsLimiter,
        LoggerInterface $mailLogger, // $mailLogger var name auto-injects the mail channel handler
        SiteDefinitionInterface $site
    ): array {
        $form = $this->createForm(TestType::class, $this->getDataValues($variableApi, $site));
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
                    if ($variableApi->get('ZikulaMailerModule', 'enableLogging', false)) {
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

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Returns required data from module variables and mailer configuration.
     */
    private function getDataValues(
        VariableApiInterface $variableApi,
        SiteDefinitionInterface $site
    ): array {
        $modVars = $variableApi->getAll('ZikulaMailerModule');

        $modVars['sitename'] = $site->getName();
        $modVars['adminmail'] = $variableApi->getSystemVar('adminmail');

        $modVars['fromName'] = $modVars['sitename'];
        $modVars['fromAddress'] = $modVars['adminmail'];

        return $modVars;
    }
}
