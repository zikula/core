<?php
/*
 *  $Id: GenerateModelsDb.php 2761 2007-10-07 23:42:29Z zYne $
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
 * Doctrine_Task_GenerateModelsDb
 *
 * @package     Doctrine
 * @subpackage  Task
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 2761 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Task_GenerateModelsDb extends Doctrine_Task
{
    public $description          =   'Generates your Doctrine_Record definitions from your existing database connections.',
           $requiredArguments    =   array('models_path'    =>  'Specify path to your Doctrine_Record definitions.'),
           $optionalArguments    =   array('connection'     =>  'Optionally specify a single connection to generate the models for.');
    
    public function execute()
    {
        $configs = $this->dispatcher->getConfig();
        $options = isset($configs['generate_models_options']) ? $configs['generate_models_options'] : array();
        Doctrine_Core::generateModelsFromDb($this->getArgument('models_path'), (array) $this->getArgument('connection'), $options);

        $this->notify('Generated models successfully from databases');
    }
}
