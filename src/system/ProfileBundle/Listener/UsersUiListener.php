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

namespace Zikula\ProfileBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Zikula\Bundle\FormExtensionBundle\Event\FormTypeChoiceEvent;
use Zikula\ProfileBundle\Form\ProfileTypeFactory;
use Zikula\ProfileBundle\Form\Type\AvatarType;
use Zikula\ProfileBundle\Helper\UploadHelper;
use Zikula\ProfileBundle\ProfileConstant;
use Zikula\ProfileBundle\Repository\PropertyRepositoryInterface;
use Zikula\UsersBundle\Constant;
use Zikula\UsersBundle\Event\EditUserFormPostCreatedEvent;
use Zikula\UsersBundle\Event\EditUserFormPostValidatedEvent;
use Zikula\UsersBundle\Event\UserAccountDisplayEvent;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

/**
 * Hook-like event handlers for basic profile data.
 */
class UsersUiListener implements EventSubscriberInterface
{
    /**
     * The area name that this handler processes.
     */
    public const EVENT_KEY = 'module.profile.users_ui_handler';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PropertyRepositoryInterface $propertyRepository,
        private readonly ProfileTypeFactory $factory,
        private readonly Environment $twig,
        private readonly UploadHelper $uploadHelper,
        private readonly string $prefix
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            UserAccountDisplayEvent::class => ['uiView'],
            EditUserFormPostCreatedEvent::class => ['amendForm'],
            EditUserFormPostValidatedEvent::class => ['editFormHandler'],
            FormTypeChoiceEvent::class => ['formTypeChoices']
        ];
    }

    /**
     * Render and return profile information for display as part of a hook-like UI event issued from the Users module.
     */
    public function uiView(UserAccountDisplayEvent $event): void
    {
        $event->addContent(self::EVENT_KEY, $this->twig->render('@ZikulaProfile/Hook/display.html.twig', [
            'prefix' => $this->prefix,
            'user' => $event->getUser(),
            'activeProperties' => $this->propertyRepository->getDynamicFieldsSpecification()
        ]));
    }

    public function amendForm(EditUserFormPostCreatedEvent $event): void
    {
        $user = $event->getFormData();
        $uid = !empty($user['uid']) ? $user['uid'] : Constant::USER_ID_ANONYMOUS;
        $userEntity = $this->userRepository->find($uid);
        $attributes = $userEntity->getAttributes() ?? [];

        // unpack json values (e.g. array for multi-valued options)
        foreach ($attributes as $key => $attribute) {
            $value = $attribute->getValue();
            if (is_string($value) && is_array(json_decode($value, true)) && JSON_ERROR_NONE === json_last_error()) {
                $attribute->setValue(json_decode($value, true));
            }
        }

        $profileForm = $this->formFactory->createForm($attributes, false);
        $event
            ->formAdd($profileForm)
            ->addTemplate('@ZikulaProfile/Hook/edit.html.twig')
        ;
    }

    public function editFormHandler(EditUserFormPostValidatedEvent $event): void
    {
        $userEntity = $event->getUser();
        $formData = $event->getFormData(ProfileConstant::FORM_BLOCK_PREFIX);
        foreach ($formData as $key => $value) {
            if (!empty($value)) {
                if ($value instanceof UploadedFile) {
                    $value = $this->uploadHelper->handleUpload($value, $userEntity->getUid());
                } elseif (is_array($value)) {
                    // pack multi-valued options into json
                    $value = json_encode($value);
                }
                $userEntity->setAttribute($key, $value);
            } elseif (false === mb_strpos($key, 'avatar')) {
                $userEntity->delAttribute($key);
            }
        }

        // we do not call flush here on purpose because maybe
        // other modules need to care for certain things before
        // the Users module calls flush after all listeners finished
    }

    public function formTypeChoices(FormTypeChoiceEvent $event): void
    {
        $choices = $event->getChoices();

        $groupName = $this->translator->trans('Other Fields', [], 'zikula');
        if (!isset($choices[$groupName])) {
            $choices[$groupName] = [];
        }

        $groupChoices = $choices[$groupName];
        $groupChoices[$this->translator->trans('Avatar')] = AvatarType::class;
        $choices[$groupName] = $groupChoices;

        $event->setChoices($choices);
    }
}
