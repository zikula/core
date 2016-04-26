<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller\FormData;

use ModUtil;
use ServiceUtil;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form data container and validator.
 */
abstract class AbstractFormData extends \Zikula_AbstractBase
{
    /**
     * The value for the form's id attribute, and used in creating the id attribute for each field.
     *
     * @var string
     */
    private $formId;

    /**
     * An array index of the field ids defined in this form container, used to prevent duplication.
     *
     * @var array
     */
    private $fieldIds;

    /**
     * The form fields defined in this container.
     *
     * @var array
     */
    private $formFields;

    /**
     * Construct a new form data container instance, initializing the id value.
     *
     * @param string             $formId         A value for the form's id attribute.
     * @param ContainerInterface $serviceManager The current service manager instance.
     *
     * @throws \InvalidArgumentException Thrown if the specified form id is not valid.
     */
    public function __construct($formId, ContainerInterface $serviceManager = null)
    {
        if (!isset($serviceManager)) {
            $serviceManager = ServiceUtil::getManager();
        }
        $bundle = ModUtil::getModule('ZikulaUsersModule');
        parent::__construct($serviceManager, $bundle);

        $formId = trim($formId);
        if (!isset($formId) || !is_string($formId) || empty($formId)) {
            throw new \InvalidArgumentException($this->__('Invalid form id.'));
        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $formId)) {
            throw new \InvalidArgumentException($this->__f('The form id \'%1$s\' contains invalid characters.', array($formId)));
        }
        $this->formId = $formId;

        $this->fieldIds = array();
        $this->formFields = array();
    }

    /**
     * Add a field to the form container.
     *
     * @param Field $field The field definition.
     *
     * @return Field A reference to the field just added, to allow for function chaining to configure the field.
     *
     * @throws \InvalidArgumentException Thrown if the field definition is not valid, a field with the specified name is already defined, or adding the field would result in a duplicate field id.
     */
    public function addField(Field $field)
    {
        if (!isset($field)) {
            throw new \InvalidArgumentException($this->__('Invalid field definition'));
        } elseif ($field->getFormContainer() !== $this) {
            throw new \InvalidArgumentException($this->__('Form container mismatch.'));
        } elseif (array_key_exists($field->fieldName, $this->formFields)) {
            throw new \InvalidArgumentException($this->__f('Field definition for the \'%1$s\' field is already defined.', array($field->fieldName)));
        } elseif (array_key_exists($field->fieldId, $this->fieldIds)) {
            throw new \InvalidArgumentException($this->__f('Field definition duplicates the field id \'%1$s\' already claimed by the field \'%2$s\'.', array($field->fieldId, $this->fieldIds[$field->fieldId])));
        }

        $this->formFields[$field->fieldName] = $field;
        $this->fieldIds[$field->fieldId] = &$this->formFields[$field->fieldName];

        return $this->formFields[$field->fieldName];
    }

    /**
     * Retrieve the form id.
     *
     * @return string The form id.
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Retrieve a field definition with the specified name.
     *
     * @param string $fieldName The name of the field previously added to this form container.
     *
     * @return Field The field definition for the specified name.
     *
     * @throws \InvalidArgumentException Thrown if this form data container does not contain a field with the specified name.
     */
    public function getField($fieldName)
    {
        if (!isset($this->formFields[$fieldName])) {
            throw new \InvalidArgumentException($this->__f('Invalid field name: %1$s', array($fieldName)));
        }

        return $this->formFields[$fieldName];
    }

    /**
     * Retrieve the value for the id attribute for the field of the specified name.
     *
     * This is a pass-through function to the field's getFieldId() method. This function calls {@link getField()}, which may throw an exception.
     *
     * @param string $fieldName The name of the field defintion previously added to this form data container.
     *
     * @return string The value for the field's id attribute.
     */
    public function getFieldId($fieldName)
    {
        return $this->getField($fieldName)->getFieldId();
    }

    /**
     * Retrieve the value of the data for the field of the specified name.
     *
     * This is a pass-through function to the field's getData() method. This function calls {@link getField()}, which may throw an exception.
     *
     * @param string $fieldName The name of the field defintion previously added to this form data container.
     *
     * @return mixed The value for the field's data.
     */
    public function getFieldData($fieldName)
    {
        return $this->getField($fieldName)->getData();
    }

    /**
     * Retrieve the value of error message for the field of the specified name.
     *
     * This is a pass-through function to the field's getErrorMessage() method. This function calls {@link getField()}, which may throw an exception.
     *
     * @param string $fieldName The name of the field defintion previously added to this form data container.
     *
     * @return string|boolean The value for the field's error message; false if a message is not set.
     */
    public function getFieldErrorMessage($fieldName)
    {
        return $this->getField($fieldName)->getErrorMessage();
    }

    /**
     * Validate the contents of this form data container.
     *
     * @return boolean True if all fields and dependencies validate; otherwise false.
     */
    public function isValid()
    {
        $isValid = true;

        foreach ($this->formFields as $formField) {
            // Must be called this way to ensure that nothing is skipped by PHP's short-circuit boolean expression evaluation.
            // E.g., do not do $isValid = $isValid && $formField->isValid();, because if $isValid is already false, then the
            // call to $formField->isValid will be skipped. The isValid() method must be called for all fields in order to set
            // all error messages.
            if (!$formField->isValid()) {
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Retreive an array list of all error messages currently set for fields in this form data container.
     *
     * @return array An array of error messages indexed by field name; the array may be empty if there are no error messages set.
     */
    public function getErrorMessages()
    {
        $returnValue = array();

        foreach ($this->formFields as $formField) {
            $error = $formField->getErrorMessage();
            if ($error) {
                $returnValue[$formField->getFieldName()] = $error;
            }
        }

        return $returnValue;
    }

    /**
     * Reset the validation status for the entire form data container.
     *
     * @return void
     */
    public function clearValidation()
    {
        foreach ($this->formFields as $formField) {
            $error = $formField->clearValidation();
        }
    }

    /**
     * Set the data for one field contained by this form data container.
     *
     * @param string $fieldName The field name of the field to be set.
     * @param mixed  $value     The value to set.
     *
     * @return void
     */
    public function setField($fieldName, $value)
    {
        if (array_key_exists($fieldName, $this->formFields)) {
            $this->formFields[$fieldName]->setData($value);
        }
        $this->clearValidation();
    }

    /**
     * Set the data for the field defintiions contained by this form data container from an array.
     *
     * The array should be indexed by field name. Indexes that do no represent a known field definition are ignored. The validation
     * status of the form data container is reset by this function.
     *
     * @param array $data The field name index array of data to set.
     *
     * @return void
     */
    public function setFromArray(array $data)
    {
        foreach ($this->formFields as $fieldName => $formField) {
            if (array_key_exists($fieldName, $data)) {
                $this->formFields[$fieldName]->setData($data[$fieldName]);
            }
        }
        $this->clearValidation();
    }

    /**
     * Set the data for the field definitions contained by this form data container from session variables.
     *
     * The session variables should be named the same as the field names. Session variables within the namespace that
     * do not represent known fields are ignored. The validation status of the form data container is reset by this function.
     *
     * @param \Zikula_Session $session   The session instance.
     * @param string         $namespace The session namespace where the fields are found; optional; defaults to '/'.
     *
     * @return void
     */
    public function setFromSession(\Zikula_Session $session, $namespace = '/')
    {
        foreach ($this->formFields as $fieldName => $formField) {
            if ($session->has($fieldName, $namespace)) {
                $this->formFields[$fieldName]->setData($session->get($fieldName, null, $namespace));
            }
        }
        $this->clearValidation();
    }

    /**
     * Set the data for the field definitions contained by this form data container from request (post, get, etc.) variables.
     *
     * The request variables should be named the same as the field names. Request variables within the namespace that
     * do not represent known fields are ignored. The validation status of the form data container is reset by this function.
     *
     * @param ParameterBag $requestCollection The request collection (e.g. $this->request->request) from which to set field data.
     *
     * @return void
     */
    public function setFromRequestCollection(ParameterBag $requestCollection)
    {
        foreach ($this->formFields as $fieldName => $formField) {
            if ($requestCollection->has($fieldName)) {
                $this->formFields[$fieldName]->setData($requestCollection->get($fieldName));
            }
        }
        $this->clearValidation();
    }

    /**
     * Convert the form data collection to an array indexed by field name.
     *
     * @return array An array containing the form data container's field data, indexed by field name.
     */
    public function toArray()
    {
        $returnValue = array();

        foreach ($this->formFields as $formField) {
            $returnValue[$formField->getFieldName()] = $formField->getData();
        }

        return $returnValue;
    }
}
