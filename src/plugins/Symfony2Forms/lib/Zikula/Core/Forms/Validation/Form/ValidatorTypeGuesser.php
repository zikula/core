<?php

namespace Zikula\Core\Forms\Validation\Form;

use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\Guess;
use SystemPlugin\Symfony2Forms\Validation\ValidationRegistry;
use SystemPlugin\Symfony2Forms\Validation\Constraints;

/**
 *
 */
class ValidatorTypeGuesser implements FormTypeGuesserInterface
{

    public function guessMaxLength($class, $property)
    {
        return null;
    }
    
    public function guessMinLength($class, $property)
    {
        return null;
    }
    
    public function guessRequired($class, $property)
    {
        $validator = ValidationRegistry::getClassValidator($class);
        
        if($validator) {
            $constraints = $validator->getFieldConstraints($property);
            
            if($constraints) {
                $required = false;
                
                foreach($constraints as $constraint) {
                    if($constraint instanceof Constraints\NotBlank
                            || $constraint instanceof Constraints\NotNull) {
                        $required = true;
                    }
                }
                
                if($required) {
                    return new ValueGuess(
                                    true, 
                                    Guess::HIGH_CONFIDENCE);
                } else {
                    return new ValueGuess(
                                    false, 
                                    Guess::LOW_CONFIDENCE);
                }
            }
        }
    }
    
    public function guessType($class, $property)
    {
        $validator = ValidationRegistry::getClassValidator($class);
        
        if($validator) {
            $constraints = $validator->getFieldConstraints($property);
            
            if($constraints) {
                $type = null;
                
                foreach($constraints as $constraint) {
                    if($constraint instanceof Constraints\Email) {
                        $type = 'email';
                    }
                }
                
                if($type != null) {
                    return new TypeGuess(
                                    $type, 
                                    array(), 
                                    Guess::HIGH_CONFIDENCE);
                } else {
                    return null;
                }
            }
        }
    }
}

