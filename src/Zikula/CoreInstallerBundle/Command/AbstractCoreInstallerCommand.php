<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LocaleType;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\RequestContextType;
use Zikula\MailerModule\Form\Type\MailTransportConfigType;
use Zikula\MailerModule\Helper\MailTransportHelper;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

abstract class AbstractCoreInstallerCommand extends Command
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ZikulaHttpKernelInterface
     */
    protected $kernel;

    /**
     * @var LocaleApiInterface
     */
    protected $localeApi;

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
        'database_port' => [
            'description' => 'Optional custom database port number.',
            'default' => null
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
            'description' => 'The root domain where you install Zikula, e.g. "example.com". Do not include subdirectories.',
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
        ],
        /* mailer settings */
        'transport' => [
            'description' => 'The mailer transport to use.',
            'default' => 'test'
        ],
        'mailer_id' => [
            'description' => 'The ACCESS_KEY, USERNAME, ID or apikey for the selected transport.',
            'default' => null
        ],
        'mailer_key' => [
            'description' => 'The SECRET_KEY, PASSWORD, ID or KEY for the selected transport.',
            'default' => null
        ],
        'host' => [
            'description' => 'SMTP host server',
            'default' => null
        ],
        'port' => [
            'description' => 'SMTP port',
            'default' => null
        ],
        'customParameters' => [
            'description' => 'Use query parameters syntax, for example: <code>?param1=value1&amp;param2=value2</code>.',
            'default' => null
        ],
        'enableLogging' => [
            'description' => 'Enable logging of sent mail.',
            'default' => false
        ],
    ];

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        LocaleApiInterface $localeApi
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->localeApi = $localeApi;
    }

    protected function printWarnings(OutputInterface $output, $warnings): void
    {
        foreach ($warnings as $warning) {
            $output->writeln('<error>' . $warning . '</error>');
        }
    }

    protected function doRequestContext(InputInterface $input, OutputInterface $output, StyleInterface $io): array
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Request context'));
        }
        $data = $this->getHelper('form')->interactUsingForm(RequestContextType::class, $input, $output);
        foreach ($data as $k => $v) {
            $newKey = str_replace(':', '.', $k);
            $data[$newKey] = $v;
            unset($data[$k]);
        }

        return $data;
    }

    protected function doLocale(InputInterface $input, OutputInterface $output, StyleInterface $io): array
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Locale'));
        }

        return $this->getHelper('form')->interactUsingForm(LocaleType::class, $input, $output, [
            'choices' => $this->localeApi->getSupportedLocaleNames(),
            'choice_loader' => null
        ]);
    }

    protected function doMailer(InputInterface $input, OutputInterface $output, StyleInterface $io) // bool|array
    {
        if ($input->isInteractive()) {
            $io->newLine();
            $io->section($this->translator->trans('Mailer transport'));
            $io->note($this->translator->trans('Empty values are allowed for all except Mailer transport.'));
        }
        $data = $this->getHelper('form')->interactUsingForm(MailTransportConfigType::class, $input, $output);
        $mailDsnWrite = (new MailTransportHelper($this->kernel->getProjectDir()))->handleFormData($data);
        if ($mailDsnWrite) {
            return $this->encodeArrayValues($data);
        }

        return false;
    }

    protected function encodeArrayValues(array $data): array
    {
        foreach ($data as $k => $v) {
            $data[$k] = is_string($v) ? base64_encode($v) : $v; // encode so values are 'safe' for json
        }

        return $data;
    }

    protected function printSettings($givenSettings, SymfonyStyle $io): void
    {
        $rows = [];
        foreach ($givenSettings as $name => $givenSetting) {
            if (isset($this->settings[$name]['password']) && $this->settings[$name]['password']) {
                $givenSetting = str_repeat('*', mb_strlen($givenSetting));
            }
            $rows[] = [$name, $givenSetting];
        }
        $io->table([$this->translator->trans('Parameter'), $this->translator->trans('Value')], $rows);
    }
}
