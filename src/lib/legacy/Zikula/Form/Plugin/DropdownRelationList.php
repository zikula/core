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
 * Dropdown multilist.
 *
 * @deprecated for Symfony2 Forms
 */
class Zikula_Form_Plugin_DropdownRelationList extends Zikula_Form_Plugin_DropdownList
{
    /**
     * The class name of a doctrine record.
     *
     * Required in doctrine mode only.
     *
     * @var string
     */
    public $recordClass;

    /**
     * The module of the relation.
     *
     * Required in dbobject mode only.
     *
     * @var string
     */
    public $module;

    /**
     * Object type.
     *
     * Required in dbobject mode only.
     *
     * @var string
     */
    public $objecttype;

    /**
     * DBObject class prefix.
     *
     * Required in dbobject mode only.
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
     * Required in dbobject mode only.
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
     * Name of optional second field to display.
     *
     * @var string
     */
    public $displayFieldTwo = '';

    /**
     * Whether to display an empty value to select nothing.
     *
     * @var boolean
     */
    public $showEmptyValue = 0;

    /**
     * Get filename of this file.
     *
     * @return string
     */
    public function getFilename()
    {
        return __FILE__;
    }

    /**
     * Create event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @see    Zikula_Form_AbstractPlugin
     * @return void
     */
    public function create(Zikula_Form_View $view, &$params)
    {
        $hasModule = isset($params['module']) && !empty($params['module']);
        $hasObjecttype = isset($params['objecttype']) && !empty($params['objecttype']);
        $hasIdField = isset($params['idField']) && !empty($params['idField']);
        $hasDisplayField = isset($params['displayField']) && !empty($params['displayField']);
        $hasRecordClass = isset($params['recordClass']) && !empty($params['recordClass']);

        if ($hasRecordClass) {
            $this->recordClass = $params['recordClass'];

            $idColumns = Doctrine::getTable($this->recordClass)->getIdentifierColumnNames();

            if (count($idColumns) > 1) {
                $view->trigger_error(__f('Error! in %1$s: an invalid %2$s parameter was received.',
                                     array('formdropdownrelationlist', 'recordClass')));
            }

            $this->idField = $idColumns[0];
        } else {
            if (!$hasModule) {
                $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                         array('formdropdownrelationlist', 'module')));
            }
            $this->module = $params['module'];
            unset($params['module']);

            if (!ModUtil::available($this->module)) {
                $view->trigger_error(__f('Error! in %1$s: an invalid %2$s parameter was received.',
                                         array('formdropdownrelationlist', 'module')));
            }

            if (!$hasObjecttype) {
                $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                         array('formdropdownrelationlist', 'objecttype')));
            }
            $this->objecttype = $params['objecttype'];
            unset($params['objecttype']);

            if (!$hasIdField) {
                $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                         array('formdropdownrelationlist', 'idField')));
            }
            $this->idField = $params['idField'];
            unset($params['idField']);

            if (isset($params['prefix'])) {
                $this->prefix = $params['prefix'];
                unset($params['prefix']);
            }
        }

        if (!isset($params['displayField']) || empty($params['displayField'])) {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.',
                                     array('formdropdownrelationlist', 'displayField')));
        }
        $this->displayField = $params['displayField'];
        unset($params['displayField']);

        $this->displayFieldTwo = '';
        if (isset($params['displayField2'])) {
            $this->displayFieldTwo = $params['displayField2'];
            unset($params['displayField2']);
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

        if (isset($params['showEmptyValue'])) {
            $this->showEmptyValue = $params['showEmptyValue'];
            unset($params['showEmptyValue']);
        }

        parent::create($view, $params);

        $this->cssClass .= ' z-form-relationlist';
    }

    /**
     * Load event handler.
     *
     * @param Zikula_Form_View $view Reference to Zikula_Form_View object.
     * @param array            &$params Parameters passed from the Smarty plugin function.
     *
     * @return void
     */
    public function load(Zikula_Form_View $view, &$params)
    {
        if ($this->showEmptyValue != 0) {
            $this->addItem('- - -', 0);
        }

        // switch between doctrine and dbobject mode
        if ($this->recordClass) {
            $q = Doctrine::getTable($this->recordClass)->createQuery();

            if ($this->where) {
                if (is_array($this->where)) {
                    $q->where($this->where[0], $this->where[1]);
                } else {
                    $q->where($this->where);
                }
            }

            if ($this->orderby) {
                $q->orderBy($this->orderby);
            }

            if ($this->pos >= 0) {
                $q->offset($this->pos);
            }

            if ($this->num > 0) {
                $q->limit($this->num);
            }

            $rows = $q->execute();

            foreach ($rows as $row) {
                $itemLabel = $row[$this->displayField];
                if (!empty($this->displayFieldTwo)) {
                    $itemLabel .= ' (' . $row[$this->displayFieldTwo] . ')';
                }
                $this->addItem($itemLabel, $row[$this->idField]);
            }
        } else {
            ModUtil::dbInfoLoad($this->module);

            // load the object class corresponding to $this->objecttype
            $class = "{$this->module}_DBObject_".StringUtil::camelize($this->objecttype).'Array';

            // instantiate the object-array
            $objectArray = new $class();

            // get() returns the cached object fetched from the DB during object instantiation
            // get() with parameters always performs a new select
            // while the result will be saved in the object, we assign in to a local variable for convenience.
            $objectData = $objectArray->get($this->where, $this->orderby, $this->pos, $this->num);

            foreach ($objectData as $obj) {
                $itemLabel = $obj[$this->displayField];
                if (!empty($this->displayFieldTwo)) {
                    $itemLabel .= ' (' . $obj[$this->displayFieldTwo] . ')';
                }
                $this->addItem($itemLabel, $obj[$this->idField]);
            }
        }

        parent::load($view, $params);
    }
}
