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
 * Contains and validates the data found on the Users module's user registration form.
 */
class RegistrationForm extends AbstractFormData
{
    /**
     * Create a new instance of the form data container, intializing the fields and validators.
     *
     * @param string                 $formId                       The id value to use for the form.
     * @param \Zikula_ServiceManager $serviceManager               The current service manager instance.
     * @param bool                   $passwordReminderNotMandatory Set it to true to remove the mandatory validation of the password reminder. Overwrites modvar.
     */
    public function __construct($formId, \Zikula_ServiceManager $serviceManager = null, $passwordReminderNotMandatory = false)
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

        $passwordMinimumLength = (int)$this->getVar(UsersConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, UsersConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH);
        $this->addField(new Field(
                $this,
                'pass',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(false)
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')))
            ->addValidator(new Validator\StringMinimumLength(
                $this->serviceManager,
                $passwordMinimumLength,
                $this->__f('Passwords must be at least %1$d characters in length.', array($passwordMinimumLength))));

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

        if ((bool)$this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) {
            $passReminderField = new Field(
                $this,
                'passreminder',
                false,
                false,
                $this->serviceManager
            );

            $passReminderField->setNullAllowed(false)
                ->addValidator(new Validator\StringType(
                    $this->serviceManager,
                    $this->__('The value must be a string.')
            ));

            if (((bool)$this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY, UsersConstant::DEFAULT_PASSWORD_REMINDER_MANDATORY)) && (!$passwordReminderNotMandatory)) {
                $passReminderField->addValidator(new Validator\StringMinimumLength(
                    $this->serviceManager,
                    1,
                    $this->__('A password reminder is required, and cannot be left blank.')
                ));
            }

            $this->addField($passReminderField);
        }

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

        $antispamQuestion = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, '');
        $this->addField(new Field(
                $this,
                'antispamanswer',
                '',
                '',
                $this->serviceManager))
            ->setNullAllowed(empty($antispamQuestion))
            ->addValidator(new Validator\StringType(
                $this->serviceManager,
                $this->__('The value must be a string.')));
    }

    /**
     * Validate the entire form data set against each field's validators, and additionally validate interdependent fields.
     *
     * @return boolean True if each of the container's fields validates, and additionally if the dependencies validate; otherwise false.
     */
    public function isValid()
    {
        $valid = parent::isValid();

        $passwordField = $this->getField('pass');
        $passwordAgainField = $this->getField('passagain');

        if (!$passwordField->hasErrorMessage() && !$passwordAgainField->hasErrorMessage()) {
            $password = $passwordField->getData();
            $passwordAgain = $passwordAgainField->getData();

            if ($passwordAgain != $password) {
                $valid = false;
                $passwordAgainField->setErrorMessage($this->__('The value entered does not match the password entered in the password field.'));
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

        $antispamQuestion = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION, '');
        if (!empty($antispamQuestion)) {
            $antispamAnswer = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ANTISPAM_ANSWER, '');

            $antiSpamAnswerField = $this->getField('antispamanswer');
            if ($antiSpamAnswerField->getData() != $antispamAnswer) {
                $valid = false;
                $antiSpamAnswerField->setErrorMessage($this->__f('You did not provide the correct answer for the security question. The correct answer is \'%1$s\' (not including the quotes).', array($antispamAnswer)));
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
            'uname'         => $this->getField('uname')->getData(),
            'pass'          => $this->getField('pass')->getData(),
            'passreminder'  => (((bool)$this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED, UsersConstant::DEFAULT_PASSWORD_REMINDER_ENABLED)) ? $this->getField('passreminder')->getData() : ''),
            'email'         => $this->getField('email')->getData(),
        );

        if ($includeLoginInfo) {
            $user['pass'] = $this->getField('setpass')->getData() ? $this->getField('pass')->getData() : '';
            $user['passreminder'] = $this->getField('setpass')->getData() ? $this->__('Password set by administrator.') : '';
        }

        return $user;
    }
}
