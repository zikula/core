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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
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
    public function configAction(
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
                $this->setVars($formData);
                if (true === $mailTransportHelper->handleFormData($formData)) {
                    $this->addFlash('status', 'Done! Configuration updated.');
                } else {
                    $this->addFlash('error', $this->trans('Cannot write to %file%.', ['%file%' => '\.env.local']));
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
