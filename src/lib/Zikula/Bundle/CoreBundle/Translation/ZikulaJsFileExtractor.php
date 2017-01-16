<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Translation;

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;


class ZikulaJsFileExtractor implements FileVisitorInterface
{
    const SINGULAR_CAPTURE_REGEX = '\s?[\'"]([^"\'),]+)[\'"]\s?';
    const PLURAL_CAPTURE_REGEX = '\s?[\'"]([^"\'),]+)[\'"]\s?,\s?[\'"]([^"\'),]+)[\'"]';
    const REGEX_DELIMITER = '/';

    private $singularFunctions = [
        'trans', // should be replaced by vendor eventually
        'transChoice', // should be replaced by vendor eventually
        '__',
        '__f',
    ];
    private $pluralFunctions = [
        '_n',
        '_fn'
    ];
    
    /**
     * @var array
     */
    private $bundles;
    
    /**
     * @var KernelInterface
     */
    private $kernel;
    
    /**
     * @var array cache of domain names by composerPath
     */
    private static $domainCache;
    
    /**
     * ZikulaJsFileExtractor constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $bundles = $kernel->getBundles();
        foreach ($bundles as $bundle) {
            $this->bundles[$bundle->getNamespace()] = $bundle->getName();
        }
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
        if ('.js' !== substr($file, -3)) {
            return;
        }
        
        $composerPath = str_replace($file->getRelativePathname(), '', $file->getPathname());
        if (isset(self::$domainCache[$composerPath])) {
            $domain = self::$domainCache[$composerPath];
        } else {
            $scanner = new Scanner();
            $scanner->scan([$composerPath], 1);
            $metaData = $scanner->getModulesMetaData(true);
            $domains = array_keys($metaData);
            if (isset($domains[0])) {
                if ($this->kernel->isBundle($domains[0])) {
                    $bundle = $this->kernel->getBundle($domains[0]);
                    $domain = ($bundle instanceof AbstractBundle) ? $bundle->getTranslationDomain() : strtolower($bundle->getName());
                } else {
                    $domain = strtolower($domains[0]);
                }
                $domain = $domain . "_javascript";
                // cache result of file lookup
                self::$domainCache[$composerPath] = $domain;
            } else {
                $domain = "zikula_javascript";
            }
        }
        
        // singular type
        $argumentsRegex = self::REGEX_DELIMITER
            .'\.(?:' . implode('|', $this->singularFunctions) . ')\('
            .self::SINGULAR_CAPTURE_REGEX
            .self::REGEX_DELIMITER;
        preg_match_all($argumentsRegex, file_get_contents($file), $matches);
        foreach ($matches[1] as $string) {
            $message = new Message($string, $domain);
            $message->addSource(new FileSource((string) $file));
            $catalogue->add($message);
        }
        // plural type
        $argumentsRegex = self::REGEX_DELIMITER
            .'\.(?:' . implode('|', $this->pluralFunctions) . ')\('
            .self::PLURAL_CAPTURE_REGEX
            .self::REGEX_DELIMITER;
        preg_match_all($argumentsRegex, file_get_contents($file), $matches);
        foreach ($matches[1] as $key => $singluar) {
            $fullString = $singluar . '|' . $matches[2][$key];
            $message = new Message($fullString, $domain);
            $message->addSource(new FileSource((string) $file));
            $catalogue->add($message);
        }
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $node)
    {
    }
}

