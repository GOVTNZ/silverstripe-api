<?php

namespace GovtNZ\SilverStripe\Api;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injectable;

class ApiManager
{
    use Injectable;
    use Configurable;

    /**
     * Without a data_dir specified then use the assets/api path.
     *
     * @return string
     */
    public function getDefaultPath()
    {
        return Controller::join_links(
            ASSETS_PATH,
            'api'
        );
    }

    /**
     * Without a data_dir specified then use assets/api.
     *
     * @return string
     */
    public function getDefaultURL()
    {
        return Controller::join_links(
            Director::absoluteBaseURL(),
            ASSETS_DIR,
            'api'
        );
    }
}
