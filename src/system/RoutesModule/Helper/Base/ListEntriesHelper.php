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

namespace Zikula\RoutesModule\Helper\Base;

use Zikula\Common\Translator\Translator;

/**
 * Utility base class for list field entries related methods.
 */
class ListEntriesHelper
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Constructor.
     * Initialises member vars.
     *
     * @param Translator $translator Translator service instance.
     *
     * @return void
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Return the name or names for a given list item.
     *
     * @param string $value      The dropdown value to process.
     * @param string $objectType The treated object type.
     * @param string $fieldName  The list field's name.
     * @param string $delimiter  String used as separator for multiple selections.
     *
     * @return string List item name.
     */
    public function resolve($value, $objectType = '', $fieldName = '', $delimiter = ', ')
    {
        if ((empty($value) && $value != '0') || empty($objectType) || empty($fieldName)) {
            return $value;
        }

        $isMulti = $this->hasMultipleSelection($objectType, $fieldName);
        if ($isMulti === true) {
            $value = $this->extractMultiList($value);
        }

        $options = $this->getEntries($objectType, $fieldName);
        $result = '';

        if ($isMulti === true) {
            foreach ($options as $option) {
                if (!in_array($option['value'], $value)) {
                    continue;
                }
                if (!empty($result)) {
                    $result .= $delimiter;
                }
                $result .= $option['text'];
            }
        } else {
            foreach ($options as $option) {
                if ($option['value'] != $value) {
                    continue;
                }
                $result = $option['text'];
                break;
            }
        }

        return $result;
    }

    /**
     * Extract concatenated multi selection.
     *
     * @param string  $value The dropdown value to process.
     *
     * @return array List of single values.
     */
    public function extractMultiList($value)
    {
        $listValues = explode('###', $value);
        $amountOfValues = count($listValues);
        if ($amountOfValues > 1 && $listValues[$amountOfValues - 1] == '') {
            unset($listValues[$amountOfValues - 1]);
        }
        if ($listValues[0] == '') {
            // use array_shift instead of unset for proper key reindexing
            // keys must start with 0, otherwise the dropdownlist form plugin gets confused
            array_shift($listValues);
        }

        return $listValues;
    }

    /**
     * Determine whether a certain dropdown field has a multi selection or not.
     *
     * @param string $objectType The treated object type.
     * @param string $fieldName  The list field's name.
     *
     * @return boolean True if this is a multi list false otherwise.
     */
    public function hasMultipleSelection($objectType, $fieldName)
    {
        if (empty($objectType) || empty($fieldName)) {
            return false;
        }

        $result = false;
        switch ($objectType) {
            case 'route':
                switch ($fieldName) {
                    case 'workflowState':
                        $result = false;
                        break;
                    case 'routeType':
                        $result = false;
                        break;
                    case 'schemes':
                        $result = true;
                        break;
                    case 'methods':
                        $result = true;
                        break;
                }
                break;
        }

        return $result;
    }

    /**
     * Get entries for a certain dropdown field.
     *
     * @param string  $objectType The treated object type.
     * @param string  $fieldName  The list field's name.
     *
     * @return array Array with desired list entries.
     */
    public function getEntries($objectType, $fieldName)
    {
        if (empty($objectType) || empty($fieldName)) {
            return array();
        }

        $entries = array();
        switch ($objectType) {
            case 'route':
                switch ($fieldName) {
                    case 'workflowState':
                        $entries = $this->getWorkflowStateEntriesForRoute();
                        break;
                    case 'routeType':
                        $entries = $this->getRouteTypeEntriesForRoute();
                        break;
                    case 'schemes':
                        $entries = $this->getSchemesEntriesForRoute();
                        break;
                    case 'methods':
                        $entries = $this->getMethodsEntriesForRoute();
                        break;
                }
                break;
        }

        return $entries;
    }

    /**
     * Get 'workflow state' list entries.
     *
     * @return array Array with desired list entries.
     */
    public function getWorkflowStateEntriesForRoute()
    {
        $states = array();
        $states[] = array('value'   => 'approved',
                          'text'    => $this->translator->__('Approved'),
                          'title'   => $this->translator->__('Content has been approved and is available online.'),
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => '!approved',
                          'text'    => $this->translator->__('All except approved'),
                          'title'   => $this->translator->__('Shows all items except these which are approved'),
                          'image'   => '',
                          'default' => false);

        return $states;
    }

    /**
     * Get 'route type' list entries.
     *
     * @return array Array with desired list entries.
     */
    public function getRouteTypeEntriesForRoute()
    {
        $states = array();
        $states[] = array('value'   => 'additional',
                          'text'    => $this->translator->__('Additional'),
                          'title'   => '',
                          'image'   => '',
                          'default' => true);
        $states[] = array('value'   => 'temporaryRedirect',
                          'text'    => $this->translator->__('Temporary redirect'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'permanentRedirect',
                          'text'    => $this->translator->__('Permanent redirect'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'replace',
                          'text'    => $this->translator->__('Replace'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);

        return $states;
    }

    /**
     * Get 'schemes' list entries.
     *
     * @return array Array with desired list entries.
     */
    public function getSchemesEntriesForRoute()
    {
        $states = array();
        $states[] = array('value'   => 'http',
                          'text'    => $this->translator->__('Http'),
                          'title'   => '',
                          'image'   => '',
                          'default' => true);
        $states[] = array('value'   => 'https',
                          'text'    => $this->translator->__('Https'),
                          'title'   => '',
                          'image'   => '',
                          'default' => true);

        return $states;
    }

    /**
     * Get 'methods' list entries.
     *
     * @return array Array with desired list entries.
     */
    public function getMethodsEntriesForRoute()
    {
        $states = array();
        $states[] = array('value'   => 'GET',
                          'text'    => $this->translator->__('G e t'),
                          'title'   => '',
                          'image'   => '',
                          'default' => true);
        $states[] = array('value'   => 'POST',
                          'text'    => $this->translator->__('P o s t'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'HEAD',
                          'text'    => $this->translator->__('H e a d'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'PUT',
                          'text'    => $this->translator->__('P u t'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'DELETE',
                          'text'    => $this->translator->__('D e l e t e'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);
        $states[] = array('value'   => 'OPTIONS',
                          'text'    => $this->translator->__('O p t i o n s'),
                          'title'   => '',
                          'image'   => '',
                          'default' => false);

        return $states;
    }
}
