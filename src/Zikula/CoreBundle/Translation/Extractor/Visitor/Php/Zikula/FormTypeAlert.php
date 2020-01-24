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

namespace Zikula\Bundle\CoreBundle\Translation\Extractor\Visitor\Php\Zikula;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use Translation\Extractor\Visitor\Php\Symfony\AbstractFormType;
use Translation\Extractor\Visitor\Php\Symfony\FormTrait;

/**
 * This class extracts "alert" array keys from form type classes.
 */
final class FormTypeAlert extends AbstractFormType implements NodeVisitor
{
    use FormTrait;

    public function enterNode(Node $node): ?Node
    {
        if (!$this->isFormType($node)) {
            return null;
        }

        parent::enterNode($node);

        if (!$node instanceof Node\Expr\Array_) {
            return null;
        }

        $alertNode = null;
        $domain = null;
        foreach ($node->items as $item) {
            if (!$item->key instanceof Node\Scalar\String_) {
                continue;
            }
            if ('translation_domain' === $item->key->value) {
                // Try to find translation domain
                if ($item->value instanceof Node\Scalar\String_) {
                    $domain = $item->value->value;
                }
            } elseif ('alert' === $item->key->value) {
                $alertNode = $item;
            }
        }

        if (null === $alertNode) {
            return null;
        }

        if ($alertNode->value instanceof Node\Expr\Array_) {
            foreach ($alertNode->value->items as $alertItem) {
                if (!$alertItem->key instanceof Node\Scalar\String_) {
                    $this->addError($alertNode, 'Form alert key is not a scalar string');
                    continue;
                }
                $line = $alertItem->value->getAttribute('startLine');
                if (null !== $location = $this->getLocation($alertItem->key->value, $line, $alertItem, ['domain' => $domain])) {
                    $this->lateCollect($location);
                }
            }
        } else {
            $this->addError($alertNode, 'Form alert is not an array');
        }

        return null;
    }
}
