<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula_Core;
use Zikula_Request_Http as Request;

/**
 * Class AbstractCoreInstallerCmd
 */
abstract class AbstractCoreInstallerCommand extends ContainerAwareCommand
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     * @see \Zikula\Bundle\CoreInstallerBundle\Command\Install\StartCommand
     */
    protected $settings = [
        /* Database */
        'database_host' => [
            'description' => 'The location of your database, most of the times "localhost".',
            'default' => 'localhost'
        ],
        'database_user' => [
            'description' => 'The database user.',
            'default' => null
        ],
        'database_password' => [
            'description' => 'Your database user\'s password.',
            'default' => null,
        ],
        'database_name' => [
            'description' => 'The name of the database.',
            'default' => null
        ],
        'database_driver' => [
            'description' => 'Your database driver.',
            'default' => 'mysql'
        ],
        'dbtabletype' => [
            'description' => '@todo ?',
            'default' => 'myisam'
        ],
        /* Admin user */
        'username' => [
            'description' => 'Username of the new Zikula admin user.',
            'default' => 'admin'
        ],
        'password' => [
            'description' => 'Password of the new Zikula admin user.',
            'default' => null,
        ],
        'email' => [
            'description' => 'Email of the new Zikula admin user.',
            'default' => null
        ],
        /* Http settings */
        'router:request_context:host' => [
            'description' => 'The host where you install Zikula, e.g. "example.com". Do not include subdirectories.',
            'default' => null,
        ],
        'router:request_context:scheme' => [
            'description' => 'The scheme of where you install Zikula, can be either "http" or "https".',
            'default' => 'http',
        ],
        'router:request_context:base_url' => [
            'description' => 'The url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            'default' => '',
        ],
        'locale' => [
            'description' => 'The locale to use.',
            'default' => 'en'
        ]
    ];

    protected function bootstrap($disableSessions = true, $fakeRequest = true)
    {
        define('_ZINSTALLVER', \Zikula_Core::VERSION_NUM);
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
        $failures = [];
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
        $messages = [
            'phpsatisfied' => $this->translator->__f("You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version %s or later.", [phpversion(), Zikula_Core::PHP_MINIMUM_VERSION]),
            'datetimezone' => $this->translator->__("date.timezone is currently not set.  It needs to be set to a valid timezone in your php.ini such as timezone like UTC, GMT+5, Europe/Berlin."),
            'pdo' => $this->translator->__("Your PHP installation doesn't have the PDO extension loaded."),
            'phptokens' => $this->translator->__("You have got a problem! Your PHP installation does not have the token functions available, but they are necessary for Zikula's output system."),
            'mbstring' => $this->translator->__("Your PHP installation does not have the multi-byte string functions available. Zikula needs this to handle multi-byte character sets."),
            'pcreUnicodePropertiesEnabled' => $this->translator->__("Your PHP installation's PCRE library does not have Unicode property support enabled. Zikula needs this to handle multi-byte character sets in regular expressions. The PCRE library used with PHP must be compiled with the '--enable-unicode-properties' option."),
            'json_encode' => $this->translator->__("Your PHP installation does not have the JSON functions available. Zikula needs this to handle AJAX requests."),
            'config_personal_config_php' => $this->translator->__f("'%s' has been found. This is not OK: please rename this file before continuing the installation process.", "config/personal_config.php"),
//            'custom_parameters_yml' => $this->translator->__f("'%s' has been found. This is not OK: please rename this file before continuing the installation process.", "app/config/custom_parameters.yml"),
        ];
        if (array_key_exists($key, $messages)) {
            return $messages[$key];
        }

        // remaining keys are filenames
        return $this->translator->__f("You have a problem! '%s' is not writeable. Please ensure that the permissions are set correctly for the installation process.", $key);
    }

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->translator = $container->get('translator.default');
    }
}
