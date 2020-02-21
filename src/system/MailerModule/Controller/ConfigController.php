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

use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\MailerModule\Form\Type\ConfigType;
use Zikula\MailerModule\Form\Type\TestType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaMailerModule/Config/config.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function configAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper
    ): array {
        if (!$this->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ConfigType::class,
            $this->getDataValues($variableApi, $configDumper), [
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

                $currentConfig = $configDumper->getConfiguration('swiftmailer');

                $deliveryAddresses = [];
                if (isset($currentConfig['delivery_addresses']) && !empty($currentConfig['delivery_addresses'])) {
                    $deliveryAddresses = $currentConfig['delivery_addresses'];
                } elseif (isset($currentConfig['delivery_address']) && !empty($currentConfig['delivery_address'])) {
                    $deliveryAddresses = [$currentConfig['delivery_address']];
                }

                // write the config file
                // https://symfony.com/doc/current/reference/configuration/swiftmailer.html
                $config = [
                    'transport' => $transport,
                    'username' => $formData['username' . $credentialsSuffix],
                    'password' => $formData['password' . $credentialsSuffix],
                    'host' => $formData['host'],
                    'port' => 'gmail' === $formData['transport'] ? 465 : $formData['port'],
                    'encryption' => $formData['encryption'],
                    'auth_mode' => $formData['auth_mode'],
                    // the items below can be configured by modifying the /config/dynamic/generated.yaml file
                    // 'spool' => !empty($currentConfig['spool']) ? $currentConfig['spool'] : ['type' => 'memory'],
                    'delivery_addresses' => $deliveryAddresses,
                    'disable_delivery' => $disableDelivery
                ];
                if ('' === $config['encryption']) {
                    $config['encryption'] = null;
                }
                if ('' === $config['auth_mode']) {
                    $config['auth_mode'] = null;
                }
                $configDumper->setConfiguration('swiftmailer', $config);

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
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function testAction(
        Request $request,
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper,
        MailerApiInterface $mailerApi
    ): array {
        if (!$this->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $paramHtml = $configDumper->getConfigurationForHtml('swiftmailer');
        // avoid exposing a password
        $paramHtml = preg_replace('/<li><strong>password:(.*?)<\/li>/is', '', $paramHtml);

        $form = $this->createForm(TestType::class, $this->getDataValues($variableApi, $configDumper));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('test')->isClicked()) {
                $formData = $form->getData();

                $html = in_array($formData['messageType'], ['html', 'multipart']) ? true : false;

                $textBody = $formData['bodyText'];
                $htmlBody = $formData['bodyHtml'];

                $msgBody = $textBody;
                $altBody = '';
                if ($html) {
                    $msgBody = $htmlBody;
                    $altBody = $textBody;
                }

                // add swiftmailer config to message for testing
                $swiftConfigHtml = "<h4>Swiftmailer Config:</h4>\n";
                $swiftConfigHtml .= $paramHtml;

                if ($html) {
                    $msgBody .= $swiftConfigHtml;
                    $altBody .= !empty($altBody) ? strip_tags($swiftConfigHtml) : '';
                } else {
                    $msgBody .= strip_tags($swiftConfigHtml);
                }

                // send the email
                try {
                    $siteName = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
                    $adminMail = $variableApi->getSystemVar('adminmail');

                    // create new message instance
                    $message = new Swift_Message();

                    $message->setFrom([$adminMail => $siteName]);
                    $message->setTo([$formData['toAddress'] => $formData['toName']]);

                    $result = $mailerApi->sendMessage($message, $formData['subject'], $msgBody, $altBody, $html);

                    // check our result and return the correct error code
                    if (true === $result) {
                        // success
                        $this->addFlash('status', 'Done! Message sent.');
                    } else {
                        $this->addFlash('error', 'It looks like the message could not be sent properly.');
                    }
                } catch (RuntimeException $exception) {
                    $this->addFlash('error', 'The message could not be sent properly.');
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView(),
            'swiftmailerHtml' => $paramHtml
        ];
    }

    /**
     * Returns required data from module variables and SwiftMailer configuration.
     */
    private function getDataValues(
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper
    ): array {
        $params = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');

        if (null === $params['transport']) {
            $params['transport'] = 'test';
        }

        $dataValues = array_merge($params, $modVars);

        $dataValues['sitename'] = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
        $dataValues['adminmail'] = $variableApi->getSystemVar('adminmail');

        $dataValues['fromName'] = $dataValues['sitename'];
        $dataValues['fromAddress'] = $dataValues['adminmail'];

        return $dataValues;
    }
}
