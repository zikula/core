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
    const JAVASCRIPT_DOMAIN = 'zikula_javascript'; // figure out way to compute the bundle's translation domain? #3650
    const SINGULAR_CAPTURE_REGEX = '\s?([\'"])((?:(?!\1).)*)\1\s?';
    const PLURAL_CAPTURE_REGEX = '\s?([\'"])((?:(?!\1).)*)\1\s?,\s?([\'"])((?:(?!\3).)*)\3\s?';
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
        $argumentsRegex = $this->generateRegexPattern($this->singularFunctions, self::SINGULAR_CAPTURE_REGEX);
        preg_match_all($argumentsRegex, file_get_contents($file), $singularMatches);
        foreach ($singularMatches[2] as $string) {
            $message = new Message($string, self::JAVASCRIPT_DOMAIN);
            $message->addSource(new FileSource((string) $file));
            $catalogue->add($message);
        }
        // plural type
        $argumentsRegex = $this->generateRegexPattern($this->pluralFunctions, self::PLURAL_CAPTURE_REGEX);
        preg_match_all($argumentsRegex, file_get_contents($file), $pluralMatches);
        foreach ($pluralMatches[2] as $key => $singularString) {
            $fullString = $singularString . '|' . $pluralMatches[4][$key];
            $message = new Message($fullString, self::JAVASCRIPT_DOMAIN);
            $message->addSource(new FileSource((string) $file));
            $catalogue->add($message);
        }
    }

    private function generateRegexPattern($functions, $base)
    {
        return self::REGEX_DELIMITER . '\.(?:' . implode('|', $functions) . ')\(' . $base . self::REGEX_DELIMITER;
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $node)
    {
    }
}
