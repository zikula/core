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

use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Annotation\Meaning;
use JMS\TranslationBundle\Exception\RuntimeException;
use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\AbstractBundle;

/**
 * This parser can extract translation information from PHP files.
 *
 * It parses all zikula-style translation calls.
 */
class ZikulaPhpFileExtractor implements LoggerAwareInterface, FileVisitorInterface, NodeVisitor
{
    /**
     * @var string
     */
    private $domain = '';

    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var MessageCatalogue
     */
    private $catalogue;

    /**
     * @var \SplFileInfo
     */
    private $file;

    /**
     * @var DocParser
     */
    private $docParser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Node
     */
    private $previousNode;

    /**
     * @var array
     */
    private $bundles;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

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

    /**
     * ZikulaPhpFileExtractor constructor.
     * @param DocParser $docParser
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function __construct(DocParser $docParser, ZikulaHttpKernelInterface $kernel)
    {
        $this->docParser = $docParser;
        $this->kernel = $kernel;
        $bundles = $kernel->getBundles();
        foreach ($bundles as $bundle) {
            $this->bundles[$bundle->getNamespace()] = $bundle->getName();
        }

        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function enterNode(Node $node)
    {
        /**
         * determine domain from namespace of files.
         * Finder appears to start with root level files so Namespace is correct for remaining files
         */
        if ($node instanceof Namespace_) {
            if (isset($node->name)) {
                $bundle = $this->getBundleFromNodeNamespace($node->name->toString());
                if (isset($bundle) && $bundle instanceof AbstractBundle) {
                    $this->domain = $bundle->getTranslationDomain();
                } else {
                    $this->domain = 'zikula';
                }

                return;
            } else {
                foreach ($node->stmts as $node) {
                    $this->enterNode($node);
                }

                return;
            }
        }
        if (!$node instanceof MethodCall
            || !is_string($node->name)
            || !in_array(strtolower($node->name), $this->methodNames)
        ) {
            $this->previousNode = $node;

            return;
        }

        $ignore = false;
        $desc = $meaning = null;
        if (null !== $docComment = $this->getDocCommentForNode($node)) {
            foreach ($this->docParser->parse($docComment, 'file ' . $this->file . ' near line ' . $node->getLine()) as $annot) {
                if ($annot instanceof Ignore) {
                    $ignore = true;
                } elseif ($annot instanceof Desc) {
                    $desc = $annot->text;
                } elseif ($annot instanceof Meaning) {
                    $meaning = $annot->text;
                }
            }
        }

        if (!$node->args[0]->value instanceof String_) {
            if ($ignore) {
                return;
            }

            $message = sprintf('Can only extract the translation id from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

            if ($this->logger) {
                $this->logger->error($message);

                return;
            }

            throw new RuntimeException($message);
        }

        $id = $node->args[0]->value->value;
        if (in_array(strtolower($node->name), ['_n', '_fn'], true)) {
            // concatenate pluralized strings from zikula functions
            $id = $node->args[0]->value->value . '|' . $node->args[1]->value->value;
        }

        // determine location of domain
        $domainIndex = array_search(strtolower($node->name), $this->methodNames);

        if (isset($node->args[$domainIndex])) {
            if (!$node->args[$domainIndex]->value instanceof String_) {
                if ($ignore) {
                    return;
                }

                $message = sprintf('Can only extract the translation domain from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

                if ($this->logger) {
                    $this->logger->error($message);

                    return;
                }

                throw new RuntimeException($message);
            }

            $domain = $node->args[$domainIndex]->value->value;
        } else {
            $domain = !empty($this->domain) ? $this->domain : 'zikula';
        }

        $message = new Message($id, $domain);
        $message->setDesc($desc);
        $message->setMeaning($meaning);
        $message->addSource(new FileSource((string)$this->file, $node->getLine()));

        $this->catalogue->add($message);
    }

    public function visitPhpFile(\SplFileInfo $file, MessageCatalogue $catalogue, array $ast)
    {
        $this->file = $file;
        $this->catalogue = $catalogue;
        $this->traverser->traverse($ast);
    }

    public function beforeTraverse(array $nodes)
    {
    }

    public function leaveNode(Node $node)
    {
    }

    public function afterTraverse(array $nodes)
    {
    }

    public function visitFile(\SplFileInfo $file, MessageCatalogue $catalogue)
    {
    }

    public function visitTwigFile(\SplFileInfo $file, MessageCatalogue $catalogue, \Twig_Node $ast)
    {
    }

    private function getDocCommentForNode(Node $node)
    {
        // check if there is a doc comment for the ID argument
        // ->trans(/** @Desc("FOO") */ 'my.id')
        if (null !== $comment = $node->args[0]->getDocComment()) {
            return $comment->getText();
        }

        // this may be placed somewhere up in the hierarchy,
        // -> /** @Desc("FOO") */ trans('my.id')
        // /** @Desc("FOO") */ ->trans('my.id')
        // /** @Desc("FOO") */ $translator->trans('my.id')
        if (null !== $comment = $node->getDocComment()) {
            return $comment->getText();
        } elseif (null !== $this->previousNode && $this->previousNode->getDocComment() !== null) {
            $comment = $this->previousNode->getDocComment();

            return is_object($comment) ? $comment->getText() : $comment;
        }

        return null;
    }

    /**
     * Search namespaces for match and return BundleObject
     * @param $nodeNamespace
     * @return BundleInterface|null
     */
    private function getBundleFromNodeNamespace($nodeNamespace)
    {
        foreach ($this->bundles as $namespace => $bundleName) {
            if (false !== strpos($namespace, $nodeNamespace)) {
                if ($this->kernel->isBundle($bundleName)) {
                    return $this->kernel->getBundle($bundleName);
                }
            }
        }

        return null;
    }
}
