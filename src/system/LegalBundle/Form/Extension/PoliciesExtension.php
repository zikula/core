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

namespace Zikula\LegalBundle\Form\Extension;

use Nucleos\ProfileBundle\Form\Type\ProfileFormType;
use Nucleos\ProfileBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Zikula\LegalBundle\Form\Type\AcceptPoliciesType;
use Zikula\LegalBundle\Helper\AcceptPoliciesHelper;
use Zikula\UsersBundle\Entity\User;

class PoliciesExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly AcceptPoliciesHelper $acceptPoliciesHelper
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $policies = $this->acceptPoliciesHelper->getActivePolicies();
        foreach ($policies as $policyName => $isEnabled) {
            if (!$isEnabled) {
                continue;
            }
            $builder->add($policyName . 'Accepted', CheckboxType::class, [
                'label' => $policyName,
                'getter' => function (User $user, FormInterface $form): bool {
                    $getter = 'get' . ucwords((string) $form->getPropertyPath());

                    return null !== $user->$getter();
                },
                'setter' => function (User $user, bool $state, FormInterface $form): void {
                    $setter = 'set' . ucwords((string) $form->getPropertyPath());
                    $nowUTC = new \DateTime('now', new \DateTimeZone('UTC'));
                    $user->$setter($state ? $nowUTC : null);
                },
            ]);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProfileFormType::class, RegistrationFormType::class, AcceptPoliciesType::class];
    }
}
