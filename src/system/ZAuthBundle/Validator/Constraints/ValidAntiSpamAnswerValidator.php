<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Translation\Extractor\Annotation\Ignore;

class ValidAntiSpamAnswerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator,
        private readonly ?string $antiSpamAnswer
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidAntiSpamAnswer) {
            throw new UnexpectedTypeException($constraint, ValidAntiSpamAnswer::class);
        }
        $correctAnswer = $this->antiSpamAnswer ?? '';
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new EqualTo([
                'value' => $correctAnswer,
                /** @Ignore */
                'message' => $this->translator->trans('You did not provide the correct answer for the security question. Try %answer%!', ['%answer%' => $correctAnswer], 'validators'),
            ])
        ]);
        if (0 < count($errors)) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }
    }
}