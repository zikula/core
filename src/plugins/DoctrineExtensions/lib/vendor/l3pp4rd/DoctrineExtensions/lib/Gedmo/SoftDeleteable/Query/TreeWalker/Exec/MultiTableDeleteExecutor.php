<?php

namespace Gedmo\SoftDeleteable\Query\TreeWalker\Exec;

use Doctrine\ORM\Query\Exec\MultiTableDeleteExecutor as BaseMultiTableDeleteExecutor;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * This class is used when a DELETE DQL query is called for entities
 * that are part of an inheritance tree
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Query.TreeWalker.Exec
 * @subpackage MultiTableDeleteExecutor
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class MultiTableDeleteExecutor extends BaseMultiTableDeleteExecutor
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Node $AST, $sqlWalker, ClassMetadataInfo $meta, AbstractPlatform $platform, array $config)
    {
        parent::__construct($AST, $sqlWalker);

        $reflProp = new \ReflectionProperty(get_class($this), '_sqlStatements');
        $reflProp->setAccessible(true);

        $sqlStatements = $reflProp->getValue($this);

        foreach ($sqlStatements as $index => $stmt) {
            $matches = array();
            preg_match('/DELETE FROM (\w+) .+/', $stmt, $matches);

            if (isset($matches[1]) && $meta->getQuotedTableName($platform) === $matches[1]) {
                $sqlStatements[$index] = str_replace('DELETE FROM', 'UPDATE', $stmt);;
                $sqlStatements[$index] = str_replace('WHERE', 'SET '.$config['fieldName'].' = "'.date('Y-m-d H:i:s').'" WHERE', $sqlStatements[$index]);
            } else {
                // We have to avoid the removal of registers of child entities of a SoftDeleteable entity
                unset($sqlStatements[$index]);
            }
        }

        $reflProp->setValue($this, $sqlStatements);
    }
}
