<?php
/*
 * FileDeleteRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

use wcs\helper\WcsHelper;

class FileDeleteRequest extends ManageRequest
{
    public function getPath() {
        return '/delete/'.$this->buildEntryURI();
    }

    private function buildEntryURI() {
        $entryUri = $this->bucket.':'.$this->key;
        return WcsHelper::urlsafeBase64Encode($entryUri);
    }
}