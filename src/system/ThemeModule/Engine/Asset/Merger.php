<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine\Asset;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Routing\RouterInterface;

class Merger implements MergerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CacheProvider
     */
    private $jsCache;

     /**
     * @var CacheProvider
     */
    private $cssCache;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var boolean
     */
    private $minify;

    /**
     * @var boolean
     */
    private $compress;

    /**
     * Merger constructor.
     * @param RouterInterface $router
     * @param CacheProvider $jsCache
     * @param CacheProvider $cssCache
     * @param string $kernelProjectDir
     * @param string $lifetime
     * @param bool $minify
     * @param bool $compress
     */
    public function __construct(
        RouterInterface $router,
        CacheProvider $jsCache,
        CacheProvider $cssCache,
        $kernelProjectDir,
        $lifetime = "1 day",
        $minify = false,
        $compress = false
    ) {
        $this->router = $router;
        $this->jsCache = $jsCache;
        $this->cssCache = $cssCache;
        $projectDir = realpath($kernelProjectDir . '/');
        $this->rootDir = str_replace($router->getContext()->getBaseUrl(), '', $projectDir);
        $this->lifetime = abs((new \DateTime($lifetime))->getTimestamp() - (new \DateTime())->getTimestamp());
        $this->minify = $minify;
        $this->compress = $compress;
    }

    public function merge(array $assets, $type = 'js')
    {
        $preCachedFiles = [];
        $cachedFiles = [];
        $outputFiles = [];
        // skip remote files from combining
        foreach ($assets as $weight => $asset) {
            $path = realpath($this->rootDir . $asset);
            if (is_file($path)) {
                $cachedFiles[] = $path;
            } else {
                if ($weight < 0) {
                    $preCachedFiles[] = $asset;
                } else {
                    $outputFiles[] = $asset;
                }
            }
        }
        $cacheName = in_array($type, ['js', 'css']) ? "{$type}Cache" : null;
        /** @var CacheProvider $cacheService */
        $cacheService = $this->$cacheName;
        $key = md5(serialize($assets)) . (int)$this->minify . (int)$this->compress . $this->lifetime . '.' . $type;
        $data = $cacheService->fetch($key);
        if ($data === false) {
            $data = [];
            foreach ($cachedFiles as $file) {
                $this->readFile($data, $file, $type);
            }
            $now = new \DateTime();
            array_unshift($data, sprintf("/* --- Combined file written: %s */\n\n", $now->format('c')));
            array_unshift($data, sprintf("/* --- Combined files:\n%s\n*/\n\n", implode("\n", $cachedFiles)));
            $data = implode('', $data);
            if ($type == 'css' && $this->minify) {
                $data = $this->minify($data);
            }
            $cacheService->save($key, $data, $this->lifetime);
        }
        $route = $this->router->generate('zikulathememodule_combinedasset_asset', ['type' => $type, 'key' => $key]);
        array_unshift($outputFiles, $route);
        array_merge($preCachedFiles, $outputFiles);

        return $outputFiles;
    }

    /**
     * Read a file and add its contents to the $contents array.
     * This function includes the content of all "@import" statements (recursive).
     *
     * @param array &$contents Array to save content to
     * @param string $file Path to file
     * @param string $ext Can be 'css' or 'js'
     */
    private function readFile(&$contents, $file, $ext)
    {
        if (!file_exists($file)) {
            return;
        }
        $source = fopen($file, 'r');
        if (!$source) {
            return;
        }

        $contents[] = "/* --- Source file: {$file} */\n\n";
        $inMultilineComment = false;
        $importsAllowd = true;
        $wasCommentHack = false;
        while (!feof($source)) {
            if ($ext == 'css') {
                $line = fgets($source, 4096);
                $lineParse = trim($line);
                $lineParse_length = mb_strlen($lineParse, 'UTF-8');
                $newLine = '';
                // parse line char by char
                for ($i = 0; $i < $lineParse_length; $i++) {
                    $char = $lineParse[$i];
                    $nextchar = $i < ($lineParse_length - 1) ? $lineParse[$i + 1] : '';
                    if (!$inMultilineComment && $char == '/' && $nextchar == '*') {
                        // a multiline comment starts here
                        $inMultilineComment = true;
                        $wasCommentHack = false;
                        $newLine .= $char . $nextchar;
                        $i++;
                    } elseif ($inMultilineComment && $char == '*' && $nextchar == '/') {
                        // a multiline comment stops here
                        $inMultilineComment = false;
                        $newLine .= $char . $nextchar;
                        if (substr($lineParse, $i - 3, 8) == '/*\*//*/') {
                            $wasCommentHack = true;
                            $i += 3; // move to end of hack process hack as it where
                            $newLine .= '/*/'; // fix hack comment because we lost some chars with $i += 3
                        }
                        $i++;
                    } elseif ($importsAllowd && $char == '@' && substr($lineParse, $i, 7) == '@import') {
                        // an @import starts here
                        $lineParseRest = trim(substr($lineParse, $i + 7));
                        if (strtolower(substr($lineParseRest, 0, 3)) == 'url') {
                            // the @import uses url to specify the path
                            $posEnd = strpos($lineParse, ';', $i);
                            $charsEnd = substr($lineParse, $posEnd - 1, 2);
                            if ($charsEnd == ');') {
                                // used url() without media
                                $start = strpos($lineParseRest, '(') + 1;
                                $end = strpos($lineParseRest, ')');
                                $url = substr($lineParseRest, $start, $end - $start);
                                if ($url[0] == '"' | $url[0] == "'") {
                                    $url = substr($url, 1, strlen($url) - 2);
                                }
                                // fix url
                                $url = dirname($file) . '/' . $url;
                                if (!$wasCommentHack) {
                                    // clear buffer
                                    $contents[] = $newLine;
                                    $newLine = '';
                                    // process include
                                    $this->readFile($contents, $url, $ext);
                                } else {
                                    $newLine .= '@import url("' . $url . '");';
                                }
                                // skip @import statement
                                $i += $posEnd - $i;
                            } else {
                                // @import contains media type so we can't include its contents.
                                // We need to fix the url instead.
                                $start = strpos($lineParseRest, '(') + 1;
                                $end = strpos($lineParseRest, ')');
                                $url = substr($lineParseRest, $start, $end - $start);
                                if ($url[0] == '"' | $url[0] == "'") {
                                    $url = substr($url, 1, strlen($url) - 2);
                                }
                                // fix url
                                $url = dirname($file) . '/' . $url;
                                // readd @import with fixed url
                                $newLine .= '@import url("' . $url . '")' . substr($lineParseRest, $end + 1, strpos($lineParseRest, ';') - $end - 1) . ';';
                                // skip @import statement
                                $i += $posEnd - $i;
                            }
                        } elseif (substr($lineParseRest, 0, 1) == '"' || substr($lineParseRest, 0, 1) == '\'') {
                            // the @import uses an normal string to specify the path
                            $posEnd = strpos($lineParseRest, ';');
                            $url = substr($lineParseRest, 1, $posEnd - 2);
                            $posEnd = strpos($lineParse, ';', $i);
                            // fix url
                            $url = dirname($file) . '/' . $url;
                            if (!$wasCommentHack) {
                                // clear buffer
                                $contents[] = $newLine;
                                $newLine = '';
                                // process include
                                self::readFile($contents, $url, $ext);
                            } else {
                                $newLine .= '@import url("' . $url . '");';
                            }
                            // skip @import statement
                            $i += $posEnd - $i;
                        }
                    } elseif (!$inMultilineComment && $char != ' ' && $char != "\n" && $char != "\r\n" && $char != "\r") {
                        // css rule found -> stop processing of @import statements
                        $importsAllowd = false;
                        $newLine .= $char;
                    } else {
                        $newLine .= $char;
                    }
                }
                // fix other paths after @import processing
                if (!$importsAllowd) {
                    $relativePath = str_replace(realpath($this->rootDir), '', $file);
                    $newLine = self::cssFixPath($newLine, explode('/', dirname($relativePath)));
                }
                $contents[] = $newLine;
            } else {
                $contents[] = fgets($source, 4096);
            }
        }
        fclose($source);
        if ($ext == 'js') {
            $contents[] = "\n;\n";
        } else {
            $contents[] = "\n\n";
        }
    }

    /**
     * Fix paths in CSS files.
     * @param string $line CSS file line
     * @param string $relativeFilePath relative path to original file
     * @return string
     */
    private function cssFixPath($line, $relativeFilePath)
    {
        $regexpurl = '/url\([\'"]?([\.\/]*)(.*?)[\'"]?\)/i';
        if (false === strpos($line, 'url')) {
            return $line;
        }

        preg_match_all($regexpurl, $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ((strpos($match[1], '/') !== 0) && (substr($match[2], 0, 7) != 'http://') && (substr($match[2], 0, 8) != 'https://')) {
                $depth = substr_count($match[1], '../') * -1;
                $path = $depth < 0 ? array_slice($relativeFilePath, 0, $depth) : $relativeFilePath;
                $path = implode('/', $path);
                $path = !empty($path) ? $path . '/' : '/';
                $line = str_replace($match[0], "url('{$path}{$match[2]}')", $line);
            }
        }

        return $line;
    }

    /**
     * Remove comments, whitespace and spaces from css files
     * @param $contents
     * @return string
     */
    private function minify($contents)
    {
        // Remove comments.
        $contents = trim(preg_replace('/\/\*.*?\*\//s', '', $contents));
        // Compress whitespace.
        $contents = preg_replace('/\s+/', ' ', $contents);
        // Additional whitespace optimisation -- spaces around certain tokens is not required by CSS
        $contents = preg_replace('/\s*(;|\{|\}|:|,)\s*/', '\1', $contents);

        return $contents;
    }
}
