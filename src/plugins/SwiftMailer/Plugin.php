<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version.
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * SwiftMailer plugin definition.
 */
class SystemPlugin_SwiftMailer_Plugin extends Zikula_AbstractPlugin implements Zikula_Plugin_ConfigurableInterface, Zikula_Plugin_AlwaysOnInterface
{
    /**
     * Get plugin meta data.
     *
     * @return array Meta data.
     */
    protected function getMeta()
    {
        return array(
                'displayname' => $this->__('SwiftMailer'),
                'description' => $this->__('Provides SwiftMailer'),
                'version' => '4.1.2'
        );
    }

    /**
     * Initialise.
     *
     * Runs ar plugin init time.
     *
     * @throws InvalidArgumentException If invalid configuration given.
     *
     * @return void
     */
    public function initialize()
    {
        define('SWIFT_REQUIRED_LOADED', true);
        define('SWIFT_INIT_LOADED', true);

        // register namespace
        ZLoader::addAutoloader('Swift', dirname(__FILE__) . '/lib/vendor/SwiftMailer/classes');

        // initialize Swift
        //require_once realpath($this->baseDir . '/lib/vendor/SwiftMailer/swift_init.php'); // dont use this as it fails in virtual hosting environments with open_basedir restrictions
        // Load in dependency maps
        require_once dirname(__FILE__) . '/lib/vendor/SwiftMailer/dependency_maps/cache_deps.php';
        require_once dirname(__FILE__) . '/lib/vendor/SwiftMailer/dependency_maps/mime_deps.php';
        require_once dirname(__FILE__) . '/lib/vendor/SwiftMailer/dependency_maps/transport_deps.php';

        // load configuration (todo: move this to persistence).
        include dirname(__FILE__) . '/configuration/config.php';

        $this->serviceManager['swiftmailer.preferences.sendmethod'] = $config['sendmethod'];

        $preferences = Swift_Preferences::getInstance();
        $this->serviceManager['swiftmailer.preferences.charset'] = $config['charset'];
        $this->serviceManager['swiftmailer.preferences.cachetype'] = $config['cachetype'];
        $this->serviceManager['swiftmailer.preferences.tempdir'] = $config['tempdir'];
        $preferences->setCharset($config['charset']);
        $preferences->setCacheType($config['cachetype']);
        $preferences->setTempDir($config['tempdir']);

        // determine the correct transport
        $type = $config['transport']['type'];
        $args = $config['transport'][$type];
        switch ($type) {
            case 'mail':
                $this->serviceManager['swiftmailer.transport.mail.extraparams'] = $args['extraparams'];
                $definition = new Zikula_ServiceManager_Definition('Swift_MailTransport', array(new Zikula_ServiceManager_Argument('swiftmailer.transport.mail.extraparams')));
                break;

            case 'smtp':
                $this->serviceManager['swiftmailer.transport.smtp.host'] = $args['host'];
                $this->serviceManager['swiftmailer.transport.smtp.port'] = $args['port'];
                $definition = new Zikula_ServiceManager_Definition('Swift_SmtpTransport', array(
                                new Zikula_ServiceManager_Argument('swiftmailer.transport.smtp.host'),
                                new Zikula_ServiceManager_Argument('swiftmailer.transport.smtp.port')));

                if ($args['username'] && $args['password']) {
                    $this->serviceManager['swiftmailer.transport.smtp.username'] = $args['username'];
                    $this->serviceManager['swiftmailer.transport.smtp.password'] = $args['password'];
                    $definition->addMethod('setUserName', new Zikula_ServiceManager_Argument('swiftmailer.transport.smtp.username'));
                    $definition->addMethod('setPassword', new Zikula_ServiceManager_Argument('swiftmailer.transport.smtp.password'));
                }
                if (isset($args['encryption'])) {
                    $this->serviceManager['swiftmailer.transport.smtp.encryption'] = $args['encryption'];
                    $definition->addMethod('setEncryption', new Zikula_ServiceManager_Argument('swiftmailer.transport.smtp.encryption'));
                }
                break;

            case 'sendmail':
                $this->serviceManager['swiftmailer.transport.mail.command'] = $args['command'];
                $definition = new Zikula_ServiceManager_Definition('Swift_SendmailTransport', array(new Zikula_ServiceManager_Argument('swiftmailer.transport.mail.command')));
                break;

            default:
                // error
                throw new InvalidArgumentException('Invalid transport type, must be mail, smtp or sendmail');
                break;
        }

        // register transport
        $this->serviceManager->registerService('swiftmailer.transport', $definition);

        // define and register mailer using transport service
        $definition = new Zikula_ServiceManager_Definition('Swift_Mailer', array(new Zikula_ServiceManager_Reference('swiftmailer.transport')));
        $this->serviceManager->registerService('mailer', $definition, false);

        // register simple mailer service
        $definition = new Zikula_ServiceManager_Definition('SystemPlugins_SwiftMailer_Mailer', array(new Zikula_ServiceManager_Reference('zikula.servicemanager')));
        $this->serviceManager->registerService('mailer.simple', $definition);
    }

    /**
     * Return controller instance.
     *
     * @return Zikula_Controller_AbstractPlugin
     */
    public function getConfigurationController()
    {
        return new SystemPlugin_SwiftMailer_Controller($this->serviceManager, $this);
    }
}
