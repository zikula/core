<?php

namespace Zikula\PermissionsBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Zikula\PermissionsBundle\Entity\PermissionEntity;

class PermissionEntityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PermissionEntity::class;
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
