<?php
/*
 *  $Id: Integer.php 7490 2010-03-29 19:53:27Z jwage $
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
 * Doctrine_Hook_Integer
 *
 * @package     Doctrine
 * @subpackage  Hook
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 7490 $
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Hook_Integer extends Doctrine_Hook_Parser_Complex
{
    /**
     * parse
     * Parses given field and field value to DQL condition
     * and parameters. This method should always return
     * prepared statement conditions (conditions that use
     * placeholders instead of literal values).
     *
     * @param string $alias     component alias
     * @param string $field     the field name
     * @param mixed $value      the value of the field
     * @return void
     */
    public function parseSingle($alias, $field, $value)
    {
        $e = explode(' ', $value);

        foreach ($e as $v) {
             $v = trim($v);

             $e2   = explode('-', $v);

            $name = $alias. '.' . $field;

             if (count($e2) == 1) {
                 // one '-' found

                $a[] = $name . ' = ?';

                $this->params[] = $v;
            } else {
                // more than one '-' found

                $a[] = '(' . $name . ' > ? AND ' . $name . ' < ?)';

                $this->params += array($e2[0], $e2[1]);
            }

        }
        return implode(' OR ', $a);
    }
}