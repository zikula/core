<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

/**
 * Delete operation.
 * @param object $entity The treated object.
 * @param array  $params Additional arguments.
 *
 * @return bool False on failure or true if everything worked well.
 *
 * @throws RuntimeException Thrown if executing the workflow action fails
 */
function ZikulaRoutesModule_operation_delete(&$entity, $params)
{
    $dom = ZLanguage::getModuleDomain('ZikulaRoutesModule');

    // initialise the result flag
    $result = false;

    // get entity manager
    $serviceManager = ServiceUtil::getManager();
    $entityManager = $serviceManager->get('doctrine.entitymanager');

    // delete entity
    try {
        $entityManager->remove($entity);
        $entityManager->flush();
        $result = true;

        $logger = $serviceManager->get('logger');
        $logger->notice('{app}: User {user} deleted an entity.', array('app' => 'ZikulaRoutesModule', 'user' => UserUtil::getVar('uname')));
    } catch (\Exception $e) {
        throw new \RuntimeException($e->getMessage());

        $logger = $serviceManager->get('logger');
        $logger->error('{app}: User {user} tried to delete an entity, but failed.', array('app' => 'ZikulaRoutesModule', 'user' => UserUtil::getVar('uname')));
    }

    // return result of this operation
    return $result;
}
