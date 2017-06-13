<?php
/*
* AdvancedFileDeleteRequest.php
*
* created by laihy 2017年5月4日
*/

namespace wcs\request;

use wcs\helper\WcsHelper;

class AdvancedFileDeleteRequest extends ManageRequest
{
    public $items;

    public function getPath() {
        return '/fmgr/delete';
    }

    public function buildFops() {
        $fops = [];
        foreach ($this->items as $item) {
            $fops[] = $this->buildFop($item);
        }

        return implode(';', $fops);
    }

    private function buildFop($item) {
        $bucket     = $item['bucket'];
        $key        = $item['key'];
        $fops = 'bucket/'.WcsHelper::urlsafeBase64Encode($bucket).
                '/key/'.WcsHelper::urlsafeBase64Encode($key);

        return $fops;
    }
}