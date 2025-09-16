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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Nucleos\UserBundle\Model\GroupInterface;
use Nucleos\UserBundle\Model\GroupManager;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Zikula\UsersBundle\Entity\Group;
use Zikula\UsersBundle\Helper\ChoiceHelper;
use function Symfony\Component\Translation\t;

#[IsGranted('ROLE_ADMIN')]
class GroupCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly GroupManager $groupManager,
        private readonly ChoiceHelper $choiceHelper
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Group::class;
    }

    public function createEntity(string $entityFqcn): GroupInterface
    {
        return $this->groupManager->createGroup('new group');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(
                fn (?Group $group, ?string $pageName) => $group ?? 'Group'
            )
            ->setEntityLabelInPlural('Groups')
            ->setPageTitle('index', '%entity_label_plural% list')
            ->setPageTitle('detail', fn (Group $group) => $group)
            ->setPageTitle('edit', fn (Group $group) => sprintf('Edit %s', $group))
            ->addFormTheme('@ZikulaTheme/Form/bootstrap_4_zikula_admin_layout.html.twig')
            ->addFormTheme('@ZikulaTheme/Form/form_div_layout.html.twig')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield 'id' => IdField::new('id', t('Id'))->hideOnForm()->setTextAlign('right')->setRequired(true);
        yield 'name' => TextField::new('name', t('Group name'))->setRequired(true);
        yield 'roles' => ChoiceField::new('roles', t('Roles'))->setRequired(false)->setChoices($this->choiceHelper->getRoles())->allowMultipleChoices();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('name')
            ->add(ChoiceFilter::new('roles')->setChoices($this->choiceHelper->getRoles())->canSelectMultiple())
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
        $this->groupManager->updateGroup($entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->groupManager->updateGroup($entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->groupManager->deleteGroup($entityInstance);
    }
}
