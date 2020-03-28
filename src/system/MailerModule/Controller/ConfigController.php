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

namespace Zikula\MailerModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Form\Type\ConfigType;
use Zikula\MailerModule\Form\Type\TestType;
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
    public function configAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel
    ): array {
        $form = $this->createForm(
            ConfigType::class,
            $this->getVars()
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                $this->setVars($formData);
                $transportStrings = [
                    'smtp' => 'smtp://$MAILER_ID:$MAILER_KEY@example.com',
                    'sendmail' => 'sendmail+smtp://default',
                    'amazon' => 'ses://$MAILER_ID:$MAILER_KEY@default',
                    'gmail' => 'gmail://$MAILER_ID:$MAILER_KEY@default',
                    'mailchimp' => 'mandrill://$MAILER_ID:$MAILER_KEY@default',
                    'mailgun' => 'mailgun://$MAILER_ID:$MAILER_KEY@default',
                    'postmark' => 'postmark://$MAILER_ID:$MAILER_KEY@default',
                    'sendgrid' => 'sendgrid://apikey:$MAILER_KEY@default', // unclear if 'apikey' is supposed to be literal, or replaced
                    'test' => 'null://null',
                ];
                try {
                    $vars = [
                        'MAILER_ID' => $formData['mailer_id'],
                        'MAILER_KEY' => $formData['mailer_key'],
                        'MAILER_DSN' => '!' . $transportStrings[$formData['transport']]
                    ];
                    $helper = new LocalDotEnvHelper($kernel->getProjectDir());
                    $helper->writeLocalEnvVars($vars);
                    $this->addFlash('status', 'Done! Configuration updated.');
                } catch (IOExceptionInterface $exception) {
                    $this->addFlash('error', $this->trans('Cannot write to %file%.' . ' ' . $exception->getMessage(), ['%file%' => $kernel->getProjectDir() . '\.env.local']));
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/test")
     * @Theme("admin")
     * @Template("@ZikulaMailerModule/Config/test.html.twig")
     *
     * This function displays a form to send a test mail.
     */
    public function testAction(
        Request $request,
        VariableApiInterface $variableApi,
        MailerInterface $mailer
    ): array {
        $form = $this->createForm(TestType::class, $this->getDataValues($variableApi));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('test')->isClicked()) {
                $formData = $form->getData();
                $html = in_array($formData['messageType'], ['html', 'multipart']) ? true : false;
                try {
                    $siteName = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
                    $adminMail = $variableApi->getSystemVar('adminmail');

                    $email = (new Email())
                        ->from(new Address($adminMail, $siteName))
                        ->to(new Address($formData['toAddress'], $formData['toName']))
                        ->subject($formData['subject'])
                        ->text($formData['bodyText'])
                    ;
                    if ($html) {
                        $email->html($formData['bodyHtml']);
                    }
                    $mailer->send($email);
                    $this->addFlash('status', 'Done! Message sent.');
                } catch (TransportExceptionInterface $exception) {
                    $this->addFlash('error', $exception->getMessage());
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
        VariableApiInterface $variableApi
    ): array {
        $modVars = $variableApi->getAll('ZikulaMailerModule');

        $modVars['sitename'] = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
        $modVars['adminmail'] = $variableApi->getSystemVar('adminmail');

        $modVars['fromName'] = $modVars['sitename'];
        $modVars['fromAddress'] = $modVars['adminmail'];

        return $modVars;
    }
}
