<?php
/*
* AvBriefInfoRequest.php
*
* created by laihy 2017年4月28日
*/

namespace wcs\request;

class AvBriefInfoRequest extends DownloadRequest
{
    public function getOp() {
        return 'avinfo2';
    }
}