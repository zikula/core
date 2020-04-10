<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
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
 * This class extracts "input_group" array values from form type classes.
 */
final class FormTypeInputGroup extends AbstractFormType implements NodeVisitor
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

        $inputGroupNode = null;
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
            } elseif ('input_group' === $item->key->value) {
                $inputGroupNode = $item;
            }
        }

        if (null === $inputGroupNode) {
            return null;
        }

        if ($inputGroupNode->value instanceof Node\Expr\Array_) {
            foreach ($inputGroupNode->value->items as $inputGroupAddOn) {
                if (!$inputGroupAddOn->key instanceof Node\Scalar\String_) {
                    $this->addError($inputGroupNode, 'Form input group key is not a scalar string');
                    continue;
                }
                if (!\in_array($inputGroupAddOn->key->value, ['left', 'right'])) {
                    $this->addError($inputGroupNode, 'Form input group key is neither left nor right');
                    continue;
                }
                if (!$inputGroupAddOn->value instanceof Node\Scalar\String_) {
                    $this->addError($inputGroupNode, 'Form input group value is not a scalar string');
                    continue;
                }
                $value = $inputGroupAddOn->value->value;
                if ('<i' === mb_substr($value, 0, 2) && '</i>' === mb_substr($value, -4)) {
                    // do not add FA icons
                    continue;
                }
                if ('<span' === mb_substr($value, 0, 5) && '</span>' === mb_substr($value, -7)) {
                    // skip
                    continue;
                }
                $line = $inputGroupAddOn->value->getAttribute('startLine');
                if (null !== $location = $this->getLocation($value, $line, $inputGroupAddOn, ['domain' => $domain])) {
                    $this->lateCollect($location);
                }
            }
        } else {
            $this->addError($inputGroupNode, 'Form input group is not an array');
        }

        return null;
    }
}
