<?php
/*
 * FileListRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

class FileListRequest extends ManageRequest
{
    public function getPath() {
        return '/list';
    }
}