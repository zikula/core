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
 * Contains and validates the data found on the Users module's configration form.
 */
class Users_Controller_FormData_ConfigForm extends Users_Controller_FormData_AbstractFormData
{
    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string                $formId         The id value to use for the form.
     * @param Zikula_ServiceManager $serviceManager The current service manager instance.
     */
    public function __construct($formId, Zikula_ServiceManager $serviceManager = null)
    {
        parent::__construct($formId, $serviceManager);

        $modVars = $this->getVars();

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS,
                $modVars[Users_Constant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS],
                Users_Constant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE,
                $modVars[Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_PAGE],
                Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                    $this->serviceManager,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                    $this->serviceManager,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW,
                $modVars[Users_Constant::MODVAR_ACCOUNT_ITEMS_PER_ROW],
                Users_Constant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                    $this->serviceManager,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                    $this->serviceManager,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH,
                $modVars[Users_Constant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH],
                Users_Constant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                    $this->serviceManager,
                    $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME,
                $modVars[Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                    $this->serviceManager,
                    $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_AVATAR_IMAGE_PATH,
                $modVars[Users_Constant::MODVAR_AVATAR_IMAGE_PATH],
                Users_Constant::DEFAULT_AVATAR_IMAGE_PATH,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                    $this->serviceManager,
                    $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL,
                $modVars[Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL],
                Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                    $this->serviceManager,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                    $this->serviceManager,
                    0,
                    $this->__('The value must be an integer greater than or equal to 0.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD,
                $modVars[Users_Constant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD],
                Users_Constant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                    $this->serviceManager,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                    $this->serviceManager,
                    0,
                    $this->__('The value must be an integer greater than or equal to 0.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_GRAVATARS_ENABLED,
                $modVars[Users_Constant::MODVAR_GRAVATARS_ENABLED],
                Users_Constant::DEFAULT_GRAVATARS_ENABLED,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_GRAVATAR_IMAGE,
                $modVars[Users_Constant::MODVAR_GRAVATAR_IMAGE],
                Users_Constant::DEFAULT_GRAVATAR_IMAGE,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $hashMethods = new Users_Helper_HashMethodList();
        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_HASH_METHOD,
                $modVars[Users_Constant::MODVAR_HASH_METHOD],
                Users_Constant::DEFAULT_HASH_METHOD,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringInSet(
                $this->serviceManager,
                $hashMethods->getHashMethods(),
                $this->__('The value must be one of the following: '. implode(', ', $hashMethods->getHashMethods()) .'.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_ITEMS_PER_PAGE,
                $modVars[Users_Constant::MODVAR_ITEMS_PER_PAGE],
                Users_Constant::DEFAULT_ITEMS_PER_PAGE,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                    $this->serviceManager,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                    $this->serviceManager,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS,
                $modVars[Users_Constant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS],
                Users_Constant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS,
                $modVars[Users_Constant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS],
                Users_Constant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS,
                $modVars[Users_Constant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS],
                Users_Constant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS,
                $modVars[Users_Constant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS],
                Users_Constant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_METHOD,
                $modVars[Users_Constant::MODVAR_LOGIN_METHOD],
                Users_Constant::DEFAULT_LOGIN_METHOD,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be a integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericInSet(
                $this->serviceManager,
                array(
                    Users_Constant::LOGIN_METHOD_UNAME,
                    Users_Constant::LOGIN_METHOD_EMAIL,
                    Users_Constant::LOGIN_METHOD_ANY
                ),
                $this->__('The value must be a valid login method constant.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_LOGIN_WCAG_COMPLIANT,
                $modVars[Users_Constant::MODVAR_LOGIN_WCAG_COMPLIANT],
                Users_Constant::DEFAULT_LOGIN_WCAG_COMPLIANT,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_MANAGE_EMAIL_ADDRESS,
                $modVars[Users_Constant::MODVAR_MANAGE_EMAIL_ADDRESS],
                Users_Constant::DEFAULT_MANAGE_EMAIL_ADDRESS,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH,
                $modVars[Users_Constant::MODVAR_PASSWORD_MINIMUM_LENGTH],
                Users_Constant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be an integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                $this->serviceManager,
                3,
                $this->__('The value must be an integer greater than 3.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED,
                $modVars[Users_Constant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED],
                Users_Constant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^(?:'.Users_Constant::EMAIL_VALIDATION_PATTERN.')?$/Ui',
                $this->__('The value does not appear to be a properly formatted e-mail address.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED,
                $modVars[Users_Constant::MODVAR_REGISTRATION_APPROVAL_REQUIRED],
                Users_Constant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE,
                $modVars[Users_Constant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE],
                Users_Constant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be a integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericInSet(
                $this->serviceManager,
                array(
                    Users_Constant::APPROVAL_BEFORE,
                    Users_Constant::APPROVAL_AFTER,
                    Users_Constant::APPROVAL_ANY
                ),
                $this->__('The value must be a valid approval sequence constant.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN,
                $modVars[Users_Constant::MODVAR_REGISTRATION_AUTO_LOGIN],
                Users_Constant::DEFAULT_REGISTRATION_AUTO_LOGIN,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON,
                $modVars[Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ENABLED,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ENABLED],
                Users_Constant::DEFAULT_REGISTRATION_ENABLED,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_EXPIRE_DAYS_REGISTRATION,
                $modVars[Users_Constant::MODVAR_EXPIRE_DAYS_REGISTRATION],
                Users_Constant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be a integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericMinimumValue(
                $this->serviceManager,
                0,
                $this->__('The value must be a integer greater than or equal to 0.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ILLEGAL_AGENTS],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^(?:[^\s,][^,]*(?:,\s?[^\s,][^,]*)*)?$/',
                $this->__('The contents of this field does not appear to be a valid comma separated list. The list should consist of one or more string values separated by commas. For example: \'first example, 2nd example, tertiary example\' (the quotes should not appear in the list). One optional space following the comma is ignored for readability. Any other spaces (those appearing before the comma, and any additional spaces beyond the single optional space) will be considered to be part of the string value. Commas cannot be part of the string value. Empty values (two commas together, or separated only by a space) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^(?:'. Users_Constant::EMAIL_DOMAIN_VALIDATION_PATTERN .'(?:\s*,\s*'. Users_Constant::EMAIL_DOMAIN_VALIDATION_PATTERN .')*)?$/Ui',
                $this->__('The contents of this field does not appear to be a valid list of e-mail address domains. The list should consist of one or more e-mail address domains (the part after the \'@\'), separated by commas. For example: \'gmail.com, example.org, acme.co.uk\' (the quotes should not appear in the list). Do not include the \'@\' itself. Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES,
                $modVars[Users_Constant::MODVAR_REGISTRATION_ILLEGAL_UNAMES],
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Users_Controller_FormData_Validator_StringRegularExpression(
                $this->serviceManager,
                '/^(?:'. Users_Constant::UNAME_VALIDATION_PATTERN .'(?:\s*,\s*'. Users_Constant::UNAME_VALIDATION_PATTERN .')*)?$/uD',
                $this->__('The value provided does not appear to be a valid list of user names. The list should consist of one or more user names made up of lowercase letters, numbers, underscores, periods, or dashes. Separate each user name with a comma. For example: \'root, administrator, superuser\' (the quotes should not appear in the list). Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE,
                $modVars[Users_Constant::MODVAR_REGISTRATION_VERIFICATION_MODE],
                Users_Constant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericType(
                $this->serviceManager,
                $this->__('The value must be a integer.')))
            ->addValidator(new Users_Controller_FormData_Validator_IntegerNumericInSet(
                $this->serviceManager,
                array(
                    Users_Constant::VERIFY_NO,
                    Users_Constant::VERIFY_USERPWD
                ),
                $this->__('The value must be a valid verification mode constant.')));

        $this->addField(new Users_Controller_FormData_Field(
                $this,
                Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL,
                $modVars[Users_Constant::MODVAR_REQUIRE_UNIQUE_EMAIL],
                Users_Constant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Users_Controller_FormData_Validator_BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));
    }

    /**
     * Validate the entire form data set against each field's validators, and additionally validate interdependent fields.
     *
     * @return boolean True if each of the container's fields validates, and additionally if the dependencies validate; otherwise false.
     */
    public function isValid()
    {
        $valid = parent::isValid();

        $antiSpamAnswerField = $this->getField(Users_Constant::MODVAR_REGISTRATION_ANTISPAM_ANSWER);

        if (!$antiSpamAnswerField->hasErrorMessage()) {
            $antiSpamAnswer = $antiSpamAnswerField->getData();

            $antiSpamQuestionField = $this->getField(Users_Constant::MODVAR_REGISTRATION_ANTISPAM_QUESTION);
            $antiSpamQuestion = $antiSpamQuestionField->getData();

            if (isset($antiSpamQuestion) && !empty($antiSpamQuestion) && (!isset($antiSpamAnswer) || empty($antiSpamAnswer))) {
                $valid = false;
                $antiSpamAnswerField->setErrorMessage($this->__('If a spam protection question is provided, then a spam protection answer must also be provided.'));
            }
        }

        return $valid;
    }
}
