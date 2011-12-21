<?php
/*
 *  $Id: Interface.php 7490 2010-03-29 19:53:27Z jwage $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Doctrine_Tree_Interface
 *
 * @package     Doctrine
 * @subpackage  Tree
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 * @author      Joe Simms <joe.simms@websites4.com>
 */
interface Doctrine_Tree_Interface {

    /**
     * creates root node from given record or from a new record
     *
     * @param Doctrine_Record $record                    instance of Doctrine_Record
     */
    public function createRoot(Doctrine_Record $record = null);

    /**
     * returns root node
     *
     * @return Doctrine_Record
     */
    public function fetchRoot($root_id = 1);

    /**
     * optimised method to returns iterator for traversal of the entire tree from root
     *
     * @param array $options                    options
     * @param integer $fetchmode  One of the Doctrine_Core::HYDRATE_* constants.
     * @return Iterator                 instance of Doctrine_Node_<Implementation>_PreOrderIterator
     */
    public function fetchTree($options = array(), $hydrationMode = null);

    /**
     * optimised method that returns iterator for traversal of the tree from the given record primary key
     *
     * @param mixed $pk                         primary key as used by table::find() to locate node to traverse tree from
     * @param array $options                    options
     * @param integer $fetchmode                One of the Doctrine_Core::HYDRATE_* constants.
     * @return iterator                         instance of Doctrine_Node_<Implementation>_PreOrderIterator
     */
    public function fetchBranch($pk, $options = array(), $hydrationMode = null);
}