<?php
/*
 *  $Id: GenerateMigrationsModels.php 2761 2007-10-07 23:42:29Z zYne $
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
 * Doctrine_Task_GenerateMigrationsModels
 *
 * @package     Doctrine
 * @subpackage  Task
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 2761 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Task_GenerateMigrationsModels extends Doctrine_Task
{
    public $description          =   'Generate migration classes for an existing set of models',
           $requiredArguments    =   array('migrations_path' => 'Specify the path to your migration classes folder.',
                                           'models_path'     => 'Specify the path to your doctrine models folder.'),
           $optionalArguments    =   array();
    
    public function execute()
    {   
        Doctrine_Core::generateMigrationsFromModels($this->getArgument('migrations_path'), $this->getArgument('models_path'));
        
        $this->notify('Generated migration classes successfully from models');
    }
}