<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Dropdown multilist
 */
class Form_Plugin_DropDownRelationlist extends Form_Plugin_DropdownList
{
    protected $module;
    protected $objecttype;
    protected $prefix = 'PN'; // TODO B review this prefix.
    protected $where = '';
    protected $orderby = '';
    protected $pos = -1;
    protected $num = -1;
    protected $idField = '';
    protected $displayField = '';
    
    function getFilename()
    {
        return __FILE__;
    }
    
    function create(&$render, &$params)
    {
        if (!isset($params['module']) || empty($params['module'])) {
            $render->trigger_error(__('Error! in %1$s: the %2$s parameter must be specified.', array(
                'pnformdropdownrelationlist', 
                'module')));
        }
        $this->module = $params['module'];
        unset($params['module']);
        if (!pnModAvailable($this->module)) {
            $render->trigger_error(__('Error! in %1$s: an invalid %2$s parameter was received.', array(
                'pnformdropdownrelationlist', 
                'module')));
        }
        
        if (!isset($params['objecttype']) || empty($params['objecttype'])) {
            $render->trigger_error(__('Error! in %1$s: the %2$s parameter must be specified.', array(
                'pnformdropdownrelationlist', 
                'objecttype')));
        }
        $this->objecttype = $params['objecttype'];
        unset($params['objecttype']);
        
        if (!isset($params['idField']) || empty($params['idField'])) {
            $render->trigger_error(__('Error! in %1$s: the %2$s parameter must be specified.', array(
                'pnformdropdownrelationlist', 
                'idField')));
        }
        $this->idField = $params['idField'];
        unset($params['idField']);
        
        if (!isset($params['displayField']) || empty($params['displayField'])) {
            $render->trigger_error(__('Error! in %1$s: the %2$s parameter must be specified.', array(
                'pnformdropdownrelationlist', 
                'displayField')));
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
        
        parent::create($render, $params);
        
        $this->cssClass .= ' relationlist';
    }
    
    function load(&$render, $params)
    {
        pnModDBInfoLoad($this->module);
        
        // load the object class corresponding to $this->objecttype
        if (!($class = Loader::loadArrayClassFromModule($this->module, $this->objecttype, false, $this->prefix))) {
            pn_exit(__f('Unable to load class [%s] for module [%s]', array(
                DataUtil::formatForDisplay($this->objecttype, $this->module))));
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
        
        parent::load($render, $params);
    }
}

