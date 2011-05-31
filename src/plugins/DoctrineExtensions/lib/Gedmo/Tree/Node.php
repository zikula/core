<?php

namespace Gedmo\Tree;

/**
 * This interface is not necessary but can be implemented for
 * Entities which in some cases needs to be identified as
 * Tree Node
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tree
 * @subpackage Node
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface Node
{
    // use now annotations instead of predifined methods, this interface is not necessary

    /**
     * @gedmo:TreeLeft
     * to mark the field as "tree left" use property annotation @gedmo:TreeLeft
     * it will use this field to store tree left value
     */

    /**
     * @gedmo:TreeRight
     * to mark the field as "tree right" use property annotation @gedmo:TreeRight
     * it will use this field to store tree right value
     */

    /**
     * @gedmo:TreeParent
     * in every tree there should be link to parent. To identify a relation
     * as parent relation to child use @Tree:Ancestor annotation on the related property
     */

    /**
     * @gedmo:TreeLevel
     * level of node.
     */
}