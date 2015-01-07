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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Validator\Constraints\Null;
use Zikula_Core;
use Zikula_Request_Http as Request;

class InstallCommand extends ContainerAwareCommand
{
    private $settings = array(
        /* Database */
        'db-host' => array(
            'description' => 'The location of your database, most of the times "localhost".',
            "question" => 'Please enter the database host',
            'default' => 'localhost'
        ),
        'db-user' => array(
            'description' => 'The database user.',
            "question" => 'Please enter the database user',
            'default' => null
        ),
        'db-password' => array(
            'description' => 'Your database user\'s password.',
            "question" => 'Please enter the database password',
            'default' => null,
            'password' => true
        ),
        'db-name' => array(
            'description' => 'The name of the database.',
            "question" => 'Please enter the database name',
            'default' => null
        ),
        'db-driver' => array(
            'description' => 'Your database driver.',
            "question" => 'Please enter the database driver',
            'default' => 'mysql'
            /* @todo Choices */
        ),
        'db-table-type' => array(
            'description' => '@todo ?',
            "question" => '@todo ?',
            'default' => 'innodb'
            /* @todo Choices */
        ),
        /* Admin user */
        'admin-user' => array(
            'description' => 'Username of the new Zikula admin user.',
            "question" => 'Please enter the username of the new Zikula admin user',
            'default' => 'admin'
        ),
        'admin-email' => array(
            'description' => 'Email of the new Zikula admin user.',
            "question" => 'Please enter the email address of the new Zikula admin user',
            'default' => null
        ),
        'admin-password' => array(
            'description' => 'Password of the new Zikula admin user.',
            "question" => 'Please enter the password of the new Zikula admin user',
            'default' => null,
            'password' => true
        ),
        /* Http settings */
        'http-host' => array(
            'description' => 'The host where you install Zikula, e.g. "example.com". Do not include subdirectories.',
            "question" => 'Please enter the host where you install Zikula, e.g. "example.com". Do not include subdirectories',
            'default' => null,
        ),
        'http-path' => array(
            'description' => 'The url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            "question" => 'Please enter the url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
            'default' => '',
        ),
        'http-scheme' => array(
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
            ->setName('zikula:install')
            ->setDescription('Install Zikula')
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
//        $output->writeln(array(
//            "<info>---------------------------</info>",
//            "| Zikula Installer Script |",
//            "<info>---------------------------</info>"
//        ));
//
//        $installer = $this->createInstaller();
//        $installer->printIniWarnings($output);
//        $checks = $installer->preInstallChecks();
//        if ($checks !== true){
//            if (is_int($checks)) {
//                $installer->handleError($checks, $output);
//
//                return;
//            }
//            $output->writeln("<error>Some requirements to install Zikula are missing:</error>");
//            $installer->printRequirementsWarnings($checks, $output);
//
//            return;
//        }
//
//
//        if ($input->isInteractive()) {
//            $output->writeln("Please follow the instructions to install Zikula " . Zikula_Core::VERSION_NUM . ".");
//        }
//        $settings = array(
//            /* Database */
//            'db-host' => $this->getRequiredOption($input, $output, 'db-host'),
//            'db-user' => $this->getRequiredOption($input, $output, 'db-user'),
//            'db-password' => $this->getRequiredOption($input, $output, 'db-password'),
//            'db-name' => $this->getRequiredOption($input, $output, 'db-name'),
//            'db-driver' => $this->getRequiredOption($input, $output, 'db-driver'),
//            'db-table-type' => $this->getRequiredOption($input, $output, 'db-table-type'),
//            /* Admin account */
//            'admin-user' => $this->getRequiredOption($input, $output, 'admin-user'),
//            'admin-email' => $this->getRequiredOption($input, $output, 'admin-email'),
//            'admin-password' => $this->getRequiredOption($input, $output, 'admin-password'),
//            /* Http settings */
//            'http-host' => $this->getRequiredOption($input, $output, 'http-host'),
//            'http-scheme' => $this->getRequiredOption($input, $output, 'http-scheme'),
//            'http-path' => $this->getRequiredOption($input, $output, 'http-path'),
//        );
//
//        if ($input->isInteractive()) {
//            $output->writeln(array("", "", ""));
//            $output->writeln("Configuration successful. Please verify your parameters below:");
//        }
//
//        $this->printSettings($settings, $output);
//        $output->writeln("");
//
//        if ($input->isInteractive()) {
//            $helper = $this->getHelper('question');
//            $question = new ConfirmationQuestion('<info>Start installation?</info> <comment>[yes/no]</comment>', true);
//
//            if (!$helper->ask($input, $output, $question)) {
//                $output->writeln('<error>Installation aborted.</error>');
//
//                return;
//            }
//        }
//
//        // Install...
//        $installer->install($settings, $output);
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
            $default = ' <comment>[' . $setting['default'] . ']</comment>';
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

    /**
     * Creates an installer instance.
     *
     * @return \CommandLineInstaller
     */
//    protected function createInstaller()
//    {
//        $loader = require(__DIR__ . '/../../../../../app/autoload.php');
//        \ZLoader::register($loader);
//
//        $core = new Zikula_Core();
//        $core->setKernel($this->getContainer()->get('kernel'));
//        $core->boot();
//
//        foreach ($GLOBALS['ZConfig'] as $config) {
//            $core->getContainer()->loadArguments($config);
//        }
//        $GLOBALS['ZConfig']['System']['temp'] = $core->getContainer()->getParameter('temp_dir');
//        $GLOBALS['ZConfig']['System']['datadir'] = $core->getContainer()->getParameter('datadir');
//        $GLOBALS['ZConfig']['System']['system.chmod_dir'] = $core->getContainer()->getParameter('system.chmod_dir');
//
//        \ServiceUtil::getManager($core);
//        \EventUtil::getManager($core);
//        $core->attachHandlers('config/EventHandlers');
//
//        // Disable sessions.
//        $this->getContainer()->set('session.storage', new MockArraySessionStorage());
//        $this->getContainer()->set('session.handler', new NullSessionHandler());
//
//
//        require_once(__DIR__ . '/../../../../../install/CommandLineInstaller.php');
//
//        // Fake request
//        $request = Request::create('http://localhost/install');
//        $installer = new \CommandLineInstaller($core, $request);
//
//        return $installer;
//    }
}
