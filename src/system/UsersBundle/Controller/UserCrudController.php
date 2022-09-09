<?php

namespace Zikula\UsersBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Zikula\UsersBundle\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setPageTitle('index', '%entity_label_plural% list')
            ->setPageTitle('detail', fn (User $user) => $user)
            ->setPageTitle('edit', fn (User $user) => sprintf('Editing <strong>%s</strong>', $user))
//            ->setDateFormat('...')
            // ...
        ;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
