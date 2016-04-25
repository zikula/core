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

use Zikula\UsersModule\Constant as UsersConstant;

/**
 * Contains and validates the data found on the Users module's new user form.
 */
class NewUserForm extends AbstractFormData
{
    /**
     * A validator to conditionally check the length of the password field.
     *
     * @var \Zikula\UsersModule\Controller\FormData\Validator\AbstractValidator
     */
    protected $passwordLengthValidator;

    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string                $formId         The id value to use for the form.
     * @param \Zikula_ServiceManager $serviceManager The current service manager instance.
     */
    public function __construct($formId, \Zikula_ServiceManager $serviceManager = null)
    {
        parent::__construct($formId, $serviceManager);

        $this->addField(new Field(
                $this,
                'uname',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringMinimumLength(
                $this->serviceManager,
                1,
                $this->__('A user name is required, and cannot be left blank.')))
            ->addValidator(new Validator\StringRegularExpression(
                $this->serviceManager,
                '/^'. UsersConstant::UNAME_VALIDATION_PATTERN .'$/uD',
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')))
            ->addValidator(new Validator\StringLowerCase(
                $this->serviceManager,
                $this->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')));

        $this->addField(new Field(
                $this,
                'setpass',
                false,
                false,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                'pass',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                'passagain',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                'sendpass',
                false,
                false,
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                'email',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringMinimumLength(
                $this->serviceManager,
                1,
                $this->__('An e-mail address is required, and cannot be left blank.')))
            ->addValidator(new Validator\FilterVar(
                $this->serviceManager,
                FILTER_VALIDATE_EMAIL,
                null,
                false,
                $this->__('The value entered does not appear to be a valid email address.')));

        $this->addField(new Field(
                $this,
                'emailagain',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $this->addField(new Field(
                $this,
                'usermustverify',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\BooleanType(
                $this->serviceManager,
                $this->__('The value must be a boolean.')));

        $this->addField(new Field(
                $this,
                'theme',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));

        $passwordMinimumLength = (int)$this->getVar(UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH);
        $this->passwordLengthValidator = new Validator\StringMinimumLength($this->serviceManager, $passwordMinimumLength,
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
}
