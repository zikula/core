<?php

namespace Zikula\Core\Tests\LinkContainer\Fixtures;

use Zikula\Core\LinkContainer\LinkContainerInterface;

class FooLinkContainer implements LinkContainerInterface
{
    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $method = 'get' . ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return array();
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = array();
        $links[] = array(
            'url' => '/foo/admin',
            'text' => 'Foo Admin',
            'icon' => 'wrench');

        return $links;
    }

    /**
     * get the User Links for this extension
     *
     * @return array
     */
    private function getUser()
    {
        $links = array();
        $links[] = array(
            'url' => '/foo',
            'text' => 'Foo',
            'icon' => 'check-square-o');

        return $links;
    }

    /**
     * set the BundleName as required buy the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'ZikulaFooExtension';
    }
}