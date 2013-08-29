<?php

namespace Acme\ExampleModule\Api;

class UserApi extends \Zikula_AbstractApi
{
    public function view(array $array = array())
    {
        return 'view: 123';
    }
}
