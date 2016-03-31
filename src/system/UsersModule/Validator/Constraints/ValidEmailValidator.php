<?php

namespace Zikula\UsersModule\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\Repository\UserRepository;

class ValidEmailValidator extends ConstraintValidator
{
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var UserRepository
     */
    private $entityManager;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * ValidUnameValidator constructor.
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     * @param EntityManager $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new Email()
        ]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                 // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure legal domain
        $illegalDomains = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ILLEGAL_DOMAINS, '');
        $pattern = array('/^((\s*,)*\s*)+/D', '/\b(\s*,\s*)+\b/D', '/((\s*,)*\s*)+$/D');
        $replace = array('', '|', '');
        $illegalDomains = preg_replace($pattern, $replace, preg_quote($illegalDomains, '/'));
        if (!empty($illegalDomains)) {
            $emailDomain = strstr($value, '@');
            if (preg_match("/@({$illegalDomains})/iD", $emailDomain)) {
                $this->context->buildViolation($this->translator->__('Sorry! The domain of the e-mail address you specified is banned.'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }

        // ensure unique
        if ($this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REQUIRE_UNIQUE_EMAIL, false)) {
            $query = $this->entityManager->createQueryBuilder()
                ->select('count(u.uid)')
                ->from('ZikulaUsersModule:UserEntity', 'u')
                ->where('u.email = :email')
                ->setParameter('email', $value)
                ->getQuery();
            $uCount = (int)$query->getSingleScalarResult();

            $query = $this->entityManager->createQueryBuilder()
                ->select('count(v.uid)')
                ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                ->where('v.newemail = :email')
                ->andWhere('v.changetype = :chgtype')
                ->setParameter('email', $value)
                ->setParameter('chgtype', UsersConstant::VERIFYCHGTYPE_EMAIL)
                ->getQuery();
            $vCount = (int)$query->getSingleScalarResult();

            if ($uCount + $vCount > 0) {
                $this->context->buildViolation($this->translator->__('The email address you entered has already been registered.'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }
    }
}
