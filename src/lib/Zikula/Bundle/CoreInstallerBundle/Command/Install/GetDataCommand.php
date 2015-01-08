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

namespace Zikula\Bundle\CoreInstallerBundle\Command\Install;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Validator\Constraints\Null;
use Zikula_Core;
use Zikula_Request_Http as Request;
use Zikula\Bundle\CoreBundle\YamlDumper;
use \Zikula\Bundle\CoreInstallerBundle\Stage\Install\AjaxInstallerStage;

class GetDataCommand extends ContainerAwareCommand
{
    private $settings = array(
        /* Database */
        'database_host' => array(
            'description' => 'The location of your database, most of the times "localhost".',
            "question" => 'Please enter the database host',
            'default' => 'localhost'
        ),
        'database_user' => array(
            'description' => 'The database user.',
            "question" => 'Please enter the database user',
            'default' => null
        ),
        'database_password' => array(
            'description' => 'Your database user\'s password.',
            "question" => 'Please enter the database password',
            'default' => null,
            'password' => true
        ),
        'database_name' => array(
            'description' => 'The name of the database.',
            "question" => 'Please enter the database name',
            'default' => null
        ),
        'database_driver' => array(
            'description' => 'Your database driver.',
            "question" => 'Please enter the database driver',
            'default' => 'mysql'
            /* @todo Choices */
        ),
        'dbtabletype' => array(
            'description' => '@todo ?',
            "question" => '@todo ?',
            'default' => 'myisam'
            /* @todo Choices */
        ),
        /* Admin user */
        'username' => array(
            'description' => 'Username of the new Zikula admin user.',
            "question" => 'Please enter the username of the new Zikula admin user',
            'default' => 'admin'
        ),
        'password' => array(
            'description' => 'Password of the new Zikula admin user.',
            "question" => 'Please enter the password of the new Zikula admin user',
            'default' => null,
            'password' => true
        ),
        'password_repeat' => array(
            'description' => 'Enter the password again for verification.',
            "question" => 'Please enter the password again for verification',
            'default' => null,
            'password' => true
        ),
        'email' => array(
            'description' => 'Email of the new Zikula admin user.',
            "question" => 'Please enter the email address of the new Zikula admin user',
            'default' => null
        ),
        /* Http settings */
        'router.request_context.host' => array(
            'description' => 'The host where you install Zikula, e.g. "example.com". Do not include subdirectories.',
            "question" => 'Please enter the host where you install Zikula, e.g. "example.com". Do not include subdirectories',
            'default' => null,
        ),
        'router.request_context.base_url' => array(
            'description' => 'The url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            "question" => 'Please enter the url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            'default' => '',
        ),
        'router.request_context.scheme' => array(
            'description' => 'The scheme of where you install Zikula, can be either "http" or "https".',
            "question" => 'Please enter the scheme of where you install Zikula, can be either "http" or "https"',
            'default' => 'http',
            /* @todo Choices */
        ),

    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zikula:install:getdata')
            ->setDescription('private: DO NOT CALL DIRECTLY')
        ;

        foreach ($this->settings as $name => $setting) {
            $this->addOption(
                $name,
                null,
                InputOption::VALUE_REQUIRED,
                $setting['description'],
                $setting['default']
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(array(
            "<info>---------------------------</info>",
            "| Zikula Installer Script |",
            "<info>---------------------------</info>"
        ));

        $this->bootstrap();

        if ($this->getContainer()->getParameter('installed') == true) {
            $output->writeln("<error>" . __('Zikula already appears to be installed.') . "</error>");
            return;
        }
        $warnings = $this->getContainer()->get('core_installer.controller.util')->initPhp();
        if (!empty($warnings)) {
            $this->printWarnings($output, $warnings);
            return;
        }
        $checks = $this->getContainer()->get('core_installer.controller.util')->requirementsMet($this->getContainer());
        if (true !== $checks) {
            $this->printRequirementsWarnings($output, $checks);
            return;
        }

        if ($input->isInteractive()) {
            $output->writeln("Please follow the instructions to install Zikula " . Zikula_Core::VERSION_NUM . ".");
        }
        $settings = array(
            /* Database */
            'database_host' => $this->getRequiredOption($input, $output, 'database_host'),
            'database_user' => $this->getRequiredOption($input, $output, 'database_user'),
            'database_password' => $this->getRequiredOption($input, $output, 'database_password'),
            'database_name' => $this->getRequiredOption($input, $output, 'database_name'),
            'database_driver' => $this->getRequiredOption($input, $output, 'database_driver'),
            'dbtabletype' => $this->getRequiredOption($input, $output, 'dbtabletype'),
            /* Admin account */
            'username' => $this->getRequiredOption($input, $output, 'username'),
            'password' => $this->getRequiredOption($input, $output, 'password'),
            'password_repeat' => $this->getRequiredOption($input, $output, 'password_repeat'),
            'email' => $this->getRequiredOption($input, $output, 'email'),
            /* Http settings */
            'router.request_context.host' => $this->getRequiredOption($input, $output, 'router.request_context.host'),
            'router.request_context.scheme' => $this->getRequiredOption($input, $output, 'router.request_context.scheme'),
            'router.request_context.base_url' => $this->getRequiredOption($input, $output, 'router.request_context.base_url'),
        );

        if ($input->isInteractive()) {
            // @todo validate! at least the admin password and username
            $output->writeln(array("", "", ""));
            $output->writeln("Configuration successful. Please verify your parameters below:");
        }

        $this->printSettings($settings, $output);
        $output->writeln("");

        if ($input->isInteractive()) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<info>Start installation? <comment>[yes/no]</comment></info>: ', true);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('<error>Installation aborted.</error>');

                return;
            }
        }
        // write the parameters to personal_config.php and custom_parameters.yml
        $yamlManager = new YamlDumper($this->getContainer()->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml', 'parameters.yml');
        $params = array_merge($yamlManager->getParameters(), $settings);
        $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in custom_parameters.yml
        unset($params['password_repeat']);
        $yamlManager->setParameters($params);
        $this->getContainer()->get('core_installer.config.util')->writeLegacyConfig($params);
        $this->getContainer()->get('zikula.cache_clearer')->clear('symfony.config');
    }

    private function getRequiredOption(InputInterface $input, OutputInterface $output, $option)
    {
        if (!$input->isInteractive()) {
            return $input->getOption($option);
        }

        $setting = $this->settings[$option];
        if ($input->getOption($option) !== null) {
            $setting['default'] = $input->getOption($option);
        }
        $default = '';
        if ($setting['default'] !== null) {
            $default = ' <comment>default:[' . $setting['default'] . ']</comment>';
        }
        $question = new Question("<info>" . $setting['question'] . "$default:</info> ", $setting['default']);
        if (isset($setting['password']) && $setting['password']) {
            $question->setHidden(true);
        }
        $helper = $this->getHelper('question');

        return $helper->ask($input, $output, $question);
    }

    private function printSettings($givenSettings, OutputInterface $output)
    {
        foreach ($givenSettings as $name => $givenSetting) {
            if (isset($this->settings[$name]['password']) && $this->settings[$name]['password']) {
                $givenSetting = str_repeat("*", strlen($givenSetting));
            }
            $output->writeln("<info>$name:</info> <comment>$givenSetting</comment>");
        }
    }

    private function bootstrap()
    {
        define('_ZINSTALLVER', \Zikula_Core::VERSION_NUM);

        if (!$this->getContainer()->has('zikula')) {
            $kernel = $this->getContainer()->get('kernel');
            $loader = require($kernel->getRootDir() . '/autoload.php');
            \ZLoader::register($loader);

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

        // Disable sessions.
        $this->getContainer()->set('session.storage', new MockArraySessionStorage());
        $this->getContainer()->set('session.handler', new NullSessionHandler());

        // Fake request
        $request = Request::create('http://localhost/install');
        $this->getContainer()->set('request', $request);
    }

    private function printWarnings(OutputInterface $output, $warnings)
    {
        foreach ($warnings as $warning) {
            $output->writeln("<error>$warning</error>");
        }
    }

    private function printRequirementsWarnings(OutputInterface $output, $warnings)
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
            'phpsatisfied' => __f("You have got a problem! Your PHP version is %s, which does not satisfy the Zikula system requirement of version 5.3.3 or later.", phpversion()),
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
