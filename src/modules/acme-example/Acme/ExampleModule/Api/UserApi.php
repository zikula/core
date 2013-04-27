<?php

namespace Acme\ExampleModule\Api;

use Zikula\Core\Api\AbstractApi;

class UserApi extends AbstractApi
{
    public function view(array $array = array())
    {
        return 'view: 123';
    }
}
