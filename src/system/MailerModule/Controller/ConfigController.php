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
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
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
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi
    ): array {
        $form = $this->createForm(
            ConfigType::class,
            $this->getDataValues($variableApi),
            [
                'charset' => $kernel->getCharset()
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // save modvars
                $vars = [];
                foreach (['charset', 'encoding', 'html', 'wordwrap', 'enableLogging'] as $varName) {
                    $vars[$varName] = $formData[$varName];
                }
                $this->setVars($vars);

                // fetch different username and password fields depending on the transport type
                $credentialsSuffix = 'gmail' === $formData['transport'] ? 'Gmail' : '';

                $transport = (string)$formData['transport'];
                $disableDelivery = false;
                if ('test' === $transport) {
                    $transport = null;
                    $disableDelivery = true;
                }

                $deliveryAddresses = [];
                if (isset($currentConfig['delivery_addresses']) && !empty($currentConfig['delivery_addresses'])) {
                    $deliveryAddresses = $currentConfig['delivery_addresses'];
                } elseif (isset($currentConfig['delivery_address']) && !empty($currentConfig['delivery_address'])) {
                    $deliveryAddresses = [$currentConfig['delivery_address']];
                }

                // write the config file
                // @todo update the .env.local file

                $this->addFlash('status', 'Done! Configuration updated.');
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
