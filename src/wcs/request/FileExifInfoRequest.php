<?php
/*
 * FileExifInfoRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

use wcs\helper\WcsHelper;

class FileExifInfoRequest extends ManageRequest
{
    public function getPath() {
        return '/stat/'.$this->buildEntryURI();
    }

    private function buildEntryURI() {
        $entryUri = $this->bucket.':'.$this->key;
        return WcsHelper::urlsafeBase64Encode($entryUri);
    }
}