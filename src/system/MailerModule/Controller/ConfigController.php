<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @package Zikula\MailerModule\Controller
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("")
     * @deprecated remove at Core-2.0
     */
    public function indexAction()
    {
        @trigger_error('The zikulamailermodule_config_index route is deprecated. please use zikulamailermodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirect($this->generateUrl('zikulamailermodule_config_config'));
    }

    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\MailerModule\Form\Type\ConfigType');

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // save modvars
                $vars = [];
                foreach (['charset', 'encoding', 'html', 'wordwrap', 'enableLogging'] as $varName) {
                    $vars[$varName] = $formData[$varName];
                }
                $this->get('zikula_extensions_module.api.variable')->setAll('ZikulaMailerModule', $vars);

                // fetch different username and password fields depending on the transport type
                $credentialsSuffix = $formData['transport'] == 'gmail' ? 'Gmail' : '';

                // write the config file
                // http://symfony.com/doc/current/reference/configuration/swiftmailer.html
                $configDumper = $this->get('zikula.dynamic_config_dumper');
                $currentConfig = $configDumper->getConfiguration('swiftmailer');
                $config = [
                    'transport' => (string)$formData['transport'],
                    'username' => $formData['username' . $credentialsSuffix],
                    'password' => $formData['password' . $credentialsSuffix],
                    'host' => $formData['host'],
                    'port' => $formData['port'],
                    'encryption' => $formData['encryption'],
                    'auth_mode' => $formData['auth_mode'],
                    // the items below can be configured by modifying the app/config/dynamic/generated.yml file
                    // 'spool' => !empty($currentConfig['spool']) ? $currentConfig['spool'] : ['type' => 'memory'],
                    'delivery_addresses' => !empty($currentConfig['delivery_addresses'])
                        ? $currentConfig['delivery_addresses']
                        : (!empty($currentConfig['delivery_address']) ? [$currentConfig['delivery_address']] : []),
                    'disable_delivery' => !empty($currentConfig['disable_delivery']) ? $currentConfig['disable_delivery'] : false,
                ];
                if ($config['encryption'] == '') {
                    $config['encryption'] = null;
                }
                if ($config['auth_mode'] == '') {
                    $config['auth_mode'] = null;
                }
                $configDumper->setConfiguration('swiftmailer', $config);

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/test")
     * @Theme("admin")
     * @Template
     *
     * This function displays a form to sent a test mail.
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $dumper = $this->get('zikula.dynamic_config_dumper');
        $paramHtml = $dumper->getConfigurationForHtml('swiftmailer');

        $form = $this->createForm('Zikula\MailerModule\Form\Type\TestType');

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('test')->isClicked()) {
                $formData = $form->getData();

                $html = in_array($formData['messageType'], array('html', 'multipart')) ? true : false;

                $textBody = $formData['bodyText'];
                $htmlBody = $formData['bodyHtml'];

                $msgBody = $textBody;
                $altBody = '';
                if ($html) {
                    $msgBody = $htmlBody;
                    $altBody = $textBody;
                }

                // add swiftmailer config to message for testing
                $dumper = $this->get('zikula.dynamic_config_dumper');
                $swiftConfigHtml = "<h4>Swiftmailer Config:</h4>\n";
                $swiftConfigHtml .= $dumper->getConfigurationForHtml('swiftmailer');

                if ($html) {
                    $msgBody .= $swiftConfigHtml;
                    $altBody .= !empty($altBody) ? strip_tags($swiftConfigHtml) : '';
                } else {
                    $msgBody .= strip_tags($swiftConfigHtml);
                }

                // send the email
                try {
                    $result = \ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage', [
                        'toname' => $formData['toName'],
                        'toaddress' => $formData['toAddress'],
                        'subject' => $formData['subject'],
                        'body' => $msgBody,
                        'altbody' => $altBody,
                        'html' => $html
                    ]);

                    // check our result and return the correct error code
                    if (true === $result) {
                        // Success
                        $this->addFlash('status', $this->__('Done! Message sent.'));
                    } else {
                        $this->addFlash('error', $this->__('It looks like the message could not be sent properly.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $this->__('The message could not be sent properly.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'swiftmailerHtml' => $paramHtml
        ];
    }
}
