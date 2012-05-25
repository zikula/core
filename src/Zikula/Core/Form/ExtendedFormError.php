<?php

namespace Zikula\Core\Form;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 *
 */
class ExtendedFormError extends FormError
{
    private $propertypath;

    public function __construct($propertypath, $messageTemplate, array $messageParameters = array())
    {
        parent::__construct($messageTemplate, $messageParameters);
        $this->propertypath = $propertypath;
    }

    public function getPropertypath()
    {
        return $this->propertypath;
    }
}
