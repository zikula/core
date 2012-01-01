<?php
/*
 *  $Id: LocalKey.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Relation_LocalKey
 * This class represents a local key relation
 *
 * @package     Doctrine
 * @subpackage  Relation
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 */
class Doctrine_Relation_LocalKey extends Doctrine_Relation
{
    /**
     * fetchRelatedFor
     *
     * fetches a component related to given record
     *
     * @param Doctrine_Record $record
     * @return Doctrine_Record|Doctrine_Collection
     */
    public function fetchRelatedFor(Doctrine_Record $record)
    {
        $localFieldName = $record->getTable()->getFieldName($this->definition['local']);
        $id = $record->get($localFieldName);

        if (is_null($id) || ! $this->definition['table']->getAttribute(Doctrine_Core::ATTR_LOAD_REFERENCES)) {
            $related = $this->getTable()->create();

            // Ticket #1131 Patch.            
            if ( ! is_null($id)) {
                $related->assignIdentifier($id);
                $related->state(Doctrine_Record::STATE_PROXY);
            }
        } else {
            $dql  = 'FROM ' . $this->getTable()->getComponentName()
                 . ' WHERE ' . $this->getCondition() . $this->getOrderBy(null, false);

            $related = $this->getTable()
                            ->getConnection()
                            ->query($dql, array($id))
                            ->getFirst();
            
            if ( ! $related || empty($related)) {
                $related = $this->getTable()->create();
            }
        }

        $record->set($localFieldName, $id, false);

        return $related;
    }

    /**
     * getCondition
     *
     * @param string $alias
     */
    public function getCondition($alias = null)
    {
        if ( ! $alias) {
           $alias = $this->getTable()->getComponentName();
        }
        return $alias . '.' . $this->definition['foreign'] . ' = ?';
    }
}