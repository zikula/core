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

class ZikulaJsFileExtractor implements FileVisitorInterface
{
    const JAVASCRIPT_DOMAIN = 'zikula_javascript'; // @todo figure out way to compute the bundle's translation domain?
    const SINGULAR_CAPTURE_REGEX = '\s?[\'"]([^"\']+)[\'"]\s?';
    const PLURAL_CAPTURE_REGEX = '\s?[\'"]([^"\']+)[\'"]\s?,\s?[\'"]([^"\']+)[\'"]';
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

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
        if ('.js' !== substr($file, -3)) {
            return;
        }

        // singular type
        $argumentsRegex = self::REGEX_DELIMITER
            .'\.(?:' . implode('|', $this->singularFunctions) . ')\('
            .self::SINGULAR_CAPTURE_REGEX
            .self::REGEX_DELIMITER;
        preg_match_all($argumentsRegex, file_get_contents($file), $matches);
        foreach ($matches[1] as $string) {
            $message = new Message($string, self::JAVASCRIPT_DOMAIN);
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
            $message = new Message($fullString, self::JAVASCRIPT_DOMAIN);
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
