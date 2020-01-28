<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Helper;

use HTMLPurifier;
use HTMLPurifier_Config;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class PurifierHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        SessionInterface $session,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->kernel = $kernel;
        $this->session = $session;
        $this->translator = $translator;
        $this->variableApi = $variableApi;
    }

    /**
     * Retrieves configuration array for HTML Purifier.
     */
    public function getPurifierConfig(array $args = []): HTMLPurifier_Config
    {
        $config = $this->getPurifierDefaultConfig();
        if (!isset($args['forcedefault']) || true !== $args['forcedefault']) {
            $savedConfigSerialised = $this->variableApi->get('ZikulaSecurityCenterModule', 'htmlpurifierConfig');
            if (null !== $savedConfigSerialised && false !== $savedConfigSerialised) {
                $savedConfigArray = [];
                /** @var HTMLPurifier_Config $savedConfig */
                $savedConfig = unserialize($savedConfigSerialised);
                if (!is_object($savedConfig) && is_array($savedConfig)) {
                    // this case may happen for old installations
                    $savedConfigArray = $savedConfig;
                } elseif (is_object($savedConfig) && $savedConfig instanceof HTMLPurifier_Config) {
                    // this is the normal case for newer installations
                    $savedConfigArray = $savedConfig->getAll();
                }
                $savedNamespaces = array_keys($savedConfigArray);
                foreach ($savedNamespaces as $savedNamespace) {
                    foreach ($savedConfigArray[$savedNamespace] as $key => $value) {
                        $config->set($savedNamespace . '.' . $key, $value);
                    }
                }
            }
        }

        $def = $config->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');

        $this->ensureCacheDirectoryExists($config->get('Cache.SerializerPath'));

        return $config;
    }

    /**
     * Retrieves an instance of HTMLPurifier.
     *
     * The instance returned is either a newly created instance, or previously created instance
     * that has been cached in a static variable.
     */
    public function getPurifier(array $args = []): HTMLPurifier
    {
        $force = $args['force'] ?? false;

        // prepare htmlpurifier class
        static $purifier;

        if (!isset($purifier) || $force) {
            $config = $this->getPurifierConfig(['forcedefault' => false]);

            $purifier = new HTMLPurifier($config);
        }

        return $purifier;
    }

    /**
     * Retrieves default configuration array for HTML Purifier.
     */
    private function getPurifierDefaultConfig(): HTMLPurifier_Config
    {
        $config = HTMLPurifier_Config::createDefault();

        $charset = $this->kernel->getCharset();
        if ('utf-8' !== mb_strtolower($charset)) {
            // set a different character encoding with iconv
            $config->set('Core.Encoding', $charset);
            // Note that HTML Purifier's support for non-Unicode encodings is crippled by the
            // fact that any character not supported by that encoding will be silently
            // dropped, EVEN if it is ampersand escaped.  If you want to work around
            // this, you are welcome to read docs/enduser-utf8.html in the full package for a fix,
            // but please be cognizant of the issues the "solution" creates (for this
            // reason, I do not include the solution in this document).
        }

        // allow nofollow and imageviewer to be used as document relationships in the rel attribute
        // see http://htmlpurifier.org/live/configdoc/plain.html#Attr.AllowedRel
        $config->set('Attr.AllowedRel', [
            'nofollow' => true,
            'imageviewer' => true,
            'lightbox' => true
        ]);

        // general enable for embeds and objects
        $config->set('HTML.SafeObject', true);
        $config->set('Output.FlashCompat', true);
        $config->set('HTML.SafeEmbed', true);

        $cacheDirectory = $this->kernel->getCacheDir() . '/purifier';
        $config->set('Cache.SerializerPath', $cacheDirectory);

        return $config;
    }

    private function ensureCacheDirectoryExists(string $cacheDirectory): void
    {
        $fs = new Filesystem();

        try {
            if (!$fs->exists($cacheDirectory)) {
                // this uses always a fixed environment (e.g. "prod") that is serialized
                // in purifier configuration
                // so ensure the main directory exists even if another environment is currently used
                $parentDirectory = mb_substr($cacheDirectory, 0, -9);
                if (!$fs->exists($parentDirectory)) {
                    $fs->mkdir($parentDirectory);
                }
                $fs->mkdir($cacheDirectory);
            }
        } catch (IOExceptionInterface $e) {
            $this->session->getFlashBag()->add(
                'error',
                $this->translator->trans(
                    'An error occurred while creating HTML Purifier cache directory at %path',
                    ['%path' => $e->getPath()]
                )
            );
        }
    }
}
