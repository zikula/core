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

namespace Zikula\UsersBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimezoneField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Nucleos\UserBundle\Model\UserInterface;
use Nucleos\UserBundle\Model\UserManager;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\Bundle\CoreBundle\Api\ApiInterface\LocaleApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Helper\ChoiceHelper;
use function Symfony\Component\Translation\t;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly LocaleApiInterface $localeApi,
        private readonly ChoiceHelper $choiceHelper
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function createEntity(string $entityFqcn): UserInterface
    {
        return $this->userManager->createUser();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(
                fn (?User $user, ?string $pageName) => $user ?? 'User'
            )
            ->setEntityLabelInPlural('Users')
            ->setPageTitle('index', '%entity_label_plural% list')
            ->setPageTitle('detail', fn (User $user) => $user)
            ->setPageTitle('edit', fn (User $user) => sprintf('Edit %s', $user))
            ->setDateFormat(DateTimeField::FORMAT_MEDIUM)
            ->setDateTimeFormat(DateTimeField::FORMAT_MEDIUM, DateTimeField::FORMAT_SHORT)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield 'id' => IdField::new('id', t('Id'))->hideOnForm()->setTextAlign('right')->setRequired(true);
        yield 'username' => TextField::new('username', t('User name'))->setRequired(true);
        yield 'email' => EmailField::new('email', t('Email address'))->setRequired(true);
        yield 'enabled' => BooleanField::new('enabled', t('Enabled'))->setTextAlign('center');
        yield 'plainPassword' => TextField::new('plainPassword', t('New password'))->hideOnIndex()->setHelp(t('used for resetting'));
        yield 'lastLogin' => DateTimeField::new('lastLogin', t('Last login'));
        yield 'confirmationToken' => TextField::new('confirmationToken', t('Confirmation token'))->onlyOnDetail();
        yield 'passwordRequestedAt' => DateTimeField::new('passwordRequestedAt', t('Password requested at'))->hideOnIndex();
        yield 'roles' => ChoiceField::new('roles', t('Roles'))->setRequired(true)->setChoices($this->choiceHelper->getRoles())->allowMultipleChoices();
        yield 'groups' => AssociationField::new('groups', t('Groups'));
        yield 'locale' => LocaleField::new('locale', t('Locale'))->includeOnly($this->localeApi->getSupportedLocales());
        yield 'timezone' => TimezoneField::new('timezone', t('Timezone'));

        // custom additions
        yield 'activated' => ChoiceField::new('activated', t('Activated'))->setRequired(true)->setChoices($this->choiceHelper->getActivatedValues())->renderExpanded();
        yield 'approvedDate' => DateTimeField::new('approvedDate', t('Approved at'))->hideOnIndex();
        yield 'approvedBy' => NumberField::new('approvedBy', t('Approved by'))->hideOnIndex(); // TODO replace by AssociationField
        yield 'registrationDate' => DateTimeField::new('registrationDate', t('Registered at'))->hideOnIndex();

        // yield 'attributes' => CollectionField... if eventually needed
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add(DateTimeFilter::new('lastLogin'))
            ->add(DateTimeFilter::new('passwordRequestedAt'))
            ->add(ChoiceFilter::new('roles')->setChoices($this->choiceHelper->getRoles())->canSelectMultiple())
            ->add(EntityFilter::new('groups')->canSelectMultiple())
            ->add(TextFilter::new('locale')->setFormTypeOptions(['value_type' => LocaleType::class, 'value_type_options.placeholder' => t('All')]))
            ->add(TextFilter::new('timezone')->setFormTypeOptions(['value_type' => TimezoneType::class, 'value_type_options.placeholder' => t('All')]))

            // custom additions
            ->add(ChoiceFilter::new('activated')->setChoices($this->choiceHelper->getActivatedValues())->canSelectMultiple())
            ->add(DateTimeFilter::new('approvedDate'))
            ->add('approvedBy')
            ->add(DateTimeFilter::new('registrationDate'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE)
        ;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userManager->updateUser($entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userManager->updateUser($entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userManager->deleteUser($entityInstance);
    }
}
