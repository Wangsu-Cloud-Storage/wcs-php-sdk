<?php
/*
 * UploadRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

class UploadRequest extends Request
{
    public function buildToken() {}

    public function getHeader() {
        return [];
    }
}