<?php
/*
* AvInfoRequest.php
*
* created by laihy 2017年4月28日
*/

namespace wcs\request;

class AvInfoRequest extends DownloadRequest
{
    public function getOp() {
        return 'avinfo';
    }
}