<?php

class ExampleDoctrine_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        // Define an empty array to hold the list of admin links
        $links = array();

        if (SecurityUtil::checkPermission('ExampleDoctrine::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => ModUtil::url('ExampleDoctrine', 'admin', 'main'),
                'text' => $this->__('Main'));
        }
        // Return the links array back to the calling function
        return $links;
    }
}