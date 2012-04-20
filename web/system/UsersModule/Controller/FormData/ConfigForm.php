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

namespace UsersModule\Controller\FormData;

use Zikula\Component\DependencyInjection\ContainerBuilder;
use UsersModule\Constants as UsersConstant;
use UsersModule\Helper\HashMethodListHelper;
    
/**
 * Contains and validates the data found on the Users module's configration form.
 */
class ConfigForm extends AbstractFormData
{
    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string         $formId         The id value to use for the form.
     * @param ContainerBuilder $container The current service manager instance.
     */
    public function __construct($formId, ContainerBuilder $container = null)
    {
        parent::__construct($formId, $container);

        $modVars = $this->getVars();

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS,
                $modVars[UsersConstant::MODVAR_ACCOUNT_DISPLAY_GRAPHICS],
                UsersConstant::DEFAULT_ACCOUNT_DISPLAY_GRAPHICS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_PAGE,
                $modVars[UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_PAGE],
                UsersConstant::DEFAULT_ACCOUNT_ITEMS_PER_PAGE,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                    $this->container,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                    $this->container,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_ROW,
                $modVars[UsersConstant::MODVAR_ACCOUNT_ITEMS_PER_ROW],
                UsersConstant::DEFAULT_ACCOUNT_ITEMS_PER_ROW,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                    $this->container,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                    $this->container,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH,
                $modVars[UsersConstant::MODVAR_ACCOUNT_PAGE_IMAGE_PATH],
                UsersConstant::DEFAULT_ACCOUNT_PAGE_IMAGE_PATH,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                    $this->container,
                    $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME,
                $modVars[UsersConstant::MODVAR_ANONYMOUS_DISPLAY_NAME],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                    $this->container,
                    $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_AVATAR_IMAGE_PATH,
                $modVars[UsersConstant::MODVAR_AVATAR_IMAGE_PATH],
                UsersConstant::DEFAULT_AVATAR_IMAGE_PATH,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                    $this->container,
                    $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL,
                $modVars[UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL],
                UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                    $this->container,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                    $this->container,
                    0,
                    $this->__('The value must be an integer greater than or equal to 0.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD,
                $modVars[UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD],
                UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                    $this->container,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                    $this->container,
                    0,
                    $this->__('The value must be an integer greater than or equal to 0.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_GRAVATARS_ENABLED,
                $modVars[UsersConstant::MODVAR_GRAVATARS_ENABLED],
                UsersConstant::DEFAULT_GRAVATARS_ENABLED,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_GRAVATAR_IMAGE,
                $modVars[UsersConstant::MODVAR_GRAVATAR_IMAGE],
                UsersConstant::DEFAULT_GRAVATAR_IMAGE,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $hashMethods = new HashMethodListHelper();
        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_HASH_METHOD,
                $modVars[UsersConstant::MODVAR_HASH_METHOD],
                UsersConstant::DEFAULT_HASH_METHOD,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringInSet(
                $this->container,
                $hashMethods->getHashMethods(),
                $this->__('The value must be one of the following: '. implode(', ', $hashMethods->getHashMethods()) .'.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_ITEMS_PER_PAGE,
                $modVars[UsersConstant::MODVAR_ITEMS_PER_PAGE],
                UsersConstant::DEFAULT_ITEMS_PER_PAGE,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                    $this->container,
                    $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                    $this->container,
                    1,
                    $this->__('The value must be an integer greater than or equal to 1.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS,
                $modVars[UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS],
                UsersConstant::DEFAULT_LOGIN_DISPLAY_APPROVAL_STATUS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS,
                $modVars[UsersConstant::MODVAR_LOGIN_DISPLAY_DELETE_STATUS],
                UsersConstant::DEFAULT_LOGIN_DISPLAY_DELETE_STATUS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS,
                $modVars[UsersConstant::MODVAR_LOGIN_DISPLAY_INACTIVE_STATUS],
                UsersConstant::DEFAULT_LOGIN_DISPLAY_INACTIVE_STATUS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS,
                $modVars[UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS],
                UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_METHOD,
                $modVars[UsersConstant::MODVAR_LOGIN_METHOD],
                UsersConstant::DEFAULT_LOGIN_METHOD,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be a integer.')))
            ->addValidator(new Validator\IntegerNumericInSet(
                $this->container,
                array(
                    UsersConstant::LOGIN_METHOD_UNAME,
                    UsersConstant::LOGIN_METHOD_EMAIL,
                    UsersConstant::LOGIN_METHOD_ANY
                ),
                $this->__('The value must be a valid login method constant.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_LOGIN_WCAG_COMPLIANT,
                $modVars[UsersConstant::MODVAR_LOGIN_WCAG_COMPLIANT],
                UsersConstant::DEFAULT_LOGIN_WCAG_COMPLIANT,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_MANAGE_EMAIL_ADDRESS,
                $modVars[UsersConstant::MODVAR_MANAGE_EMAIL_ADDRESS],
                UsersConstant::DEFAULT_MANAGE_EMAIL_ADDRESS,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH,
                $modVars[UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH],
                UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be an integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                $this->container,
                3,
                $this->__('The value must be an integer greater than 3.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED,
                $modVars[UsersConstant::MODVAR_PASSWORD_STRENGTH_METER_ENABLED],
                UsersConstant::DEFAULT_PASSWORD_STRENGTH_METER_ENABLED,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ADMIN_NOTIFICATION_EMAIL],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^(?:'.UsersConstant::EMAIL_VALIDATION_PATTERN.')?$/Ui',
                $this->__('The value does not appear to be a properly formatted e-mail address.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED,
                $modVars[UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED],
                UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE,
                $modVars[UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE],
                UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be a integer.')))
            ->addValidator(new Validator\IntegerNumericInSet(
                $this->container,
                array(
                    UsersConstant::APPROVAL_BEFORE,
                    UsersConstant::APPROVAL_AFTER,
                    UsersConstant::APPROVAL_ANY
                ),
                $this->__('The value must be a valid approval sequence constant.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN,
                $modVars[UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN],
                UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON,
                $modVars[UsersConstant::MODVAR_REGISTRATION_DISABLED_REASON],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ENABLED,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ENABLED],
                UsersConstant::DEFAULT_REGISTRATION_ENABLED,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_EXPIRE_DAYS_REGISTRATION,
                $modVars[UsersConstant::MODVAR_EXPIRE_DAYS_REGISTRATION],
                UsersConstant::DEFAULT_EXPIRE_DAYS_REGISTRATION,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be a integer.')))
            ->addValidator(new Validator\IntegerNumericMinimumValue(
                $this->container,
                0,
                $this->__('The value must be a integer greater than or equal to 0.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^(?:[^\s,][^,]*(?:,\s?[^\s,][^,]*)*)?$/',
                $this->__('The contents of this field does not appear to be a valid comma separated list. The list should consist of one or more string values separated by commas. For example: \'first example, 2nd example, tertiary example\' (the quotes should not appear in the list). One optional space following the comma is ignored for readability. Any other spaces (those appearing before the comma, and any additional spaces beyond the single optional space) will be considered to be part of the string value. Commas cannot be part of the string value. Empty values (two commas together, or separated only by a space) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^(?:'. UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN .'(?:\s*,\s*'. UsersConstant::EMAIL_DOMAIN_VALIDATION_PATTERN .')*)?$/Ui',
                $this->__('The contents of this field does not appear to be a valid list of e-mail address domains. The list should consist of one or more e-mail address domains (the part after the \'@\'), separated by commas. For example: \'gmail.com, example.org, acme.co.uk\' (the quotes should not appear in the list). Do not include the \'@\' itself. Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES,
                $modVars[UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES],
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^(?:'. UsersConstant::UNAME_VALIDATION_PATTERN .'(?:\s*,\s*'. UsersConstant::UNAME_VALIDATION_PATTERN .')*)?$/uD',
                $this->__('The value provided does not appear to be a valid list of user names. The list should consist of one or more user names made up of lowercase letters, numbers, underscores, periods, or dashes. Separate each user name with a comma. For example: \'root, administrator, superuser\' (the quotes should not appear in the list). Spaces surrounding commas are ignored, however extra spaces before or after the list are not and will result in an error. Empty values (two commas together, or separated only by spaces) are not allowed. The list is optional, and if no values are to be defined then the list should be completely empty (no extra spaces, commas, or any other characters).')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE,
                $modVars[UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE],
                UsersConstant::DEFAULT_REGISTRATION_VERIFICATION_MODE,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be a integer.')))
            ->addValidator(new Validator\IntegerNumericInSet(
                $this->container,
                array(
                    UsersConstant::VERIFY_NO,
                    UsersConstant::VERIFY_USERPWD
                ),
                $this->__('The value must be a valid verification mode constant.')));

        $this->addField(new Field(
                $this,
                UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL,
                $modVars[UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL],
                UsersConstant::DEFAULT_REQUIRE_UNIQUE_EMAIL,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->container,
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

        $antiSpamAnswerField = $this->getField(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER);

        if (!$antiSpamAnswerField->hasErrorMessage()) {
            $antiSpamAnswer = $antiSpamAnswerField->getData();

            $antiSpamQuestionField = $this->getField(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION);
            $antiSpamQuestion = $antiSpamQuestionField->getData();

            if (isset($antiSpamQuestion) && !empty($antiSpamQuestion) && (!isset($antiSpamAnswer) || empty($antiSpamAnswer))) {
                $valid = false;
                $antiSpamAnswerField->setErrorMessage($this->__('If a spam protection question is provided, then a spam protection answer must also be provided.'));
            }
        }

        return $valid;
    }
}
