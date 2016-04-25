<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This behavior add categories to the record.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Categorisable extends Doctrine_Template
{
    /**
     * Adds an one-to-many relationship named Categories to Zikula_Doctrine_Model_EntityCategory.
     *
     * @return void
     * @throws Exception Throws when the record uses Categorisable template AND a composite primary key.
     */
    public function setUp()
    {
        $record = $this->getInvoker();

        $recordClass  = get_class($record);
        $subclassName = 'GeneratedDoctrineModel_'.  $recordClass.'_EntityCategory';
        $module       = substr($recordClass, 0, strpos($recordClass, '_'));

        if (!class_exists($subclassName)) {
            self::_generateSubclassForCategorisableTemplate($module, $recordClass);
        }

        $idColumn = $record->getTable()->getIdentifier();
        if (is_array($idColumn)) {
            if (count($idColumn) > 1) {
                throw new LogicException(sprintf('Error: Doctrine record %s uses Categorisable template AND a composite primary key', $recordClass));
            }
            $idColumn = $idColumn[0];
        }

        $this->hasMany($subclassName.' as Categories', array(
            'local' => $idColumn,
            'foreign' => 'obj_id',
            'cascade' => array('delete')
        ));

        $this->addListener(new Zikula_Doctrine_Template_Listener_Categorisable());
    }

    /**
     * Generates an subclass of the Zikula_Doctrine_Model_EntityCategory class and caches the generated class in a file.
     *
     * @param string $module     Name of the Module to that the model belongs to.
     * @param string $modelClass Classname of the model.
     *
     * @return void
     * @throws Exception Throws when the create of the cache directory fails.
     */
    private static function _generateSubclassForCategorisableTemplate($module, $modelClass)
    {
        $table = Doctrine::getTable($modelClass);
        sscanf($table->getTableName(), Doctrine_Manager::getInstance()->getAttribute(Doctrine::ATTR_TBLNAME_FORMAT), $tableName);

        $dir = 'doctrinemodels/GeneratedDoctrineModel/' . str_replace('_', DIRECTORY_SEPARATOR, $modelClass);
        if (CacheUtil::createLocalDir($dir, ServiceUtil::getManager()->getParameter('system.chmod_dir'))) {
            $subclassName = 'GeneratedDoctrineModel_'.$modelClass.'_EntityCategory';
            $fileContents = '<?php class '.$subclassName.' extends Zikula_Doctrine_Model_EntityCategory { }';
            $fileName = 'EntityCategory.php';

            // save new model
            file_put_contents(CacheUtil::getLocalDir().'/'.$dir.'/'.$fileName, $fileContents);

            // save required data for later use
            $modelsInfo = ModUtil::getVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', array());
            $modelsInfo[$subclassName] = array('module' => $module, 'table' => $tableName);
            ModUtil::setVar('ZikulaCategoriesModule', 'EntityCategorySubclasses', $modelsInfo);
        } else {
            throw new Exception('Creation of the cache directory '.$dir.' failed');
        }
    }

    /**
     * Allows to sets multiple categories with one call.
     *
     * @param array $categories Array of property => Category id or Category Object.
     *
     * @return void
     */
    public function setCategories(array $categories)
    {
        foreach ($categories as $prop => $category) {
            $this->setCategory($prop, $category);
        }
    }

    /**
     * Set/adds an category to requested property.
     *
     * @param string $prop     Property name as definied in the registry.
     * @param string $category Category id or Object.
     *
     * @return void
     * @throws Exception If this table has not the property $prop.
     */
    public function setCategory($prop, $category)
    {
        $rec = $this->getInvoker();
        sscanf($rec->getTable()->getTableName(), Doctrine_Manager::getInstance()->getAttribute(Doctrine::ATTR_TBLNAME_FORMAT), $tableName);

        // get the registry object
        $registry = Doctrine::getTable('Zikula_Doctrine_Model_Registry')->findOneByModuleAndTableAndProperty(substr(get_class($rec), 0, strpos(get_class($rec), '_')),
                                                                                                        $tableName,
                                                                                                        $prop);
        // throw an excption when $prop is not valid
        if (!$registry) {
            throw new Exception('Property '.$prop.' not found');
        }

        // search for existring object
        $mapobjFound = null;
        foreach ($rec['Categories'] as $mapobj) {
            if ($mapobj['reg_property'] == $prop) {
                $mapobjFound = $mapobj;
                break;
            }
        }

        // update existring object
        if ($mapobjFound != null) {
            if (is_object($category) && $category instanceof Zikula_Doctrine_Model_Category) {
                $mapobjFound['Category'] = $category;
            } else {
                $mapobjFound['category_id'] = (int)$category;
            }

            // create new object
        } else {
            $rec['Categories'][]['Registry'] = $registry;
            $newmapobj = $rec['Categories']->getLast();
            $newmapobj['reg_property'] = $prop;
            if (is_object($category) && $category instanceof Zikula_Doctrine_Model_Category) {
                $newmapobj['Category'] = $category;
            } else {
                $newmapobj['category_id'] = (int)$category;
            }
        }
    }
}
