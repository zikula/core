<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Admin
 *
 * @author craig
 */
class ExampleDoctrine_Controller_Admin extends Zikula_AbstractController
{

    /**
     * Main admin display
     * @return string
     */
    public function main()
    {
        return $this->view->fetch('admin_main.tpl');
    }

    /**
     * dump the schema of the DB to Doctrine syntax
     */
    public function schemaDump()
    {
        if (isset($GLOBALS['ZConfig']['DBInfo']['databases']['default']['dbname'])) {
            $db = $GLOBALS['ZConfig']['DBInfo']['databases']['default']['dbname'];
        } else {
            var_dump($GLOBALS['ZConfig']);
            return LogUtil::registerError(__('No default database set.'));
        }
        
        // make a dir for the 'myModels' to be stored & clear it
        $dir = CacheUtil::getLocalDir('myModels');
        if (!is_dir($dir)) {
            CacheUtil::createLocalDir('myModels', 0755, true);
        } else {
            CacheUtil::clearLocalDir('myModels');
        }
        
        // set connection
        $user = $GLOBALS['ZConfig']['DBInfo']['databases']['default']['user'];
        $password = $GLOBALS['ZConfig']['DBInfo']['databases']['default']['password'];
        $host = $GLOBALS['ZConfig']['DBInfo']['databases']['default']['host'];
        $conn = Doctrine_Manager::connection("mysql://$user:$password@$host/$db", 'doctrine');

        // dump the schema.
        Doctrine_Core::generateModelsFromDb($dir, array('doctrine'), array('generateTableClasses' => true));

        LogUtil::registerStatus(__f('Schema Dump complete for database: "%1$s" to directory: %2$s', array($db, $dir)));

        $this->redirect(ModUtil::url('ExampleDoctrine', 'admin', 'main'));
    }

}