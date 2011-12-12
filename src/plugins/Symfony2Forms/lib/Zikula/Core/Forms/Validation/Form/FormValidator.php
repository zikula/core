<?php

namespace Zikula\Core\Forms\Validation\Form;

use Symfony\Component\Form\FormValidatorInterface;
use Symfony\Component\Form\FormInterface;
use SystemPlugin\Symfony2Forms\ExtendedFormError;
use SystemPlugin\Symfony2Forms\Validation\Errors;

/**
 *
 */
class FormValidator implements FormValidatorInterface
{
    private $validators;
    
    public function __construct($types)
    {
        $this->validators = array();
        
        foreach($types as $type) {
            $cls = new \ReflectionClass($type);
            if($cls->hasMethod('getValidator')) {
                $this->validators[] = $cls->getMethod('getValidator')->invoke($type);
            }
        }
    }
    
    public function validate(FormInterface $form)
    {
        if($form->isRoot()) {
            $value = $form->getData();

            if(is_array($value)) {
                foreach($this->validators as $validator) {
                    $errors = $validator->validateArray($value);
                    if($errors) {
                        $this->addToForm($form, $errors);
                    }
                }
            }
        }
    }
    
    private function addToForm(FormInterface $form, Errors $errors) 
    {
        foreach($errors->getErrors() as $error) {
            $formError = new ExtendedFormError($form->getAttribute('property_path'), $error);
            $form->addError($formError);
        }
        
        foreach($errors->getFields() as $field => $fieldErrors) {
            $this->addToForm($form->get($field), $fieldErrors);
        }
    }
}
