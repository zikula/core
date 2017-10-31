<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HTMLUtil is a class used to generate specific HTML code.
 *
 * @deprecated remove at Core-2.0
 */
class HtmlUtil
{
    const MARKER_NONE = '&nbsp;&nbsp;';
    const REQUIRED_MARKER = '<span class="z-form-mandatory-flag">*</span>';
    const VALIDATION_MARKER = '<span class="z-form-mandatory-flag">!</span>';

    /**
     * Return the HTML code for the specified date selector input box.
     *
     * @param string $objectname    The name of the object the field will be placed in
     * @param string $htmlname      The html fieldname under which the date value will be submitted
     * @param string $dateFormat    The dateformat to use for displaying the chosen date
     * @param string $defaultString The String to display before a value has been selected
     * @param string $defaultDate   The Date the calendar should to default to
     *
     * @return The resulting HTML string
     */
    public static function buildCalendarInputBox($objectname, $htmlname, $dateFormat, $defaultString = '', $defaultDate = '')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $html = '';

        if (!$htmlname) {
            throw new \Exception(__f('%1$s: Missing %2$s.', ['HtmlUtil::buildCalendarInputBox', 'htmlname']));
        }

        if (!$dateFormat) {
            throw new \Exception(__f('%1$s: Missing %2$s.', ['HtmlUtil::buildCalendarInputBox', 'dateFormat']));
        }

        $fieldKey = $htmlname;
        if ($objectname) {
            $fieldKey = $objectname . '[' . $htmlname . ']';
        }

        $triggerName = 'trigger_' . $htmlname;
        $displayName = 'display_' . $htmlname;
        //$daFormat    = preg_replace ('/([a-z|A-Z])/', '%$1', $dateFormat); // replace 'x' -> '%x'

        $html .= '<span id="' . DataUtil::formatForDisplay($displayName) . '">' . DataUtil::formatForDisplay($defaultString) . '</span>';
        $html .= '&nbsp;';
        $html .= '<input type="hidden" name="' . DataUtil::formatForDisplay($fieldKey) . '" id="' . DataUtil::formatForDisplay($htmlname) . '" value="' . DataUtil::formatForDisplay($defaultDate) . '" />';
        $html .= '<img src="javascript/jscalendar/img.gif" id="' . DataUtil::formatForDisplay($triggerName) . '" style="cursor: pointer; border: 0px solid blue;" title="Date selector" alt="Date selector" onmouseover="this.style.background=\'blue\';" onmouseout="this.style.background=\'\'" />';

        $html .= '<script type="text/javascript"> Calendar.setup({';
        $html .= 'ifFormat    : "%Y-%m-%d %H:%M:00",'; // universal format, don't change this!
        $html .= 'inputField  : "' . DataUtil::formatForDisplay($htmlname) . '",';
        $html .= 'displayArea : "' . DataUtil::formatForDisplay($displayName) . '",';
        $html .= 'daFormat    : "' . DataUtil::formatForDisplay($dateFormat) . '",';
        $html .= 'button      : "' . DataUtil::formatForDisplay($triggerName) . '",';
        $html .= 'align       : "Tl",';

        if ($defaultDate) {
            $d = strtotime($defaultDate);
            $d = date('Y/m/d', $d);
            $html .= 'date : "' . $d . '",';
        }

        $html .= 'singleClick : true }); </script>';

        return $html;
    }

    /**
     * Return the HTML for a generic selector.
     *
     * @param string  $name          The name of the generated selector (default='countries') (optional)
     * @param array   $data          The data to build the selector from (default='[]') (optional)
     * @param string  $selectedValue The value which is currently selected (default='') (optional)
     * @param string  $defaultValue  The default value to select (default='') (optional)
     * @param string  $defaultText   The text for the default value (default='') (optional)
     * @param string  $allValue      The value to assign for the "All" choice (optional) (default=0)
     * @param string  $allText       The text to display for the "All" choice (optional) (default='')
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     * @param string  $id            The ID of the generated selector (optional)
     * @param string  $class         The class of the generated selector (optional)
     * @param boolean $required      Whether or not to disable selector (optional) (default=false)
     * @param string  $title         The title of the generated selector (optional)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_Generic($name = 'genericSelector', $data = [], $selectedValue = null, $defaultValue = null, $defaultText = null, $allValue = null, $allText = null, $submit = false, $disabled = false, $multipleSize = 1, $id = null, $class = null, $required = false, $title = null)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            return LogUtil::registerError(__f('Invalid %1$s [%2$s] passed to %3$s.', ['name', $name, 'HtmlUtil::getSelector_Generic']));
        }

        $id = (is_null($id)) ? strtr($name, '[]', '__') : $id;
        $class = (is_null($class)) ? $id : $class;
        $disabled = $disabled ? 'disabled="disabled"' : '';
        $multiple = $multipleSize > 1 ? 'multiple="multiple"' : '';
        $multipleSize = $multipleSize > 1 ? "size=\"$multipleSize\"" : '';
        $submit = $submit ? 'onchange="this.form.submit();"' : '';
        $required = ($required) ? 'required="required" oninvalid="this.setCustomValidity(\''.__('Please select an item in the list.').'\');" onchange="this.setCustomValidity(\'\');" onblur="this.checkValidity();"' : '';
        $title = (is_null($title)) ? '' : 'title="'.$title.'" x-moz-errormessage="'.$title.'"';

        $html = "<select name=\"$name\" id=\"$id\" class=\"$class\" $multipleSize $multiple $submit $disabled $required $title>";

        if ($defaultText && !$selectedValue) {
            $sel = ((string)$defaultValue == (string)$selectedValue ? 'selected="selected"' : '');
            $html .= "<option value=\"" . DataUtil::formatForDisplay($defaultValue) . "\" $sel>" . DataUtil::formatForDisplay($defaultText) . "</option>";
        }

        if ($allText) {
            $sel = ((string)$allValue == (string)$selectedValue ? 'selected="selected"' : '');
            $html .= "<option value=\"" . DataUtil::formatForDisplay($allValue) . "\" $sel>" . DataUtil::formatForDisplay($allText) . "</option>";
        }

        foreach ($data as $k => $v) {
            $sel = ((string)$selectedValue == (string)$k ? 'selected="selected"' : '');
            $html .= "<option value=\"" . DataUtil::formatForDisplay($k) . "\" $sel>" . DataUtil::formatForDisplay($v) . "</option>";
        }

        $html .= '</select>';

        return $html;
    }

    /**
     * Creates an object array selector.
     *
     * @param string  $modname        Module name
     * @param string  $objectType     Object type
     * @param string  $name           Select field name
     * @param string  $field          Value field
     * @param string  $displayField   Display field
     * @param string  $where          Where clause
     * @param string  $sort           Sort clause
     * @param string  $selectedValue  Selected value
     * @param string  $defaultValue   Value for "default" option
     * @param string  $defaultText    Text for "default" option
     * @param string  $allValue       Value for "all" option
     * @param string  $allText        Text for "all" option
     * @param string  $displayField2  Second display field
     * @param boolean $submit         Submit on choose
     * @param boolean $disabled       Add Disabled attribute to select
     * @param string  $fieldSeparator Field seperator if $displayField2 is given
     * @param integer $multipleSize   Size for multiple selects
     *
     * @return string The rendered output
     */
    public static function getSelector_ObjectArray($modname, $objectType, $name, $field = '', $displayField = 'name', $where = '', $sort = '', $selectedValue = '', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $displayField2 = null, $submit = true, $disabled = false, $fieldSeparator = ', ', $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$modname) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['modname', 'HtmlUtil::getSelector_ObjectArray']));
        }

        if (!$objectType) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['objectType', 'HtmlUtil::getSelector_ObjectArray']));
        }

        if (!ModUtil::dbInfoLoad($modname)) {
            return __f('Unavailable/Invalid %1$s [%2$s] passed to %3$s.', ['modulename', $modname, 'HtmlUtil::getSelector_ObjectArray']);
        }

        if (!SecurityUtil::checkPermission("$objectType::", '::', ACCESS_OVERVIEW)) {
            return __f('Security check failed for %1$s [%2$s] passed to %3$s.', ['modulename', $modname, 'HtmlUtil::getSelector_ObjectArray']);
        }

        $cacheKey = md5("$modname|$objectType|$where|$sort");
        if (isset($cache[$cacheKey])) {
            $dataArray = $cache[$cacheKey];
        } else {
            $classname = "{$modname}_DBObject_" . StringUtil::camelize($objectType) . 'Array';

            $class = new $classname();
            //$dataArray = $class->get($where, $sort, -1, -1, '', false, $distinct);
            $dataArray = $class->get($where, $sort, -1, -1, '', false);
            $cache[$cacheKey] = $dataArray;
            if (!$field) {
                $field = $class->_objField;
            }
        }

        $data2 = [];
        foreach ($dataArray as $object) {
            $val = $object[$field];
            $disp = $object[$displayField];
            if ($displayField2) {
                $disp .= $fieldSeparator . $object[$displayField2];
            }
            $data2[$val] = $disp;
        }

        return self::getSelector_Generic($name, $data2, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Creates an Entity array selector.
     *
     * @param string  $modname        Module name
     * @param string  $entity         Doctrine 2 entity classname
     * @param string  $name           Select field name
     * @param string  $field          Value field
     * @param string  $displayField   Display field
     * @param string  $where          Where clause
     * @param string  $sort           Sort clause
     * @param string  $selectedValue  Selected value
     * @param string  $defaultValue   Value for "default" option
     * @param string  $defaultText    Text for "default" option
     * @param string  $allValue       Value for "all" option
     * @param string  $allText        Text for "all" option
     * @param string  $displayField2  Second display field
     * @param boolean $submit         Submit on choose
     * @param boolean $disabled       Add Disabled attribute to select
     * @param string  $fieldSeparator Field seperator if $displayField2 is given
     * @param integer $multipleSize   Size for multiple selects
     *
     * @return string The rendered output
     */
    public static function getSelector_EntityArray($modname, $entity, $name, $field = '', $displayField = 'name', $where = '', $sort = '', $selectedValue = '', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $displayField2 = null, $submit = true, $disabled = false, $fieldSeparator = ', ', $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$modname) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['modname', 'HtmlUtil::getSelector_EntityArray']));
        }

        if ((!$entity) || (!class_exists($entity))) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['entity', 'HtmlUtil::getSelector_EntityArray']));
        }

        if (!SecurityUtil::checkPermission("$entity::", '::', ACCESS_OVERVIEW)) {
            return __f('Security check failed for %1$s [%2$s] passed to %3$s.', ['modulename', $modname, 'HtmlUtil::getSelector_EntityArray']);
        }

        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = ServiceUtil::get('doctrine.orm.default_entity_manager');
        $qb = $entityManager->createQueryBuilder();
        $qb->select('e')->from($entity, 'e');
        $dataArray = $qb->getQuery()->getResult(); // array of Entities
        // @todo does not accommodate $sort or $where

        $data2 = [];
        foreach ($dataArray as $object) {
            $val = $object[$field]; // relies on entityAccess
            $disp = $object[$displayField];
            if ($displayField2) {
                $disp .= $fieldSeparator . $object[$displayField2];
            }
            $data2[$val] = $disp;
        }

        return self::getSelector_Generic($name, $data2, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Get selector by table field.
     *
     * @param string  $modname       Module name
     * @param string  $tablekey      Table name
     * @param string  $name          Select field name
     * @param string  $field         Field name
     * @param string  $where         Where clause
     * @param string  $sort          Sort clause
     * @param string  $selectedValue Selected value
     * @param string  $defaultValue  Value for "default" option
     * @param string  $defaultText   Text for "default" option
     * @param string  $allValue      Value for "all" option
     * @param string  $allText       Text for "all" option
     * @param string  $assocKey      Key for associative array
     * @param boolean $distinct      Use distinct for selection
     * @param boolean $submit        Submit on choose
     * @param boolean $disabled      Add Disabled attribute to select
     * @param integer $truncate      Truncate field to given length
     * @param integer $multipleSize  Size for multiple selects
     *
     * @return string The rendered output
     */
    public static function getSelector_FieldArray($modname, $tablekey, $name, $field = 'id', $where = '', $sort = '', $selectedValue = '', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $assocKey = '', $distinct = false, $submit = true, $disabled = false, $truncate = 0, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$tablekey) {
            throw new \Exception(__f('Invalid %1$s [%2$s] passed to %3$s.', ['tablekey', $modname, 'HtmlUtil::getSelector_FieldArray']));
        }

        if (!$name) {
            throw new \Exception(__f('Invalid %1$s [%2$s] passed to %3$s.', ['name', $name, 'HtmlUtil::getSelector_FieldArray']));
        }

        if ($modname) {
            ModUtil::dbInfoLoad($modname, '', true);
        }

        $fa = DBUtil::selectFieldArray($tablekey, $field, $where, $sort, $distinct, $assocKey);
        $data = [];
        foreach ($fa as $k => $v) {
            if ($v) {
                if ($truncate > 0 && strlen($v) > $truncate) {
                    $v = StringUtil::getTruncatedString($v, $truncate);
                }
                $data[$k] = $v;
            }
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the HTML selector code for the given category hierarchy, maps to CategoryUtil::getSelector_Categories().
     *
     * @param array   $cats             The category hierarchy to generate a HTML selector for
     * @param string  $name             The name of the selector field to generate (optional) (default='category[parent_id]')
     * @param string  $field            The field value to return (optional) (default='id')
     * @param integer $selectedValue    The selected category (optional) (default=0)
     * @param integer $defaultValue     The default value to present to the user (optional) (default=0)
     * @param string  $defaultText      The default text to present to the user (optional) (default='')
     * @param integer $allValue         The value to assign for the "All" choice (optional) (default=0)
     * @param string  $allText          The text to display for the "All" choice (optional) (default='')
     * @param boolean $submit           Whether or not to submit the form upon change (optional) (default=false)
     * @param boolean $displayPath      If false, the path is simulated, if true, the full path is shown (optional) (default=false)
     * @param boolean $doReplaceRootCat Whether or not to replace the root category with a localized string (optional) (default=true)
     * @param integer $multipleSize     If > 1, a multiple selector box is built, otherwise a normal/single selector box is build (optional) (default=1)
     *
     * @return The HTML selector code for the given category hierarchy
     */
    public static function getSelector_Categories($cats, $name, $field = 'id', $selectedValue = '0', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $displayPath = false, $doReplaceRootCat = true, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        return CategoryUtil::getSelector_Categories($cats, $field, $selectedValue, $name, $defaultValue, $defaultText, $allValue, $allText, $submit, $displayPath, $doReplaceRootCat, $multipleSize);
    }

    /**
     * Return the HTML code for the values in a given category.
     *
     * @param string  $categoryPath The identifying category path
     * @param array   $values       The values used to populate the defautl states (optional) (default=[])
     * @param string  $namePrefix   The path/object prefix to apply to the field name (optional) (default='')
     * @param string  $excludeList  A (string) list of IDs to exclude (optional) (default=null)
     * @param boolean $disabled     Whether or not the checkboxes are to be disabled (optional) (default=false)
     *
     * @return The resulting dropdown data
     */
    public static function getCheckboxes_CategoryField($categoryPath, $values = [], $namePrefix = '', $excludeList = null, $disabled = false)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$categoryPath) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['category', 'HtmlUtil::getCheckboxes_CategoryField']));
        }

        if (!$lang) {
            $lang = ZLanguage::getLanguageCode();
        }

        $cats = CategoryUtil::getSubCategoriesByPath($categoryPath, 'path', false, true, false, true, false, '', 'value');

        foreach ($cats as $k => $v) {
            $val = $k;
            $fname = $val;
            if ($namePrefix) {
                $fname = $namePrefix . '[' . $k . ']';
            }

            if (false === strpos($excludeList, ',' . $k . ',')) {
                $disp = $v['display_name'][$lang];
                if (!$disp) {
                    $disp = $v['name'];
                }

                $html .= "<input type=\"checkbox\" name=\"" . DataUtil::formatForDisplay($fname) . "\" " . ($values[$k] ? ' checked="checked" ' : '') . ($disabled ? ' disabled="disabled" ' : '') . " />&nbsp;&nbsp;&nbsp;&nbsp;" . DataUtil::formatForDisplay($disp) . "<br />";
            }
        }

        return $html;
    }

    /**
     * Selector for a module's tables or entities.
     *
     * This method is Backward Compatible with all Core versions back to 1.2.x
     * It scans for tables in `tables.php` as well as locating Doctrine 1 tables
     * or Doctrine 2 entities in either the 1.3.0 type directories or 1.4.0++ type
     *
     * @param string  $modname       Module name
     * @param string  $name          Select field name
     * @param string  $selectedValue Selected value
     * @param string  $defaultValue  Value for "default" option
     * @param string  $defaultText   Text for "default" option
     * @param boolean $submit        Submit on choose
     * @param string  $remove        Remove string from table name
     * @param boolean $disabled      Add Disabled attribute to select
     * @param integer $nStripChars   Strip the first n characters
     * @param integer $multipleSize  Size for multiple selects
     *
     * @return string The rendered output
     */
    public static function getSelector_ModuleTables($modname, $name, $selectedValue = '', $defaultValue = 0, $defaultText = '', $submit = false, $remove = '', $disabled = false, $nStripChars = 0, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$modname) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['modname', 'HtmlUtil::getSelector_ModuleTables']));
        }

        // old style 'tables.php' modules (Core 1.2.x--)
        $tables = ModUtil::dbInfoLoad($modname, '', true);
        $data = [];
        if (is_array($tables) && $tables) {
            foreach ($tables as $k => $v) {
                if (false === strpos($k, '_column') && false === strpos($k, '_db_extra_enable') && false === strpos($k, '_primary_key_column')) {
                    $checkColumns = $k . '_column';
                    if (!isset($tables[$checkColumns])) {
                        continue;
                    }
                }
                if (false === strpos($k, '_column') && false === strpos($k, '_db_extra_enable') && false === strpos($k, '_primary_key_column')) {
                    if (0 === strpos($k, 'z_')) {
                        $k = substr($k, 4);
                    }

                    if ($remove) {
                        $k2 = str_replace($remove, '', $k);
                    } else {
                        $k2 = $k;
                    }

                    if ($nStripChars) {
                        $k2 = ucfirst(substr($k2, $nStripChars));
                    }

                    // Use $k2 for display also (instead of showing the internal table name)
                    $data[$k2] = $k2;
                }
            }
        }
        if (!empty($data)) {
            return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, null, null, $submit, $disabled, $multipleSize);
        }

        // Doctrine 1 models (Core 1.3.0 - 1.3.5)
        DoctrineUtil::loadModels($modname);
        $records = Doctrine::getLoadedModels();
        $data = [];
        foreach ($records as $recordClass) {
            // remove records from other modules
            if (substr($recordClass, 0, strlen($modname)) != $modname) {
                continue;
            }

            // get table name of remove table prefix
            $tableNameRaw = Doctrine::getTable($recordClass)->getTableName();
            sscanf($tableNameRaw, Doctrine_Manager::getInstance()->getAttribute(Doctrine::ATTR_TBLNAME_FORMAT), $tableName);

            if ($remove) {
                $tableName = str_replace($remove, '', $tableName);
            }

            if ($nStripChars) {
                $tableName = ucfirst(substr($tableName, $nStripChars));
            }

            $data[$tableName] = $tableName;
        }
        if (!empty($data)) {
            return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, null, null, $submit, $disabled, $multipleSize);
        }

        // Doctrine 2 entities (Core 1.3.0++)
        // Core-2.0 spec
        $module = ModUtil::getModule($modname);
        if ((null !== $module) && !class_exists($module->getVersionClass())) {
            // this check just confirming a Core-2.0 spec bundle - remove in 2.0.0
            $capabilities = $module->getMetaData()->getCapabilities();
            if (isset($capabilities['categorizable'])) {
                $data = [];
                $keys = array_keys($capabilities['categorizable']);
                $entityList = is_int($keys[0]) ? $capabilities['categorizable'] : $capabilities['categorizable'][$keys[0]];
                foreach ($entityList as $fullyQualifiedEntityName) {
                    $nameParts = explode('\\', $fullyQualifiedEntityName);
                    $entityName = array_pop($nameParts);
                    $data[$entityName] = $entityName;
                }
                $selectedValue = (1 == count($data)) ? $entityName : $defaultValue;

                return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, null, null, $submit, $disabled, $multipleSize);
            }
        }

        // (Core-1.3 spec)
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($modname));
        $modpath = (ModUtil::TYPE_SYSTEM == $modinfo['type']) ? 'system' : 'modules';
        $osdir   = DataUtil::formatForOS($modinfo['directory']);
        $entityDirs = [
            "$modpath/$osdir/Entity/", // Core 1.4.0++
            "$modpath/$osdir/lib/$osdir/Entity/", // Core 1.3.5--
        ];

        $entities = [];
        foreach ($entityDirs as $entityDir) {
            if (file_exists($entityDir)) {
                $files = scandir($entityDir);
                foreach ($files as $file) {
                    if ('.' != $file && '..' != $file && '.php' === substr($file, -4)) {
                        $entities[] = $file;
                    }
                }
            }
        }

        $data = [];
        foreach ($entities as $entity) {
            $possibleClassNames = [
                $modname . '_Entity_' . substr($entity, 0, strlen($entity) - 4), // Core 1.3.5--
            ];
            if ($module) {
                $possibleClassNames[] = $module->getNamespace() . '\\Entity\\' . substr($entity, 0, strlen($entity) - 4); // Core 1.4.0++
            }

            foreach ($possibleClassNames as $class) {
                if (class_exists($class)) {
                    $entityName = substr($entity, 0, strlen($entity) - 4);

                    if ($remove) {
                        $entityName = str_replace($remove, '', $entityName);
                    }

                    if ($nStripChars) {
                        $entityName = ucfirst(substr($entityName, $nStripChars));
                    }

                    $data[$entityName] = $entityName;
                }
            }
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, null, null, $submit, $disabled, $multipleSize);
    }

    /**
     * Selector for a module's tables.
     *
     * @param string  $modname           Module name
     * @param string  $tablename         Table name
     * @param string  $name              Select field name
     * @param string  $selectedValue     Selected value
     * @param string  $defaultValue      Value for "default" option
     * @param string  $defaultText       Text for "default" option
     * @param boolean $submit            Submit on choose
     * @param boolean $showSystemColumns Whether or not to show the system columns
     * @param boolean $disabled          Add Disabled attribute to select
     * @param integer $multipleSize      Size for multiple selects
     *
     * @return string The rendered output
     */
    public static function getSelector_TableFields($modname, $tablename, $name, $selectedValue = '', $defaultValue = 0, $defaultText = '', $submit = false, $showSystemColumns = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$modname) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['modname', 'HtmlUtil::getSelector_TableFields']));
        }

        if (!$tablename) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['tablename', 'HtmlUtil::getSelector_TableFields']));
        }

        if (!$name) {
            throw new \Exception(__f('Invalid %1$s passed to %2$s.', ['name', 'HtmlUtil::getSelector_TableFields']));
        }

        $tables = ModUtil::dbInfoLoad($modname, '', true);
        $colkey = $tablename . '_column';
        $cols = $tables[$colkey];

        if (!$cols) {
            throw new \Exception(__f('Invalid %1$s [%2$s] in %3$s.', ['column key', $colkey, 'HtmlUtil::getSelector_TableFields']));
        }

        if (!$showSystemColumns) {
            $filtercols = [];
            ObjectUtil::addStandardFieldsToTableDefinition($filtercols, '');
        }

        $data = [];
        foreach ($cols as $k => $v) {
            if ($showSystemColumns) {
                $data[$v] = $k;
            } else {
                if (!$filtercols[$k]) {
                    $data[$v] = $k;
                }
            }
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the HTML code for the Yes/No dropdown.
     *
     * @param integer $selected The value which should be selected (default=1) (optional)
     * @param string  $name     The name of the generated selector (optional)
     *
     * @return The resulting HTML string
     */
    public static function getSelector_YesNo($selected = '1', $name = '')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'permission';
        }

        $vals = [__('No'), __('Yes')];

        return self::getSelector_Generic($name, $vals);
    }

    /**
     * Return the localized string for the specified yes/no value.
     *
     * @param integer $val The value for which we wish to obtain the string representation
     *
     * @return The string representation for the selected value
     */
    public static function getSelectorValue_YesNo($val)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $vals = [__('No'), __('Yes')];

        return $vals[$val];
    }

    /**
     * Return the dropdown data for the language selector.
     *
     * @param boolean $includeAll Whether or not to include the 'All' choice
     *
     * @return The string representation for the selected value
     */
    public static function getSelectorData_Language($includeAll = true)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $langlist = [];
        $dropdown = [];

        if ($includeAll) {
            $dropdown[] = ['id' => '', 'name' => __('All')];
        }

        $langlist = ZLanguage::getInstalledLanguageNames();

        asort($langlist);

        foreach ($langlist as $k => $v) {
            $dropdown[] = ['id' => $k, 'name' => $v];
        }

        return $dropdown;
    }

    /**
     * Return the localized string for the given value.
     *
     * @param mixed $value The currently active/selected value
     *
     * @return The resulting HTML string
     */
    public static function getSelectorValue_Permission($value)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $perms = [];
        $perms[_Z_PERMISSION_BASIC_PRIVATE] = __('Private');
        $perms[_Z_PERMISSION_BASIC_GROUP] = __('Group');
        $perms[_Z_PERMISSION_BASIC_USER] = __('User');
        $perms[_Z_PERMISSION_BASIC_PUBLIC] = __('Public');

        return $perms[$value];
    }

    /**
     * Return the HTML code for the Permission dropdown.
     *
     * @param string  $name          The name of the generated selector (optional) (default='permission')
     * @param integer $selectedValue The value which should be selected (optional) (default=2)
     *
     * @return The resulting HTML string
     */
    public static function getSelector_Permission($name = 'permission', $selectedValue = 'U')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'permission';
        }

        $perms = [];
        $perms[_Z_PERMISSION_BASIC_PRIVATE] = __('Private');
        $perms[_Z_PERMISSION_BASIC_GROUP] = __('Group');
        $perms[_Z_PERMISSION_BASIC_USER] = __('User');
        $perms[_Z_PERMISSION_BASIC_PUBLIC] = __('Public');

        return self::getSelector_Generic($name, $perms, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the HTML code for the Permission Level dropdown.
     *
     * @param string  $name          The name of the generated selector (optional) (default='permission')
     * @param integer $selectedValue The value which should be selected (optional) (default=0)
     *
     * @return The resulting HTML string
     */
    public static function getSelector_PermissionLevel($name = 'permission', $selectedValue = '0')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $perms = [];
        $perms[_Z_PERMISSION_LEVEL_NONE] = __('No access');
        $perms[_Z_PERMISSION_LEVEL_READ] = __('Read access');
        $perms[_Z_PERMISSION_LEVEL_WRITE] = __('Write access');

        return self::getSelector_Generic($name, $perms, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the html for the PN user group selector.
     *
     * @param string  $name          The selector name
     * @param integer $selectedValue The currently selected value of the selector (optional) (default=0)
     * @param integer $defaultValue  The default value of the selector (optional) (default=0)
     * @param string  $defaultText   The text of the default value (optional) (default='')
     * @param integer $allValue      The value to assign for the "All" choice (optional) (default=0)
     * @param string  $allText       The text to display for the "All" choice (optional) (default='')
     * @param string  $excludeList   A (string) list of IDs to exclude (optional) (default=null)
     * @param boolean $submit        Whether or not to auto-submit the selector (optional) (default=false)
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The html for the user group selector
     */
    public static function getSelector_Group($name = 'groupid', $selectedValue = 0, $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $excludeList = '', $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $data = [];
        $grouplist = UserUtil::getGroups([], ['name' => 'ASC']);
        foreach ($grouplist as $k => $v) {
            $id = $v['gid'];
            $disp = $v['name'];
            if (false === strpos($excludeList, ",$id,")) {
                $data[$id] = $disp;
            }
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return a PN array strcuture for the PN user dropdown box.
     *
     * @param string  $name          The selector name
     * @param integer $gid           The group ID to get users for (optional) (default=null)
     * @param integer $selectedValue The currently selected value of the selector (optional) (default=0)
     * @param integer $defaultValue  The default value of the selector (optional) (default=0)
     * @param string  $defaultText   The text of the default value (optional) (default='')
     * @param integer $allValue      The value to assign for the "All" choice (optional) (default='')
     * @param string  $allText       The text to display for the "All" choice (optional) (default='')
     * @param string  $excludeList   A (string) list of IDs to exclude (optional) (default=null)
     * @param boolean $submit        Whether or not to auto-submit the selector (optional) (default=false)
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The string for the user group selector
     */
    public static function getSelector_User($name = 'userid', $gid = null, $selectedValue = 0, $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $excludeList = '', $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $where = '';
        if ($excludeList) {
            $where = "WHERE uid NOT IN ($excludeList)";
        }

        if ($gid) {
            $users = UserUtil::getUsersForGroup($gid);
            if ($users) {
                $and = $where ? ' AND ' : '';
                $where .= $and . 'uid IN (' . implode(',', $users) . ')';
            }
        }

        $data = [];
        $userlist = UserUtil::getUsers($where, 'ORDER BY uname');
        foreach ($userlist as $k => $v) {
            $data[$k] = $v['uname'];
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the html for the PNModule selector.
     *
     * @param string  $name          The selector name
     * @param integer $selectedValue The currently selected value of the selector (optional) (default=0)
     * @param integer $defaultValue  The default value of the selector (optional) (default=0)
     * @param string  $defaultText   The text of the default value (optional) (default='')
     * @param integer $allValue      The value to assign the "All" choice (optional) (default=0)
     * @param string  $allText       The text to display for the "All" choice (optional) (default='')
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     * @param string  $field         The field to use for value
     *
     * @return The string for the user group selector
     */
    public static function getSelector_Module($name = 'moduleName', $selectedValue = 0, $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $disabled = false, $multipleSize = 1, $field = 'name')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $data = [];
        $modules = ModUtil::getModulesByState(3, 'displayname');
        foreach ($modules as $module) {
            $value = $module[$field];
            $displayname = $module['displayname'];
            $data[$value] = $displayname;
        }

        return self::getSelector_Generic($name, $data, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize);
    }

    /**
     * Return the HTML for the date day selector.
     *
     * @param integer $selectedValue The value which should be selected (default=0) (optional)
     * @param string  $name          The name of the generated selector (default='day') (optional)
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_DatetimeDay($selectedValue = 0, $name = 'day', $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'day';
        }

        $data = [];
        for ($i = 1; $i < 32; $i++) {
            $val = sprintf("%02d", $i);
            $data[$val] = $val;
        }

        return self::getSelector_Generic($name, $data, $selectedValue, null, null, null, null, $submit, $disabled, $multipleSize = 1);
    }

    /**
     * Return the HTML for the date hour selector.
     *
     * @param integer $selectedValue The value which should be selected (default=0) (optional)
     * @param string  $name          The name of the generated selector (default='hour') (optional)
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_DatetimeHour($selectedValue = 0, $name = 'hour', $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'hour';
        }

        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $val = sprintf("%02d", $i);
            $data[$val] = $val;
        }

        return self::getSelector_Generic($name, $data, $selectedValue, null, null, null, null, $submit, $disabled, $multipleSize = 1);
    }

    /**
     * Return the HTML for the date minute selector.
     *
     * @param integer $selectedValue The value which should be selected (default=0) (optional)
     * @param string  $name          The name of the generated selector (default='minute') (optional)
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_DatetimeMinute($selectedValue = 0, $name = 'minute', $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'minute';
        }

        $data = [];
        for ($i = 0; $i < 60; $i += 5) {
            $val = sprintf('%02d', $i);
            $data[$val] = $val;
        }

        return self::getSelector_Generic($name, $data, $selectedValue, null, null, null, null, $submit, $disabled, $multipleSize = 1);
    }

    /**
     * Return the HTML for the date month selector.
     *
     * @param integer $selected     The value which should be selected (default=0) (optional)
     * @param string  $name         The name of the generated selector (default='month') (optional)
     * @param boolean $submit       Whether or not to auto-submit the selector
     * @param boolean $disabled     Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     * @param string  $text         Text to print
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_DatetimeMonth($selected = 0, $name = 'month', $submit = false, $disabled = false, $multipleSize = 1, $text = 0)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'month';
        }

        if ($text) {
            $mnames = explode(' ', __('January February March April May June July August September October November December'));
        }
        array_unshift($mnames, 'noval');

        $id = strtr($name, '[]', '__');
        $disabled = $disabled ? 'disabled="disabled"' : '';
        $multiple = $multipleSize > 1 ? 'multiple="multiple"' : '';
        $multipleSize = $multipleSize > 1 ? "size=\"$multipleSize\"" : '';
        $submit = $submit ? 'onchange="this.form.submit();"' : '';

        $html = "<select name=\"" . DataUtil::formatForDisplay($name) . "\" id=\"" . DataUtil::formatForDisplay($id) . "\" " . DataUtil::formatForDisplay($multipleSize) . " $multiple $submit $disabled>";

        for ($i = 1; $i < 13; $i++) {
            $val = sprintf("%02d", $i);
            $opt = $text ? $mnames[$i] : $val;
            $sel = ($i == $selected ? 'selected="selected"' : '');
            $html = $html . "<option value=\"$val\" $sel>" . DataUtil::formatForDisplay($opt) . "</option>";
        }

        $html = $html . '</select>';

        return $html;
    }

    /**
     * Return the HTML for the date year selector.
     *
     * @param integer $selectedValue The value which should be selected (default=2009) (optional)
     * @param string  $name          The name of the generated selector (default='year') (optional)
     * @param integer $first         The start year for the selector (default=2003) (optional)
     * @param integer $last          The name of the generated selector (default=2007) (optional)
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_DatetimeYear($selectedValue = 2009, $name = 'year', $first = 2003, $last = 2008, $submit = false, $disabled = false, $multipleSize = 1)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (!$name) {
            $name = 'year';
        }

        $data = [];
        for ($i = $first; $i < $last; $i++) {
            $data[$i] = $i;
        }

        return self::getSelector_Generic($name, $data, $selectedValue, null, null, null, null, $submit, $disabled, $multipleSize = 1);
    }

    /**
     * Return the HTML for the country selector.
     *
     * @param string  $name          The name of the generated selector (default='countries') (optional)
     * @param string  $selectedValue The value which is currently selected (default='') (optional)
     * @param string  $defaultValue  The default value to select (default='') (optional)
     * @param string  $defaultText   The text for the default value (default='') (optional)
     * @param integer $allValue      The value to assign for the "All" choice (optional) (default=0)
     * @param string  $allText       The text to display for the "All" choice (optional) (default='')
     * @param boolean $submit        Whether or not to auto-submit the selector
     * @param boolean $disabled      Whether or not to disable selector (optional) (default=false)
     * @param integer $multipleSize  The size to use for a multiple selector, 1 produces a normal/single selector (optional (default=1)
     * @param string  $id            The ID of the generated selector (optional)
     * @param string  $class         The class of the generated selector (optional)
     * @param string $required      Specifies that the user is required to select a value
     *                               before submitting the form (optional)
     * @param string  $title         The title of the generated selector (optional)
     *
     * @return The generated HTML for the selector
     */
    public static function getSelector_Countries($name = 'countries', $selectedValue = '', $defaultValue = 0, $defaultText = '', $allValue = 0, $allText = '', $submit = false, $disabled = false, $multipleSize = 1, $id = null, $class = null, $required = false, $title = null)
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        $countries = ZLanguage::countryMap();
        asort($countries);

        return self::getSelector_Generic($name, $countries, $selectedValue, $defaultValue, $defaultText, $allValue, $allText, $submit, $disabled, $multipleSize, $id, $class, $required, $title);
    }

    /**
     * Same as PN HTMLApi function but adds javascript form submit code to selector.
     *
     * @param string  $fieldname Field name
     * @param array   $data      Data array
     * @param integer $multiple  Whether or not this is a multiple select
     * @param integer $size      Size for multiple selects
     * @param string  $selected  Selected value
     * @param string  $accesskey Access key
     * @param string  $onchange  OnChange event
     *
     * @return string The rendered output
     */
    public static function FormSelectMultipleSubmit($fieldname, $data, $multiple = 0, $size = 1, $selected = '', $accesskey = '', $onchange = '')
    {
        @trigger_error('HtmlUtil is deprecated. please use Twig extensions and Symfony forms instead.', E_USER_DEPRECATED);

        if (empty($fieldname)) {
            return '';
        }

        // Set up selected if required
        if (!empty($selected)) {
            for ($i = 0; !empty($data[$i]); $i++) {
                if ($data[$i]['id'] == $selected) {
                    $data[$i]['selected'] = 1;
                }
            }
        }

        $c = count($data);
        if ($c < $size) {
            $size = $c;
        }

        $idname = strtr($fieldname, '[]', '__');

        $output = '<select' . ' name="' . DataUtil::formatForDisplay($fieldname) . '"'
                . ' id="' . DataUtil::formatForDisplay($idname) . '"'
                . ' size="' . DataUtil::formatForDisplay($size) . '"'
                . ((1 == $multiple) ? ' multiple="multiple"' : '')
                . ((empty($accesskey)) ? '' : ' accesskey="' . DataUtil::formatForDisplay($accesskey) . '"')
                //. ' tabindex="'.$this->tabindex.'"'
                . ($onchange ? " onchange=\"$onchange\"" : '') . '>';

        foreach ($data as $datum) {
            $output .= '<option value="' . DataUtil::formatForDisplay($datum['id']) . '"' . ((empty($datum['selected'])) ? '' : " selected='$datum[selected]'") . '>' . DataUtil::formatForDisplay($datum['name']) . '</option>';
        }

        $output .= '</select>';

        return $output;
    }
}
