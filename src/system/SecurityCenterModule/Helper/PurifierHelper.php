<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Helper;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

class PurifierHelper
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * PurifierHelper constructor.
     * @param KernelInterface $kernel
     * @param VariableApi $variableApi
     */
    public function __construct(KernelInterface $kernel, VariableApi $variableApi)
    {
        $this->kernel = $kernel;
        $this->variableApi = $variableApi;
    }

    /**
     * Retrieves configuration array for HTML Purifier.
     *
     * @param bool[] $args {
     *      @type bool $forcedefault true to force return of default config / false to auto detect
     *                    }
     *
     * @return \HTMLPurifier_Config HTML Purifier configuration settings
     */
    public function getPurifierConfig($args)
    {
        $config = $this->getPurifierDefaultConfig();
        if (!isset($args['forcedefault']) || true !== $args['forcedefault']) {
            $savedConfigSerialised = $this->variableApi->get('ZikulaSecurityCenterModule', 'htmlpurifierConfig');
            if (!is_null($savedConfigSerialised) && false !== $savedConfigSerialised) {
                /** @var \HTMLPurifier_Config $savedConfig */
                $savedConfig = unserialize($savedConfigSerialised);
                $savedConfigArray = $savedConfig->getAll();
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

        return $config;
    }

    /**
     * Retrieves an instance of HTMLPurifier.
     *
     * The instance returned is either a newly created instance, or previously created instance
     * that has been cached in a static variable.
     *
     * @param bool[] $args {
     *      @type bool $force If true, the HTMLPurifier instance will be generated anew, rather than using an
     *                        existing instance from the static variable.
     *                     }
     *
     * @staticvar array $purifier The HTMLPurifier instance.
     *
     * @return \HTMLPurifier The HTMLPurifier instance, returned by reference
     */
    public function getPurifier($args = null)
    {
        $force = isset($args['force']) ? $args['force'] : false;

        // prepare htmlpurifier class
        static $purifier;

        if (!isset($purifier) || $force) {
            $config = $this->getPurifierConfig(['forcedefault' => false]);

            $purifier = new \HTMLPurifier($config);
        }

        return $purifier;
    }

    /**
     * Retrieves default configuration array for HTML Purifier.
     *
     * @return \HTMLPurifier_Config HTML Purifier default configuration settings
     */
    private function getPurifierDefaultConfig()
    {
        $config = \HTMLPurifier_Config::createDefault();

        $charset = $this->kernel->getCharset();
        if (strtolower($charset) != 'utf-8') {
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

        $config->set('Cache.SerializerPath', $this->kernel->getCacheDir() . '/purifier');

        return $config;
    }
}
