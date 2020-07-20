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

namespace Zikula\SecurityCenterModule\Helper;

use HTMLPurifier;
use HTMLPurifier_Config;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;

class PurifierHelper
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CacheDirHelper
     */
    private $cacheDirHelper;

    /**
     * @var string
     */
    private $purifierCacheDir;

    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi,
        CacheDirHelper $cacheDirHelper
    ) {
        $this->kernel = $kernel;
        $this->variableApi = $variableApi;
        $this->cacheDirHelper = $cacheDirHelper;
        $this->purifierCacheDir = $this->kernel->getCacheDir() . '/purifier';
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

        $this->cacheDirHelper->ensureCacheDirectoryExists($this->purifierCacheDir, true);

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
        $config->set('Cache.SerializerPath', $this->purifierCacheDir);

        return $config;
    }
}
