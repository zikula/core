<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Form
 * @subpackage Form_Plugin
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Dropdown multilist
 */
class Form_Plugin_DropDownRelationlist extends Form_Plugin_DropdownList
{
    /**
     * The module of the relation.
     *
     * @var string
     */
    public $module;

    /**
     * Object type.
     *
     * @var string
     */
    public $objecttype;

    /**
     * DBObject class prefix.
     *
     * TODO B [review this prefix].
     *
     * @var string
     */
    public $prefix = 'PN';

    /**
     * Where clause.
     *
     * @var string
     */
    public $where = '';

    /**
     * OrderBy clause.
     *
     * @var string
     */
    public $orderby = '';

    /**
     * Row offset.
     *
     * @var integer
     */
    public $pos = -1;

    /**
     * Numbers of rows to catch.
     *
     * @var integer
     */
    public $num = -1;

    /**
     * Field name of the ID field.
     *
     * @var string
     */
    public $idField = '';

    /**
     * Name of the field to display.
     *
     * @var string
     */
    public $displayField = '';

    /**
     * Get filename of this file.
     *
     * @return string
     */
    function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Form_View $view    Reference to Form_View object.
     * @param array     &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Form_Plugin
     * @return void
     */
    function create($view, &$params)
    {
        if (!isset($params['module']) || empty($params['module'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                     array('formdropdownrelationlist', 'module')));
        }

        $this->module = $params['module'];
        unset($params['module']);
        if (!ModUtil::available($this->module)) {
            $view->trigger_error(__f('Error! in %1$s: an invalid %2$s parameter was received.',
                                     array('formdropdownrelationlist', 'module')));
        }

        if (!isset($params['objecttype']) || empty($params['objecttype'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                     array('formdropdownrelationlist', 'objecttype')));
        }
        $this->objecttype = $params['objecttype'];
        unset($params['objecttype']);

        if (!isset($params['idField']) || empty($params['idField'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                     array('formdropdownrelationlist', 'idField')));
        }
        $this->idField = $params['idField'];
        unset($params['idField']);

        if (!isset($params['displayField']) || empty($params['displayField'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                     array('formdropdownrelationlist', 'displayField')));
        }
        $this->displayField = $params['displayField'];
        unset($params['displayField']);

        if (isset($params['prefix'])) {
            $this->prefix = $params['prefix'];
            unset($params['prefix']);
        }

        if (isset($params['where'])) {
            $this->where = $params['where'];
            unset($params['where']);
        }

        if (isset($params['orderby'])) {
            $this->orderby = $params['orderby'];
            unset($params['orderby']);
        }

        if (isset($params['pos'])) {
            $this->pos = $params['pos'];
            unset($params['pos']);
        }

        if (isset($params['num'])) {
            $this->num = $params['num'];
            unset($params['num']);
        }

        parent::create($view, $params);

        $this->cssClass .= ' relationlist';
    }

    /**
     * Load event handler.
     *
     * @param Form_View $view    Reference to Form_View object.
     * @param array     &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    function load($view, &$params)
    {
        ModUtil::dbInfoLoad($this->module);

        // load the object class corresponding to $this->objecttype
        $class = "{$this->module}_DBObject_".StringUtil::camelize($this->objecttype).'Array';

        if (!class_exists($class) && System::isLegacyMode()) {
            if (!($class = Loader::loadArrayClassFromModule($this->module, $this->objecttype, false, $this->prefix))) {
                z_exit(__f('Unable to load class [%s] for module [%s]',
                           array(DataUtil::formatForDisplay($this->objecttype, $this->module))));
            }
        }

        // instantiate the object-array
        $objectArray = new $class();

        // get() returns the cached object fetched from the DB during object instantiation
        // get() with parameters always performs a new select
        // while the result will be saved in the object, we assign in to a local variable for convenience.
        $objectData = $objectArray->get($this->where, $this->orderby, $this->pos, $this->num);

        foreach ($objectData as $obj) {
            $this->addItem($obj[$this->displayField], $obj[$this->idField]);
        }

        parent::load($view, $params);
    }
}
