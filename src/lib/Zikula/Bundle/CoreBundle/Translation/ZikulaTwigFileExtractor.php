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

namespace Zikula\Bundle\CoreBundle\Translation {

use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use SplFileInfo;
use Twig\Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeTraverser;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Zikula\Bundle\CoreBundle\Bundle\Scanner;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\AbstractBundle;

class ZikulaTwigFileExtractor extends AbstractNodeVisitor implements FileVisitorInterface
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var MessageCatalogue
     */
    private $catalogue;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var array
     */
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

    public function __construct(Environment $env, ZikulaHttpKernelInterface $kernel)
    {
        $this->traverser = new NodeTraverser($env, [$this]);
        $this->kernel = $kernel;
        self::$domainCache = [];
    }

    protected function doEnterNode(Node $node, Environment $env)
    {
        $this->stack[] = $node;

        if ($node instanceof FunctionExpression) {
            $name = $node->getAttribute('name');
            if (in_array($name, $this->methodNames, true)) {
                $args = $node->getNode('arguments');
                switch ($name) {
                    case '_n':
                    case '_fn':
                        $id = $args->getNode(0)->getAttribute('value') . '|' . $args->getNode(1)->getAttribute('value');
                        break;
                    case '__f':
                    case '__':
                    default:
                        $id = $args->getNode(0)->getAttribute('value');
                        break;
                }

                // obtain translation domain from bundle if possible
                $composerPath = str_replace($this->file->getRelativePathname(), '', $this->file->getPathname());
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
                            $domain = ($bundle instanceof AbstractBundle) ? $bundle->getTranslationDomain() : mb_strtolower($bundle->getName());
                        } else {
                            $domain = mb_strtolower($domains[0]);
                        }
                        // cache result of file lookup
                        self::$domainCache[$composerPath] = $domain;
                    } else {
                        $domain = 'zikula';
                    }
                }
                $domainNode = array_search($name, $this->methodNames, true);
                $domain = $args->hasNode($domainNode) ? $args->getNode($domainNode)->getAttribute('value') : $domain;

                $message = new Message($id, $domain);
                $message->addSource(new FileSource((string)$this->file, $node->getTemplateLine()));
                $this->catalogue->add($message);
            }
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function visitTwigFile(SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
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

    protected function doLeaveNode(Node $node, Environment $env)
    {
        array_pop($this->stack);

        return $node;
    }

    public function visitFile(SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitPhpFile(SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
    }
}
}

// TODO remove temporary workaround when vendor interface (FileVisitorInterface) is updated
namespace {
    use Twig\Node\Node;
    if (!class_exists('Twig_Node')) {
        class Twig_Node extends Node
        {
        }
    }
}
