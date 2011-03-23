<?php
/**
 * Copyright 2011 Zikula Foundation.
 * 
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 * 
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * 
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Contains and validates the data found on the Users module's modify user form.
 */
class Users_Controller_FormData_ModifyUserForm extends Users_Controller_FormData_AbstractFormData
{
    /**
     * A validator to conditionally check the length of the password field.
     *
     * @var Users_Controller_Data_Validator
     */
    protected $passwordLengthValidator;
    
    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string                $formId         The id value to use for the form.
     * @param Zikula_ServiceManager $serviceManager The current service manager instance.
     */
    public function __construct($formId, Zikula_ServiceManager $serviceManager = null)
    {
        parent::__construct($formId, $serviceManager);
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'uid',
                0,
                0,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be an integer.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'uname',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringMinimumLength(
                $this->serviceManager,
                1,
                $this->__('A user name is required, and cannot be left blank.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^'. Users_Constant::UNAME_VALIDATION_PATTERN .'$/uD',
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringLowercase(
                $this->serviceManager,
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'email',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringMinimumLength(
                $this->serviceManager,
                1,
                $this->__('An e-mail address is required, and cannot be left blank.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^'. Users_Constant::EMAIL_VALIDATION_PATTERN .'$/Di',
                $this->__('The value entered does not appear to be a valid e-mail address.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'emailagain',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'activated',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericInSet(
                $this->serviceManager,
                array(Users_Constant::ACTIVATED_INACTIVE, Users_Constant::ACTIVATED_ACTIVE, Users_Constant::ACTIVATED_PENDING_DELETE),
                $this->__('The value must be an integer.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'theme',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'setpass',
                false,
                false,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'pass',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));
        
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                'passagain',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));
        
        $passwordMinimumLength = (int)$this->getVar(Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH, Users_Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH);
        $this->passwordLengthValidator = new Users_Controller_FormData_Validator_StringMinimumLength($this->serviceManager, $passwordMinimumLength, 
                $this->__f('Passwords must be at least %1$d characters in length.', array($passwordMinimumLength)));
        
    }
    
    /**
     * Validate the entire form data set against each field's validators, and additionally validate interdependent fields.
     *
     * @return boolean True if each of the container's fields validates, and additionally if the dependencies validate; otherwise false.
     */
    public function isValid()
    {
        $valid = parent::isValid();
        
        $setPasswordField = $this->getField('setpass');
        if (!$setPasswordField->hasErrorMessage() && $setPasswordField->getData()) {
            $passwordField = $this->getField('pass');
            
            if (!$this->passwordLengthValidator->isValid($passwordField->getData())) {
                $passwordField->setErrorMessage($this->passwordLengthValidator->getErrorMessage());
            }
            
            $passwordAgainField = $this->getField('passagain');

            if (!$passwordField->hasErrorMessage() && !$passwordAgainField->hasErrorMessage()) {
                $password = $passwordField->getData();
                $passwordAgain = $passwordAgainField->getData();

                if ($passwordAgain != $password) {
                    $valid = false;
                    $passwordAgainField->setErrorMessage($this->__('The value entered does not match the password entered in the password field.'));
                }
            }
        }
        
        $emailField = $this->getField('email');
        if (!$emailField->hasErrorMessage()) {
            $emailAgainField = $this->getField('emailagain');
            
            $email = $emailField->getData();
            $emailAgain = $emailAgainField->getData();
            
            if ($email != $emailAgain) {
                $valid = false;
                $emailAgainField->setErrorMessage($this->__('The value entered does not match the e-mail address entered in the e-mail address field.'));
            }
        }
        
        return $valid;
    }
    
    /**
     * Convert the data in the form data container to an array suitable for use with functions expecting a user array.
     * 
     * @param boolean $includeLoginInfo True to include the password and password reminder, false to exclude them.
     *
     * @return array An array suitable for use as a user array.
     */
    public function toUserArray($includeLoginInfo = false)
    {
        $user = array(
            'uid'       => $this->getField('uid')->getData(),
            'uname'     => $this->getField('uname')->getData(),
            'email'     => $this->getField('email')->getData(),
            'activated' => $this->getField('activated')->getData(),
            'theme'     => $this->getField('theme')->getData(),
        );
        
        if ($includeLoginInfo) {
            $user['pass'] = $this->getField('setpass')->getData() ? $this->getField('pass')->getData() : '';
            $user['passreminder'] = $this->getField('setpass')->getData() ? $this->__('Password set by administrator.') : '';
        }
        
        return $user;
    }
}
