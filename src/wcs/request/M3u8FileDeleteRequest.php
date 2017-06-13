<?php
/*
* M3u8FileDeleteRequest.php
*
* created by laihy 2017年5月3日
*/

namespace wcs\request;

use wcs\helper\WcsHelper;

class M3u8FileDeleteRequest extends ManageRequest
{
    public $items;

    public function getPath() {
        return '/fmgr/deletem3u8';
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
        $isDeleteTs = isset($item['isDeleteTs']) ? $item['isDeleteTs'] : 0;
        $fops = 'bucket/'.WcsHelper::urlsafeBase64Encode($bucket).
                '/key/'.WcsHelper::urlsafeBase64Encode($key).
                '/deletets/'.$isDeleteTs;

        return $fops;
    }
}