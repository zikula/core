<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class OptionValidatorListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => ['onPostSubmit'],
        ];
    }

    public function onPostSubmit(FormEvent $event)
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
                                $form->addError(new FormError($option['key'] . ' must be either (string) "true" or "false").')); // @todo translate
                        }
                        break;
                    case 'array':
                        $option['value'] = str_replace("'", '"', $option['value']);
                        $json = json_decode($option['value'], true);
                        if (null === $json) {
                            $form->addError(new FormError($option['key'] . ' must have a value that can be json_decoded.')); // @todo translate
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

        if (isset($requirements[$option])) {
            return $requirements[$option];
        }

        return false;
    }
}
