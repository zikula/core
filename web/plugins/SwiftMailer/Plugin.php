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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Zikula\Framework\Plugin\ConfigurableInterface;
use Zikula\Framework\Plugin\AlwaysOnInterface;
use Zikula\Framework\AbstractPlugin;

/**
 * SwiftMailer plugin definition.
 */
class SystemPlugin_SwiftMailer_Plugin extends AbstractPlugin implements ConfigurableInterface, AlwaysOnInterface
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
        //define('SWIFT_REQUIRED_LOADED', true);
        define('SWIFT_INIT_LOADED', true);

        // register namespace
        \ZLoader::addAutoloader('Swift', __DIR__.'/../../../vendor/swiftmailer/swiftmailer/lib/classes', '_');

        $basePath = realpath(__DIR__.'/../../../vendor/swiftmailer/swiftmailer/lib');

        // initialize Swift
        //require_once realpath($this->baseDir . '/lib/vendor/SwiftMailer/swift_init.php'); // dont use this as it fails in virtual hosting environments with open_basedir restrictions
        // Load in dependency maps
        require_once $basePath . '/dependency_maps/cache_deps.php';
        require_once $basePath . '/dependency_maps/mime_deps.php';
        require_once $basePath . '/dependency_maps/transport_deps.php';

        // load configuration (todo: move this to persistence).
        // this include provides the following $config array.
        /* @var array $config */
        include __DIR__ . '/configuration/config.php';

        $this->container['swiftmailer.preferences.sendmethod'] = $config['sendmethod'];

        $preferences = \Swift_Preferences::getInstance();
        $this->container['swiftmailer.preferences.charset'] = $config['charset'];
        $this->container['swiftmailer.preferences.cachetype'] = $config['cachetype'];
        $this->container['swiftmailer.preferences.tempdir'] = $config['tempdir'];
        $preferences->setCharset($config['charset']);
        $preferences->setCacheType($config['cachetype']);
        $preferences->setTempDir($config['tempdir']);

        // determine the correct transport
        $type = $config['transport']['type'];
        $args = $config['transport'][$type];
        switch ($type) {
            case 'mail':
                $this->container['swiftmailer.transport.mail.extraparams'] = $args['extraparams'];
                $definition = new Definition('Swift_MailTransport', array(new Parameter('swiftmailer.transport.mail.extraparams')));
                break;

            case 'smtp':
                $this->container['swiftmailer.transport.smtp.host'] = $args['host'];
                $this->container['swiftmailer.transport.smtp.port'] = $args['port'];
                $definition = new Definition('Swift_SmtpTransport', array(
                                new Parameter('swiftmailer.transport.smtp.host'),
                                new Parameter('swiftmailer.transport.smtp.port')));

                if ($args['username'] && $args['password']) {
                    $this->container['swiftmailer.transport.smtp.username'] = $args['username'];
                    $this->container['swiftmailer.transport.smtp.password'] = $args['password'];
                    $definition->addMethodCall('setUserName', new Parameter('swiftmailer.transport.smtp.username'));
                    $definition->addMethodCall('setPassword', new Parameter('swiftmailer.transport.smtp.password'));
                }
                if (isset($args['encryption'])) {
                    $this->container['swiftmailer.transport.smtp.encryption'] = $args['encryption'];
                    $definition->addMethodCall('setEncryption', new Parameter('swiftmailer.transport.smtp.encryption'));
                }
                break;

            case 'sendmail':
                $this->container['swiftmailer.transport.mail.command'] = $args['command'];
                $definition = new Definition('Swift_SendmailTransport', array(new Parameter('swiftmailer.transport.mail.command')));
                break;

            default:
                // error
                throw new \InvalidArgumentException('Invalid transport type, must be mail, smtp or sendmail');
                break;
        }

        // register transport
        $this->container->setDefinition('swiftmailer.transport', $definition);

        // define and register mailer using transport service
        $definition = new Definition('Swift_Mailer', array(new Reference('swiftmailer.transport')));
        $this->container->setDefinition('mailer', $definition, false);

        // register simple mailer service
        $definition = new Definition('SystemPlugins_SwiftMailer_Mailer', array(new Reference('service_container')));
        $this->container->setDefinition('mailer.simple', $definition);
    }

    /**
     * Return controller instance.
     *
     * @return Zikula_Controller_AbstractPlugin
     */
    public function getConfigurationController()
    {
        return new SystemPlugin_SwiftMailer_Controller($this->container, $this);
    }
}
