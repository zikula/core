<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Requirements;

/**
 * This class specifies all requirements and optional recommendations that
 * are necessary to run Symfony.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SymfonyRequirements extends RequirementCollection
{
    const REQUIRED_PHP_VERSION_3x = '5.5.9';
    const REQUIRED_PHP_VERSION_4x = '7.1.3';

    public function __construct($rootDir, $symfonyVersion = null)
    {
        /* mandatory requirements follow */

        $installedPhpVersion = phpversion();

        $rootDir = $this->getComposerRootDir($rootDir);
        $options = $this->readComposer($rootDir);

        $phpVersion = $symfonyVersion && version_compare($symfonyVersion, '4.0.0', '>=') ? self::REQUIRED_PHP_VERSION_4x : self::REQUIRED_PHP_VERSION_3x;

        $this->addRequirement(
            version_compare($installedPhpVersion, $phpVersion, '>='),
            sprintf('PHP version must be at least %s (%s installed)', $phpVersion, $installedPhpVersion),
            sprintf('You are running PHP version "<strong>%s</strong>", but Symfony needs at least PHP "<strong>%s</strong>" to run.
            Before using Symfony, upgrade your PHP installation, preferably to the latest version.',
                $installedPhpVersion, $phpVersion),
            sprintf('Install PHP %s or newer (installed version is %s)', $phpVersion, $installedPhpVersion)
        );

        $this->addRequirement(
            is_dir($rootDir.'/vendor/composer'),
            'Vendor libraries must be installed',
            'Vendor libraries are missing. Install composer following instructions from <a href="http://getcomposer.org/">http://getcomposer.org/</a>. '.
            'Then run "<strong>php composer.phar install</strong>" to install them.'
        );

        if (is_dir($cacheDir = $rootDir.'/'.$options['var-dir'].'/cache')) {
            $this->addRequirement(
                is_writable($cacheDir),
                sprintf('%s/cache/ directory must be writable', $options['var-dir']),
                sprintf('Change the permissions of "<strong>%s/cache/</strong>" directory so that the web server can write into it.', $options['var-dir'])
            );
        }

        if (is_dir($logsDir = $rootDir.'/'.$options['var-dir'].'/log')) {
            $this->addRequirement(
                is_writable($logsDir),
                sprintf('%s/log/ directory must be writable', $options['var-dir']),
                sprintf('Change the permissions of "<strong>%s/log/</strong>" directory so that the web server can write into it.', $options['var-dir'])
            );
        }

        if (version_compare($installedPhpVersion, '7.0.0', '<')) {
            $this->addPhpConfigRequirement(
                'date.timezone', true, false,
                'date.timezone setting must be set',
                'Set the "<strong>date.timezone</strong>" setting in php.ini<a href="#phpini">*</a> (like Europe/Paris).'
            );
        }

        if (version_compare($installedPhpVersion, $phpVersion, '>=')) {
            $this->addRequirement(
                in_array(@date_default_timezone_get(), \DateTimeZone::listIdentifiers(), true),
                sprintf('Configured default timezone "%s" must be supported by your installation of PHP', @date_default_timezone_get()),
                'Your default timezone is not supported by PHP. Check for typos in your <strong>php.ini</strong> file and have a look at the list of deprecated timezones at <a href="http://php.net/manual/en/timezones.others.php">http://php.net/manual/en/timezones.others.php</a>.'
            );
        }

        $this->addRequirement(
            function_exists('iconv'),
            'iconv() must be available',
            'Install and enable the <strong>iconv</strong> extension.'
        );

        $this->addRequirement(
            function_exists('json_encode'),
            'json_encode() must be available',
            'Install and enable the <strong>JSON</strong> extension.'
        );

        $this->addRequirement(
            function_exists('session_start'),
            'session_start() must be available',
            'Install and enable the <strong>session</strong> extension.'
        );

        $this->addRequirement(
            function_exists('ctype_alpha'),
            'ctype_alpha() must be available',
            'Install and enable the <strong>ctype</strong> extension.'
        );

        $this->addRequirement(
            function_exists('token_get_all'),
            'token_get_all() must be available',
            'Install and enable the <strong>Tokenizer</strong> extension.'
        );

        $this->addRequirement(
            function_exists('simplexml_import_dom'),
            'simplexml_import_dom() must be available',
            'Install and enable the <strong>SimpleXML</strong> extension.'
        );

        if (function_exists('apc_store') && ini_get('apc.enabled')) {
            if (version_compare($installedPhpVersion, '5.4.0', '>=')) {
                $this->addRequirement(
                    version_compare(phpversion('apc'), '3.1.13', '>='),
                    'APC version must be at least 3.1.13 when using PHP 5.4',
                    'Upgrade your <strong>APC</strong> extension (3.1.13+).'
                );
            } else {
                $this->addRequirement(
                    version_compare(phpversion('apc'), '3.0.17', '>='),
                    'APC version must be at least 3.0.17',
                    'Upgrade your <strong>APC</strong> extension (3.0.17+).'
                );
            }
        }

        $this->addPhpConfigRequirement('detect_unicode', false);

        if (extension_loaded('suhosin')) {
            $this->addPhpConfigRequirement(
                'suhosin.executor.include.whitelist',
                function($cfgValue) { return false !== stripos($cfgValue, 'phar'); },
                false,
                'suhosin.executor.include.whitelist must be configured correctly in php.ini',
                'Add "<strong>phar</strong>" to <strong>suhosin.executor.include.whitelist</strong> in php.ini<a href="#phpini">*</a>.'
            );
        }

        if (extension_loaded('xdebug')) {
            $this->addPhpConfigRequirement(
                'xdebug.show_exception_trace', false, true
            );

            $this->addPhpConfigRequirement(
                'xdebug.scream', false, true
            );

            $this->addPhpConfigRecommendation(
                'xdebug.max_nesting_level',
                function ($cfgValue) { return $cfgValue > 100; },
                true,
                'xdebug.max_nesting_level should be above 100 in php.ini',
                'Set "<strong>xdebug.max_nesting_level</strong>" to e.g. "<strong>250</strong>" in php.ini<a href="#phpini">*</a> to stop Xdebug\'s infinite recursion protection erroneously throwing a fatal error in your project.'
            );
        }

        $pcreVersion = defined('PCRE_VERSION') ? (float) PCRE_VERSION : null;

        $this->addRequirement(
            null !== $pcreVersion,
            'PCRE extension must be available',
            'Install the <strong>PCRE</strong> extension (version 8.0+).'
        );

        if (extension_loaded('mbstring')) {
            $this->addPhpConfigRequirement(
                'mbstring.func_overload',
                function ($cfgValue) { return (int) $cfgValue === 0; },
                true,
                'string functions should not be overloaded',
                'Set "<strong>mbstring.func_overload</strong>" to <strong>0</strong> in php.ini<a href="#phpini">*</a> to disable function overloading by the mbstring extension.'
            );
        }

        /* optional recommendations follow */

        if (null !== $pcreVersion) {
            $this->addRecommendation(
                $pcreVersion >= 8.0,
                sprintf('PCRE extension should be at least version 8.0 (%s installed)', $pcreVersion),
                '<strong>PCRE 8.0+</strong> is preconfigured in PHP since 5.3.2 but you are using an outdated version of it. Symfony probably works anyway but it is recommended to upgrade your PCRE extension.'
            );
        }

        $this->addRecommendation(
            class_exists('DomDocument'),
            'PHP-DOM and PHP-XML modules should be installed',
            'Install and enable the <strong>PHP-DOM</strong> and the <strong>PHP-XML</strong> modules.'
        );

        $this->addRecommendation(
            function_exists('mb_strlen'),
            'mb_strlen() should be available',
            'Install and enable the <strong>mbstring</strong> extension.'
        );

        $this->addRecommendation(
            function_exists('utf8_decode'),
            'utf8_decode() should be available',
            'Install and enable the <strong>XML</strong> extension.'
        );

        $this->addRecommendation(
            function_exists('filter_var'),
            'filter_var() should be available',
            'Install and enable the <strong>filter</strong> extension.'
        );

        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->addRecommendation(
                function_exists('posix_isatty'),
                'posix_isatty() should be available',
                'Install and enable the <strong>php_posix</strong> extension (used to colorize the CLI output).'
            );
        }

        $this->addRecommendation(
            extension_loaded('intl'),
            'intl extension should be available',
            'Install and enable the <strong>intl</strong> extension (used for validators).'
        );

        if (extension_loaded('intl')) {
            // in some WAMP server installations, new Collator() returns null
            $this->addRecommendation(
                null !== new \Collator('fr_FR'),
                'intl extension should be correctly configured',
                'The intl extension does not behave properly. This problem is typical on PHP 5.3.X x64 WIN builds.'
            );

            // check for compatible ICU versions (only done when you have the intl extension)
            if (defined('INTL_ICU_VERSION')) {
                $version = INTL_ICU_VERSION;
            } else {
                $reflector = new \ReflectionExtension('intl');

                ob_start();
                $reflector->info();
                $output = strip_tags(ob_get_clean());

                preg_match('/^ICU version +(?:=> )?(.*)$/m', $output, $matches);
                $version = $matches[1];
            }

            $this->addRecommendation(
                version_compare($version, '4.0', '>='),
                'intl ICU version should be at least 4+',
                'Upgrade your <strong>intl</strong> extension with a newer ICU version (4+).'
            );

            if (class_exists('Symfony\Component\Intl\Intl')) {
                $this->addRecommendation(
                    \Symfony\Component\Intl\Intl::getIcuDataVersion() <= \Symfony\Component\Intl\Intl::getIcuVersion(),
                    sprintf('intl ICU version installed on your system is outdated (%s) and does not match the ICU data bundled with Symfony (%s)', \Symfony\Component\Intl\Intl::getIcuVersion(), \Symfony\Component\Intl\Intl::getIcuDataVersion()),
                    'To get the latest internationalization data upgrade the ICU system package and the intl PHP extension.'
                );
                if (\Symfony\Component\Intl\Intl::getIcuDataVersion() <= \Symfony\Component\Intl\Intl::getIcuVersion()) {
                    $this->addRecommendation(
                        \Symfony\Component\Intl\Intl::getIcuDataVersion() === \Symfony\Component\Intl\Intl::getIcuVersion(),
                        sprintf('intl ICU version installed on your system (%s) does not match the ICU data bundled with Symfony (%s)', \Symfony\Component\Intl\Intl::getIcuVersion(), \Symfony\Component\Intl\Intl::getIcuDataVersion()),
                        'To avoid internationalization data inconsistencies upgrade the symfony/intl component.'
                    );
                }
            }

            $this->addPhpConfigRecommendation(
                'intl.error_level',
                function ($cfgValue) { return (int) $cfgValue === 0; },
                true,
                'intl.error_level should be 0 in php.ini',
                'Set "<strong>intl.error_level</strong>" to "<strong>0</strong>" in php.ini<a href="#phpini">*</a> to inhibit the messages when an error occurs in ICU functions.'
            );
        }

        $accelerator =
            (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))
            ||
            (extension_loaded('apc') && ini_get('apc.enabled'))
            ||
            (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable'))
            ||
            (extension_loaded('Zend OPcache') && ini_get('opcache.enable'))
            ||
            (extension_loaded('xcache') && ini_get('xcache.cacher'))
            ||
            (extension_loaded('wincache') && ini_get('wincache.ocenabled'))
        ;

        $this->addRecommendation(
            $accelerator,
            'a PHP accelerator should be installed',
            'Install and/or enable a <strong>PHP accelerator</strong> (highly recommended).'
        );

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->addRecommendation(
                $this->getRealpathCacheSize() >= 5 * 1024 * 1024,
                'realpath_cache_size should be at least 5M in php.ini',
                'Setting "<strong>realpath_cache_size</strong>" to e.g. "<strong>5242880</strong>" or "<strong>5M</strong>" in php.ini<a href="#phpini">*</a> may improve performance on Windows significantly in some cases.'
            );
        }

        $this->addPhpConfigRecommendation('short_open_tag', false);

        $this->addPhpConfigRecommendation('magic_quotes_gpc', false, true);

        $this->addPhpConfigRecommendation('register_globals', false, true);

        $this->addPhpConfigRecommendation('session.auto_start', false);

        $this->addPhpConfigRecommendation(
            'xdebug.max_nesting_level',
            function ($cfgValue) { return $cfgValue > 100; },
            true,
            'xdebug.max_nesting_level should be above 100 in php.ini',
            'Set "<strong>xdebug.max_nesting_level</strong>" to e.g. "<strong>250</strong>" in php.ini<a href="#phpini">*</a> to stop Xdebug\'s infinite recursion protection erroneously throwing a fatal error in your project.'
        );

        $this->addPhpConfigRecommendation(
            'post_max_size',
            $this->getPostMaxSize() < $this->getMemoryLimit(),
            true,
            '"memory_limit" should be greater than "post_max_size".',
            'Set "<strong>memory_limit</strong>" to be greater than "<strong>post_max_size</strong>".'
        );

        $this->addPhpConfigRecommendation(
            'upload_max_filesize',
            $this->getUploadMaxFilesize() < $this->getPostMaxSize(),
            true,
            '"post_max_size" should be greater than "upload_max_filesize".',
            'Set "<strong>post_max_size</strong>" to be greater than "<strong>upload_max_filesize</strong>".'
        );

        $this->addRecommendation(
            class_exists('PDO'),
            'PDO should be installed',
            'Install <strong>PDO</strong> (mandatory for Doctrine).'
        );

        if (class_exists('PDO')) {
            $drivers = \PDO::getAvailableDrivers();
            $this->addRecommendation(
                count($drivers) > 0,
                sprintf('PDO should have some drivers installed (currently available: %s)', count($drivers) ? implode(', ', $drivers) : 'none'),
                'Install <strong>PDO drivers</strong> (mandatory for Doctrine).'
            );
        }
    }

    /**
     * Convert a given shorthand size in an integer
     * (e.g. 16k is converted to 16384 int)
     *
     * @param string $size - Shorthand size
     *
     * @see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
     *
     * @return int - Converted size
     */
    private function convertShorthandSize($size)
    {
        // Initialize
        $size = trim($size);
        $unit = '';

        // Check unlimited alias
        if ($size === '-1') {
            return \INF;
        }

        // Check size
        if (!ctype_digit($size)) {
            $unit = strtolower(substr($size, -1, 1));
            $size = (int) substr($size, 0, -1);
        }

        // Return converted size
        switch ($unit) {
            case 'g':
                return $size * 1024 * 1024 * 1024;
            case 'm':
                return $size * 1024 * 1024;
            case 'k':
                return $size * 1024;
            default:
                return (int) $size;
        }
    }

    /**
     * Loads realpath_cache_size from php.ini and converts it to int.
     *
     * (e.g. 16k is converted to 16384 int)
     *
     * @return int
     */
    private function getRealpathCacheSize()
    {
        return $this->convertShorthandSize(ini_get('realpath_cache_size'));
    }

    /**
     * Loads post_max_size from php.ini and converts it to int.
     *
     * @return int
     */
    private function getPostMaxSize()
    {
        return $this->convertShorthandSize(ini_get('post_max_size'));
    }

    /**
     * Loads memory_limit from php.ini and converts it to int.
     *
     * @return int
     */
    private function getMemoryLimit()
    {
        return $this->convertShorthandSize(ini_get('memory_limit'));
    }

    /**
     * Loads upload_max_filesize from php.ini and converts it to int.
     *
     * @return int
     */
    private function getUploadMaxFilesize()
    {
        return $this->convertShorthandSize(ini_get('upload_max_filesize'));
    }

    private function getComposerRootDir($rootDir)
    {
        $dir = $rootDir;
        while (!file_exists($dir.'/composer.json')) {
            if ($dir === dirname($dir)) {
                return $rootDir;
            }

            $dir = dirname($dir);
        }

        return $dir;
    }

    private function readComposer($rootDir)
    {
        $composer = json_decode(file_get_contents($rootDir.'/composer.json'), true);
        $options = array(
            'bin-dir' => 'bin',
            'conf-dir' => 'conf',
            'etc-dir' => 'etc',
            'src-dir' => 'src',
            'var-dir' => 'var',
            'public-dir' => 'public',
        );

        foreach (array_keys($options) as $key) {
            if (isset($composer['extra'][$key])) {
                $options[$key] = $composer['extra'][$key];
            } elseif (isset($composer['extra']['symfony-'.$key])) {
                $options[$key] = $composer['extra']['symfony-'.$key];
            }

        }

        return $options;
    }
}
