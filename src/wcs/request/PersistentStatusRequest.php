<?php

/*
* PersistentStatusRequest.php
*
* created by laihy 2017年5月4日
*/

namespace wcs\request;

use wcs\request\ManageRequest;

class PersistentStatusRequest extends ManageRequest
{
    public function getPath() {
        return '/fmgr/status';
    }
}