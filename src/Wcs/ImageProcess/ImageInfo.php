<?php
namespace Wcs\ImageProcess;

use Wcs\Utils;


class ImageInfo
{

    /**
     * 获取图片信息
     * @param   $key
     * */
    public function imgInfo($bucketName, $fileName) {
        $url = Utils::build_public_url($bucketName, $fileName);
        $params = '?op=imageInfo';
        $url .= $params;
        $resp =Utils::http_get($url, null);

        return $resp;
    }

    /**
     * 获取图片EXIF信息
     * @param   $key
     * */
    public function imgEXIF($bucketName, $fileName) {
        $url = Utils::build_public_url($bucketName, $fileName);
        $params = '?op=exif';
        $url .= $params;
        $resp = Utils::http_get($url, null);

        return $resp;
    }


}
