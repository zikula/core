<?php

namespace Gedmo\Translatable\Hydrator\ORM;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator as BaseObjectHydrator;

/**
 * If query uses TranslationQueryWalker and is hydrating
 * objects - when it requires this custom object hydrator
 * in order to skip onLoad event from triggering retranslation
 * of the fields
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Translatable.Hydrator.ORM
 * @subpackage ObjectHydrator
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class ObjectHydrator extends BaseObjectHydrator
{
    /**
     * 2.1 version
     * {@inheritdoc}
     */
    protected function _hydrateAll()
    {
        $listener = $this->getTranslatableListener();
        $listener->setSkipOnLoad(true);
        $result = parent::_hydrateAll();
        $listener->setSkipOnLoad(false);
        return $result;
    }

    /**
     * 2.2 version
     * {@inheritdoc}
     */
    protected function hydrateAllData()
    {
        $listener = $this->getTranslatableListener();
        $listener->setSkipOnLoad(true);
        $result = parent::hydrateAllData();
        $listener->setSkipOnLoad(false);
        return $result;
    }

    /**
     * Get the currently used TranslatableListener
     *
     * @throws \Gedmo\Exception\RuntimeException - if listener is not found
     * @return TranslatableListener
     */
    protected function getTranslatableListener()
    {
        $translatableListener = null;
        foreach ($this->_em->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof TranslatableListener) {
                    $translatableListener = $listener;
                    break;
                }
            }
            if ($translatableListener) {
                break;
            }
        }

        if (is_null($translatableListener)) {
            throw new \Gedmo\Exception\RuntimeException('The translation listener could not be found');
        }
        return $translatableListener;
    }
}