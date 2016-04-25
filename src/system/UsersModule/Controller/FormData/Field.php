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

use ServiceUtil;

/**
 * One field in a form data container.
 */
class Field extends \Zikula_AbstractBase
{
    /**
     * A reference back to the parent form data container.
     *
     * @var AbstractFormData
     */
    private $formContainer;

    /**
     * The name of the field.
     *
     * @var string
     */
    private $fieldName;

    /**
     * The field id.
     *
     * @var string
     */
    private $fieldId;

    /**
     * The field's default value, if no value is provided.
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Indicates whether the field will accept null values as valid or not.
     *
     * @var boolean
     */
    private $nullAllowed;

    /**
     * The actual data value.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Indicates whether isValid() been called.
     *
     * @var boolean
     */
    protected $hasBeenValidated;

    /**
     * An array of Users_Controller_FormData_Validator_AbstractValidator instancess to apply during isValid().
     *
     * @var array
     */
    protected $validators;

    /**
     * The result of the last call to isValid() if an error was detected.
     *
     * @var string
     */
    private $errorMessage;

    /**
     * Build a new form data container field.
     *
     * @param AbstractFormData         $formContainer  The parent form data container.
     * @param string                   $fieldName      The name of the field.
     * @param mixed                    $initialValue   The initial value of the field.
     * @param mixed                    $defaultValue   The defaule value for the field.
     * @param \Zikula_ServiceManager   $serviceManager The current service manager instance.
     *
     * @throws \InvalidArgumentException Thrown if any of the parameters are not valid.
     */
    public function __construct(AbstractFormData $formContainer, $fieldName, $initialValue = null, $defaultValue = null, \Zikula_ServiceManager $serviceManager = null)
    {
        if (!isset($serviceManager)) {
            $serviceManager = ServiceUtil::getManager();
        }
        parent::__construct($serviceManager);

        if (!isset($formContainer)) {
            throw new \InvalidArgumentException($this->__('Invalid form container.'));
        } else {
            $this->formContainer = $formContainer;
        }

        $fieldName = trim($fieldName);
        if (!isset($fieldName) || !is_string($fieldName) || empty($fieldName)) {
            throw new \InvalidArgumentException($this->__f('Invalid field name: \'%1$s\'.', array($fieldName)));
        } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9_\x7f-\xff]*(\[(\d+|[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\])*$/', $fieldName)) {
            throw new \InvalidArgumentException($this->__f('The field name \'%1$s\' contains invalid characters.', array($fieldName)));
        } else {
            $this->fieldName = $fieldName;
            $this->fieldId = preg_replace('/[^a-z0-9_]/', '_', mb_strtolower($fieldName));
        }

        $this->data = $initialValue;
        $this->defaultValue = $defaultValue;
        $this->nullAllowed = false;
        $this->hasBeenValidated = false;
        $this->validators = array();
    }

    /**
     * Retrieve the parent form data container.
     *
     * @return AbstractFormData The form data container that owns this field.
     */
    public function getFormContainer()
    {
        return $this->formContainer;
    }

    /**
     * Provides attribute access to protected properties, if accessors are defined for those properites.
     *
     * @param string $name The name of the property to retrieve.
     *
     * @return mixed The value of the specified property.
     *
     * @throws \OutOfBoundsException Thrown if the specified name does not exist, the corresponding property does not have an accessor, or the specified name is invalid.
     */
    public function __get($name)
    {
        $returnValue = false;

        if (!isset($name) || empty($name)) {
            throw new \OutOfBoundsException($this->__f('Invalid field name: \'%1$s\'', array($name)));
        } else {
            $methodName = 'get' . ucfirst($name);
            if (method_exists($this, $methodName)) {
                $returnValue = $this->$methodName();
            } else {
                $methodName = 'is' . ucfirst($name);
                if (method_exists($this, $methodName)) {
                    $returnValue = $this->$methodName();
                } else {
                    throw new \OutOfBoundsException($this->__f('Invalid field name: \'%1$s\'', array($name)));
                }
            }
        }

        return $returnValue;
    }

    /**
     * Provides property isset access for protected properties that have an accessor defined.
     *
     * @param string $name The name of the property to query.
     *
     * @return boolean True if the property exists, provides an accessor, and is not null.
     *
     * @throws \OutOfBoundsException Thrown if the specified name does not exist, the corresponding property does not have an accessor, or the specified name is invalid.
     */
    public function __isset($name)
    {
        $returnValue = false;

        if (!isset($name) || empty($name) || ($name[0] == '_')) {
            throw new \OutOfBoundsException($this->__f('Invalid field name: \'%1$s\'', array($name)));
        } else {
            $methodName = 'get' . ucfirst($name);
            if (method_exists($this, $methodName)) {
                $returnValue = isset($this->$name);
            } else {
                throw new \OutOfBoundsException($this->__f('Invalid field name: \'%1$s\'', array($name)));
            }
        }

        return $returnValue;
    }

    /**
     * Retrieve the name of the field.
     *
     * @return string The name of the field.
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Retrieve the field form id (the form id combined with the field name, separated by an underscore).
     *
     * @return string The field's form id.
     */
    public function getFieldId()
    {
        return $this->formContainer->getFormId() . '_' . $this->fieldId;
    }

    /**
     * Retrieve the specified inital value for the field.
     *
     * @return mixed The field's inital value.
     */
    public function getNewItemInitialValue()
    {
        return $this->newItemInitialValue;
    }

    /**
     * Retrieve the field's default value.
     *
     * @return mixed The field's default value.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Allows or disallows the field to contain a null as a valid value.
     *
     * @param boolean $isNullAllowed True if null values are valid for this field, otherwise false.
     *
     * @return Field Returns $this to allow for function chaining.
     *
     * @throws \InvalidArgumentException Thrown if the value of the parameter is not a boolean value.
     */
    public function setNullAllowed($isNullAllowed)
    {
        if (is_bool($isNullAllowed)) {
            $this->nullAllowed = $isNullAllowed;
        } else {
            throw new InvalidArgumentException($this->__('The value supplied for the $isNullAllowed parameter is not a boolean.'));
        }

        return $this;
    }

    /**
     * Indicates whether this field allows null values as valid values or not.
     *
     * @return boolean True if null is a valid value for this field; otherwise false.
     */
    public function isNullAllowed()
    {
        return $this->nullAllowed;
    }

    /**
     * Add a validator to this field, defining what values are valid.
     *
     * This can be called multiple times to add a chain of several validators.
     *
     * @param Validator\AbstractValidator $validator The validator to be attached to this field for validation of its data.
     *
     * @return Field Returns $this to allow function chaining.
     */
    public function addValidator(Validator\AbstractValidator $validator)
    {
        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Validates the field data based on whether nulls are valid values or not.
     *
     * @return boolean True if the field's data is null and they are allowed, or if the field's value is not null; otherwise false.
     */
    protected function isValidNullAllowed()
    {
        $valid = true;

        if (!$this->nullAllowed) {
            if (!isset($this->data)) {
                $valid = false;
                $this->setErrorMessage($this->__('The value for this field cannot be null.'));
            }
        }

        return $valid;
    }

    /**
     * Validates this field's data against all of its validators, including the isValidNullAllowed() validator.
     *
     * @return boolean True if all validators attached to this field indicate that the field's value is valid; otherwise false.
     */
    public function isValid()
    {
        $valid = $this->isValidNullAllowed();

        if ($valid && isset($this->data)) {
            foreach ($this->validators as $validator) {
                if (!$validator->isValid($this->data)) {
                    $valid = false;
                    $this->setErrorMessage($validator->getErrorMessage());
                    break;
                }
            }
        }

        $this->hasBeenValidated = true;

        return $valid;
    }

    /**
     * Retrieves the error message set during the last call to isValid(), or false if no error message is set.
     *
     * @return string|boolean The error message from the last call to isValid(); otherwise false if there is no error message set.
     */
    public function getErrorMessage()
    {
        $error = false;
        if ($this->hasErrorMessage()) {
            $error =  $this->errorMessage;
        }

        return $error;
    }

    /**
     * Indicates whether the field has an error message to report or not.
     *
     * @return boolean True if the field has been validated and also has an error message; otherwise false.
     */
    public function hasErrorMessage()
    {
        return $this->hasBeenValidated && isset($this->errorMessage);
    }

    /**
     * Saves the error message, and indicates that the field has been validated.
     *
     * @param string $message The error message.
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
        $this->hasBeenValidated = true;
    }

    /**
     * Resets the field's error message and validation status indicator.
     */
    public function clearValidation()
    {
        unset($this->errorMessage);
        $this->hasBeenValidated = false;
    }

    /**
     * Sets the field's data value.
     *
     * The field's data is not validated at this point, and any value can be specified.
     *
     * @param mixed $data The field's data.
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->clearValidation();
    }

    /**
     * Retrieves the current data value for the field.
     *
     * @return mixed The current data value.
     */
    public function getData()
    {
        return $this->data;
    }
}
