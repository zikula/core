<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule;

use Zikula\Core\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the mailer module.
 */
class MailerModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Install the ZikulaMailerModule application.
     *
     * @return boolean True on success, or false
     *
     * @throws \RuntimeException Thrown if database tables can not be created or another error occurs
     */
    public function install()
    {
        $this->setVars($this->getDefaults());

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the ZikulaMailerModule application from an older version.
     *
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldVersion Version to upgrade from
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \RuntimeException Thrown if database tables can not be updated
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.3.1':
                $this->setVar('smtpsecuremethod', 'ssl');
            case '1.3.2':
                // clear old modvars
                // use manual method because getVars() is not available during system upgrade
                $modVarEntities = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionVarEntity')->findBy(['modname' => $this->name]);
                $modVars = [];
                foreach ($modVarEntities as $var) {
                    $modVars[$var['name']] = $var['value'];
                }
                $this->delVars();
                $this->setVarWithDefault('charset', $modVars['charset']);
                $this->setVarWithDefault('encoding', $modVars['encoding']);
                $this->setVarWithDefault('html', $modVars['html']);
                $this->setVarWithDefault('wordwrap', $modVars['wordwrap']);
                // new modvar for 1.4.0
                $this->setVarWithDefault('enableLogging', false);

                // write the config file
                $mailerTypeConversion = [
                    1 => 'mail',
                    2 => 'sendmail',
                    3 => 'mail',
                    4 => 'smtp',
                    5 => 'mail',
                ];
                $config = [
                    'transport' => $mailerTypeConversion[$modVars['mailertype']],
                    'username' => $modVars['smtpusername'],
                    'password' => $modVars['smtppassword'],
                    'host' => $modVars['smtpserver'],
                    'port' => $modVars['smtpport'],
                    'encryption' => (isset($modVars['smtpsecuremethod']) && in_array($modVars['smtpsecuremethod'], ['ssl', 'tls']) ? $modVars['smtpsecuremethod'] : 'ssl'),
                    'auth_mode' => (!empty($modVars['auth'])) ? 'login' : null,
                    'spool' => ['type' => 'memory'],
                    'delivery_addresses' => [],
                    'disable_delivery' => 5 == $modVars['mailertype'],
                ];
                $configDumper = $this->container->get('zikula.dynamic_config_dumper');
                $configDumper->setConfiguration('swiftmailer', $config);

            case '1.4.0':
                $configDumper = $this->container->get('zikula.dynamic_config_dumper');
                $config = $configDumper->getConfiguration('swiftmailer');
                // remove spool parameter
                unset($config['spool']);
                $configDumper->setConfiguration('swiftmailer', $config);
            case '1.4.1':
                // install subscriber hooks
            case '1.4.2':
                $configDumper = $this->container->get('zikula.dynamic_config_dumper');
                $config = $configDumper->getConfiguration('swiftmailer');
                // delivery_address has changed to an array named delivery_addresses
                $config['delivery_addresses'] = !empty($config['delivery_address']) ? [$config['delivery_address']] : [];
                unset($config['delivery_address']);
                $configDumper->setConfiguration('swiftmailer', $config);
            case '1.4.3':
                // nothing
            case '1.5.0':
            case '1.5.1':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * Uninstall ZikulaMailerModule.
     *
     * @return boolean True on success, false otherwise
     *
     * @throws \RuntimeException Thrown if database tables or stored workflows can not be removed
     */
    public function uninstall()
    {
        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }

    /**
     * Default module vars.
     *
     * @return array
     */
    private function getDefaults()
    {
        return [
            'charset' => $this->container->get('kernel')->getCharset(),
            'encoding' => '8bit',
            'html' => false,
            'wordwrap' => 50,
            'enableLogging' => false,
        ];
    }

    /**
     * set the module var but if it is not set, use the default instead.
     *
     * @param string $key
     * @param null $value
     */
    private function setVarWithDefault($key, $value = null)
    {
        if (isset($value)) {
            $this->setVar($key, $value);
        }
        $defaults = $this->getDefaults();
        $this->setVar($key, $defaults[$key]);
    }
}
