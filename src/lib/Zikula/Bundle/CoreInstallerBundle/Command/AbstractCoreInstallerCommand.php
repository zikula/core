<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zikula_Core;
use Zikula_Request_Http as Request;

/**
 * Class AbstractCoreInstallerCmd
 * @package Zikula\Bundle\CoreInstallerBundle\Command\Install
 */
abstract class AbstractCoreInstallerCommand extends ContainerAwareCommand
{
    /**
     * @var array
     * @see \Zikula\Bundle\CoreInstallerBundle\Command\Install\StartCommand
     */
    protected $settings = array(
        /* Database */
        'database_host' => array(
            'description' => 'The location of your database, most of the times "localhost".',
            'default' => 'localhost'
        ),
        'database_user' => array(
            'description' => 'The database user.',
            'default' => null
        ),
        'database_password' => array(
            'description' => 'Your database user\'s password.',
            'default' => null,
        ),
        'database_name' => array(
            'description' => 'The name of the database.',
            'default' => null
        ),
        'database_driver' => array(
            'description' => 'Your database driver.',
            'default' => 'mysql'
        ),
        'dbtabletype' => array(
            'description' => '@todo ?',
            'default' => 'myisam'
        ),
        /* Admin user */
        'username' => array(
            'description' => 'Username of the new Zikula admin user.',
            'default' => 'admin'
        ),
        'password' => array(
            'description' => 'Password of the new Zikula admin user.',
            'default' => null,
        ),
        'email' => array(
            'description' => 'Email of the new Zikula admin user.',
            'default' => null
        ),
        /* Http settings */
        'router:request_context:host' => array(
            'description' => 'The host where you install Zikula, e.g. "example.com". Do not include subdirectories.',
            'default' => null,
        ),
        'router:request_context:scheme' => array(
            'description' => 'The scheme of where you install Zikula, can be either "http" or "https".',
            'default' => 'http',
        ),
        'router:request_context:base_url' => array(
            'description' => 'The url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            'default' => '',
        ),
    );

    protected function bootstrap($disableSessions = true, $loadZikulaCore = true, $fakeRequest = true)
    {
        define('_ZINSTALLVER', \Zikula_Core::VERSION_NUM);
        $kernel = $this->getContainer()->get('kernel');
        $loader = require($kernel->getRootDir() . '/autoload.php');
        \ZLoader::register($loader);

        if ($loadZikulaCore && !$this->getContainer()->has('zikula')) {
            $core = new Zikula_Core();
            $core->setKernel($kernel);
            $core->boot();

            foreach ($GLOBALS['ZConfig'] as $config) {
                $core->getContainer()->loadArguments($config);
            }
            $GLOBALS['ZConfig']['System']['temp'] = $core->getContainer()->getParameter('temp_dir');
            $GLOBALS['ZConfig']['System']['datadir'] = $core->getContainer()->getParameter('datadir');
            $GLOBALS['ZConfig']['System']['system.chmod_dir'] = $core->getContainer()->getParameter('system.chmod_dir');

            \ServiceUtil::getManager($core);
            \EventUtil::getManager($core);
        }

        if ($disableSessions) {
            // Disable sessions.
            $this->getContainer()->set('session.storage', new MockArraySessionStorage());
            $this->getContainer()->set('session.handler', new NullSessionHandler());
        }

        if ($fakeRequest) {
            // Fake request
            $request = Request::create('http://localhost/install');
            $this->getContainer()->set('request', $request);
        }
    }

    protected function printWarnings(OutputInterface $output, $warnings)
    {
        foreach ($warnings as $warning) {
            $output->writeln("<error>$warning</error>");
        }
    }

    protected function printRequirementsWarnings(OutputInterface $output, $warnings)
    {
        $failures = array();
        foreach ($warnings as $key => $value) {
            if ($value === true) {
                continue;
            }
            $failures[] = $this->errorCodeToMessage($key);
        }
        $this->printWarnings($output, $failures);
    }

    private function errorCodeToMessage($key)
    {
        $messages = array(
            'phpsatisfied' => __f("You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version %s or later.", array(phpversion(), Zikula_Core::PHP_MINIMUM_VERSION)),
            'datetimezone' => __("date.timezone is currently not set.  It needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin."),
            'register_globals' => __("PHP register_globals = On and must be turned off in php.ini, or .htaccess"),
            'magic_quotes_gpc' => __("PHP magic_quotes_gpc = On and must be turned off in php.ini"),
            'pdo' => __("Your PHP installation doesn't have the PDO extension loaded."),
            'phptokens' => __("You have got a problem! Your PHP installation does not have the token functions available, but they are necessary for Zikula's output system."),
            'mbstring' => __("Your PHP installation does not have the multi-byte string functions available. Zikula needs this to handle multi-byte character sets."),
            'pcreUnicodePropertiesEnabled' => __("Your PHP installation's PCRE library does not have Unicode property support enabled. Zikula needs this to handle multi-byte character sets in regular expressions. The PCRE library used with PHP must be compiled with the '--enable-unicode-properties' option."),
            'json_encode' => __("Your PHP installation does not have the JSON functions available. Zikula needs this to handle AJAX requests."),
            'config_personal_config_php' => __f("'%s' has been found. This is not OK: please rename this file before continuing the installation process.", "config/personal_config.php"),
//            'custom_parameters_yml' => __f("'%s' has been found. This is not OK: please rename this file before continuing the installation process.", "app/config/custom_parameters.yml"),
        );
        if (array_key_exists($key, $messages)) {
            return $messages[$key];
        } else {
            // remaining keys are filenames
            return __f("You have a problem! '%s' is not writeable. Please ensure that the permissions are set correctly for the installation process.", $key);
        }
    }
}
