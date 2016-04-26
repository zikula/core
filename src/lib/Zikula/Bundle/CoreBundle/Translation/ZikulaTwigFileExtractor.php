<?php
/**
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
use Zikula\Bundle\CoreBundle\Bundle\Scanner;

class ZikulaTwigFileExtractor implements FileVisitorInterface, \Twig_NodeVisitorInterface
{
    private $file;

    private $catalogue;

    private $traverser;

    private $stack = [];

    /**
     * @var array cache of domain names by composerPath
     */
    private static $domainCache;

    /**
     * Possible Zikula-style translation method names
     *
     * @var array
     */
    private $methodNames = [
        1 => '__',
        2 => '__f',
        3 => '_n',
        4 => '_fn'
    ];

    public function __construct(\Twig_Environment $env)
    {
        $this->traverser = new \Twig_NodeTraverser($env, [$this]);
        self::$domainCache = [];
    }

    public function enterNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        $this->stack[] = $node;

        if ($node instanceof \Twig_Node_Expression_Function) {
            $name = $node->getAttribute('name');
            if (in_array($name, $this->methodNames)) {
                $args = $node->getNode('arguments');
                switch ($name) {
                    case '_n':
                    case '_fn':
                        $id = $args->getNode(0)->getAttribute('value') . '|' . $args->getNode(1)->getAttribute('value');
                        break;
                    default:
                    case '__f':
                    case '__':
                        $id = $args->getNode(0)->getAttribute('value');
                        break;
                }

                // obtain translation domain from composer file
                $composerPath = str_replace($this->file->getRelativePathname(), '', $this->file->getPathname());
                if (isset(self::$domainCache[$composerPath])) {
                    $domain = self::$domainCache[$composerPath];
                } else {
                    $scanner = new Scanner();
                    $scanner->scan([$composerPath], 1);
                    $metaData = $scanner->getModulesMetaData(true);
                    $domains = array_keys($metaData);
                    if (isset($domains[0])) {
                        $domain = strtolower($domains[0]);
                        // cache result of file lookup
                        self::$domainCache[$composerPath] = $domain;
                    } else {
                        $domain = 'messages';
                    }
                }
                $domainNode = array_search($name, $this->methodNames);
                $domain = $args->hasNode($domainNode) ? $args->getNode($domainNode)->getAttribute('value') : $domain;

                $message = new Message($id, $domain);
                $message->addSource(new FileSource((string) $this->file, $node->getLine()));
                $this->catalogue->add($message);
            }
        }

        return $node;
    }

    public function getPriority()
    {
        return 0;
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
        $this->traverseEmbeddedTemplates($ast);
    }

    /**
     * If the current Twig Node has embedded templates, we want to travese these templates
     * in the same manner as we do the main twig template to ensure all translations are
     * caught.
     *
     * @param \Twig_Node $node
     */
    private function traverseEmbeddedTemplates(\Twig_Node $node)
    {
        $templates = $node->getAttribute('embedded_templates');

        foreach ($templates as $template) {
            $this->traverser->traverse($template);
            if ($template->hasAttribute('embedded_templates')) {
                $this->traverseEmbeddedTemplates($template);
            }
        }
    }

    public function leaveNode(\Twig_NodeInterface $node, \Twig_Environment $env)
    {
        array_pop($this->stack);

        return $node;
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }
}
