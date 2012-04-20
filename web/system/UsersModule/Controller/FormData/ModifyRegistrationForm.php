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

/**
 * Contains and validates the data found on the Users module's modify registration form.
 */
class ModifyRegistrationForm extends AbstractFormData
{
    /**
     * A validator to conditionally check the length of the password field.
     *
     * @var Validator\AbstractValidator
     */
    protected $passwordLengthValidator;

    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string                $formId         The id value to use for the form.
     * @param ContainerBuilder $container The current service manager instance.
     */
    public function __construct($formId, ContainerBuilder $container = null)
    {
        parent::__construct($formId, $container);

        $this->addField(new Field(
                $this,
                'uid',
                0,
                0,
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\IntegerNumericType(
                $this->container,
                $this->__('The value must be an integer.')));

        $this->addField(new Field(
                $this,
                'uname',
                '',
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringMinimumLength(
                $this->container,
                1,
                $this->__('A user name is required, and cannot be left blank.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^'. UsersConstant::UNAME_VALIDATION_PATTERN .'$/uD',
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')))
            ->addValidator(new Validator\StringLowercase(
                $this->container,
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')));

        $this->addField(new Field(
                $this,
                'email',
                '',
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringMinimumLength(
                $this->container,
                1,
                $this->__('An e-mail address is required, and cannot be left blank.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->container,
                '/^'. UsersConstant::EMAIL_VALIDATION_PATTERN .'$/Di',
                $this->__('The value entered does not appear to be a valid e-mail address.')));

        $this->addField(new Field(
                $this,
                'emailagain',
                '',
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                'theme',
                '',
                '',
                $this->container))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->container,
                $this->__('The value must be a string.')));

        $passwordMinimumLength = (int)$this->getVar(UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH);
        $this->passwordLengthValidator = new Validator\StringMinimumLength($this->container, $passwordMinimumLength,
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
     * @return array An array suitable for use as a user array.
     */
    public function toUserArray()
    {
        $user = array(
            'uid'       => $this->getField('uid')->getData(),
            'uname'     => $this->getField('uname')->getData(),
            'email'     => $this->getField('email')->getData(),
            'theme'     => $this->getField('theme')->getData(),
        );

        return $user;
    }
}
