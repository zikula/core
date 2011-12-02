<?php
/*
 *  $Id: ForeignKey.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Relation_ForeignKey
 * This class represents a foreign key relation
 *
 * @package     Doctrine
 * @subpackage  Relation
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 */
class Doctrine_Relation_ForeignKey extends Doctrine_Relation
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
        $id = array();
        $localTable = $record->getTable();
        foreach ((array) $this->definition['local'] as $local) {
           $value = $record->get($localTable->getFieldName($local));
           if (isset($value)) {
               $id[] = $value;
           }
        }
        if ($this->isOneToOne()) {
            if ( ! $record->exists() || empty($id) || 
                 ! $this->definition['table']->getAttribute(Doctrine_Core::ATTR_LOAD_REFERENCES)) {
                
                $related = $this->getTable()->create();
            } else {
                $dql  = 'FROM ' . $this->getTable()->getComponentName()
                      . ' WHERE ' . $this->getCondition() . $this->getOrderBy(null, false);

                $coll = $this->getTable()->getConnection()->query($dql, $id);
                $related = $coll[0];
            }

            $related->set($related->getTable()->getFieldName($this->definition['foreign']),
                    $record, false);
        } else {

            if ( ! $record->exists() || empty($id) || 
                 ! $this->definition['table']->getAttribute(Doctrine_Core::ATTR_LOAD_REFERENCES)) {
                
                $related = Doctrine_Collection::create($this->getTable());
            } else {
                $query      = $this->getRelationDql(1);
                $related    = $this->getTable()->getConnection()->query($query, $id);
            }
            $related->setReference($record, $this);
        }
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
        $conditions = array();
        foreach ((array) $this->definition['foreign'] as $foreign) {
            $conditions[] = $alias . '.' . $foreign . ' = ?';
        }
        return implode(' AND ', $conditions);
    }
}