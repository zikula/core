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

use JMS\TranslationBundle\Exception\RuntimeException;
use Doctrine\Common\Annotations\DocParser;
use JMS\TranslationBundle\Model\FileSource;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Annotation\Meaning;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use JMS\TranslationBundle\Translation\Extractor\FileVisitorInterface;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Logger\LoggerAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;

/**
 * This parser can extract translation information from PHP files.
 *
 * It parses all zikula-style translation calls.
 */
class ZikulaPhpFileExtractor implements LoggerAwareInterface, FileVisitorInterface, \PHPParser_NodeVisitor
{
    private $domain = '';

    /**
     * @var \PHPParser_NodeTraverser
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
     * @var \PHPParser_Node
     */
    private $previousNode;

    /**
     * @var array
     */
    private $bundles;

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

    public function __construct(DocParser $docParser, KernelInterface $kernel)
    {
        $this->docParser = $docParser;
        $bundles = $kernel->getBundles();
        foreach ($bundles as $bundle) {
            $this->bundles[$bundle->getNamespace()] = $bundle->getName();
        }

        $this->traverser = new \PHPParser_NodeTraverser();
        $this->traverser->addVisitor($this);
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function enterNode(\PHPParser_Node $node)
    {
        /**
         * determine domain from namespace of files.
         * Finder appears to start with root level files so Namespace is correct for remaining files
         */
        if ($node instanceof \PHPParser_Node_Stmt_Namespace) {
            if (isset($node->name)) {
                if (array_key_exists($node->name->toString(), $this->bundles)) {
                    $this->domain = strtolower($this->bundles[$node->name->toString()]);
                }

                return;
            } else {
                foreach ($node->stmts as $node) {
                    $this->enterNode($node);
                }

                return;
            }
        }
        if (!$node instanceof \PHPParser_Node_Expr_MethodCall
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

        if (!$node->args[0]->value instanceof \PHPParser_Node_Scalar_String) {
            if ($ignore) {
                return;
            }

            $message = sprintf('Can only extract the translation id from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

            if ($this->logger) {
                $this->logger->err($message);

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
            if (!$node->args[$domainIndex]->value instanceof \PHPParser_Node_Scalar_String) {
                if ($ignore) {
                    return;
                }

                $message = sprintf('Can only extract the translation domain from a scalar string, but got "%s". Please refactor your code to make it extractable, or add the doc comment /** @Ignore */ to this code element (in %s on line %d).', get_class($node->args[0]->value), $this->file, $node->args[0]->value->getLine());

                if ($this->logger) {
                    $this->logger->err($message);

                    return;
                }

                throw new RuntimeException($message);
            }

            $domain = $node->args[$domainIndex]->value->value;
        } else {
            $domain = !empty($this->domain) ? $this->domain : 'messages';
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

    public function leaveNode(\PHPParser_Node $node)
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

    private function getDocCommentForNode(\PHPParser_Node $node)
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
}
