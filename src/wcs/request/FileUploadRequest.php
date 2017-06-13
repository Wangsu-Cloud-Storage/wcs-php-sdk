<?php
/*
* FileUploadRequest.php
*
* created by laihy 2017年5月3日
*/

namespace wcs\request;

class FileUploadRequest extends UploadRequest
{
    public function getHeader() {
        return [];
    }
}