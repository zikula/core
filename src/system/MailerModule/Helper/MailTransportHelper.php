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

namespace Zikula\MailerModule\Helper;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Zikula\Bundle\CoreBundle\Helper\LocalDotEnvHelper;

class MailTransportHelper
{
    /**
     * @var string
     */
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function handleFormData(array $formData): bool
    {
        if (!isset($formData['transport'])) {
            throw new \InvalidArgumentException('Transport must be set.');
        }
        $transportStrings = [
            'smtp' => 'smtp://${MAILER_ID}:${MAILER_KEY}@',
            'sendmail' => 'sendmail+smtp://default',
            'amazon' => 'ses://${MAILER_ID}:${MAILER_KEY}@default',
            'gmail' => 'gmail://${MAILER_ID}:${MAILER_KEY}@default',
            'mailchimp' => 'mandrill://${MAILER_ID}:${MAILER_KEY}@default',
            'mailgun' => 'mailgun://${MAILER_ID}:${MAILER_KEY}@default',
            'postmark' => 'postmark://${MAILER_ID}:${MAILER_KEY}@default',
            'sendgrid' => 'sendgrid://apikey:${MAILER_KEY}@default', // unclear if 'apikey' is supposed to be literal, or replaced
            'test' => 'null://null',
        ];
        try {
            $dsn = $transportStrings[$formData['transport']];
            if ('smtp' === $formData['transport']) {
                $dsn .= $formData['host'] ?? 'localhost';
                if (!empty($formData['port'])) {
                    $dsn .= ':' . $formData['port'];
                }
            }
            if (!empty($formData['customParameters'])) {
                $dsn .= $formData['customParameters'];
            }
            $vars = [];
            if (!empty($formData['mailer_id'])) {
                $vars['MAILER_ID'] = $formData['mailer_id'];
            }
            if (!empty($formData['mailer_key'])) {
                $vars['MAILER_KEY'] = $formData['mailer_key'];
            }
            $vars['MAILER_DSN'] = '!' . $dsn;
            $helper = new LocalDotEnvHelper($this->projectDir);
            $helper->writeLocalEnvVars($vars);

            return true;
        } catch (IOExceptionInterface $exception) {
            return false;
        }
    }
}
