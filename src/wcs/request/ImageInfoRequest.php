<?php
/*
* ImageInfoRequest.php
*
* created by laihy 2017年4月28日
*/

namespace wcs\request;

class ImageInfoRequest extends DownloadRequest
{
    public function getOp() {
        return 'imageInfo';
    }
}