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

namespace Zikula\MenuModule\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class OptionValidatorListener implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => ['onPostSubmit']
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $options = $event->getData();
        $form = $event->getForm();
        if (null === $options) {
            return;
        }
        foreach ($options as $k => $option) {
            if ($type = $this->optionRequiresValueType($option['key'])) {
                switch ($type) {
                    case 'boolean':
                        switch ($option['value']) {
                            case 'true':
                                $option['value'] = true;
                                break;
                            case 'false':
                                $option['value'] = false;
                                break;
                            default:
                                $error = $this->translator->trans('%key% must be either (string) "%true%" or "%false%").', ['%key%' => $option['key'], '%true%' => 'true', '%false%' => 'false']);
                                $form->addError(new FormError($error));
                        }
                        break;
                    case 'array':
                        $option['value'] = str_replace("'", '"', $option['value']);
                        $json = json_decode($option['value'], true);
                        if (null === $json) {
                            $error = $this->translator->trans('%key% must have a value that can be json_decoded.', ['%key%' => $option['key']]);
                            $form->addError(new FormError($error));
                        }
                        break;
                }
            }
        }
        $event->setData($options);
    }

    private function optionRequiresValueType($option)
    {
        $requirements = [
            'routeParameters' => 'array',
            'attributes' => 'array',
            'linkAttributes' => 'array',
            'childrenAttributes' => 'array',
            'labelAttributes' => 'array',
            'display' => 'boolean',
            'displayChildren' => 'boolean',
        ];

        return $requirements[$option] ?? false;
    }
}
