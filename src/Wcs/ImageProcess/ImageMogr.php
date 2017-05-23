<?php

namespace  Wcs\ImageProcess;

use Wcs\Utils;

class ImageMogr
{
    /**
     * @var $autoOrient
     * true表示根据原图EXIF信息自动旋正，便于后续处理，建议放在首位。
     */
    public $autoOrient;
    /**
     * @var $thumbnail
     * 缩放操作参数表，缺省为不缩放。
     */
    public $thumbnail;
    /**
     * @var $gravity
     * 裁剪操作参数表，只影响其后的裁剪偏移参数，缺省为左上角（NorthWest）。
     */
    public $gravity;
    /**
     * @var $crop
     * 裁剪偏移参数表，缺省为不裁剪
     */
    public $crop;
    /**
     * @var $quality
     * 图片质量，取值范围1-100，缺省为85
     *     如原图质量小于指定质量，则使用原图质量。
     */
    public $quality;
    /**
     * @var $rotate
     * 旋转角度，取值范围1-360，缺省为不旋转。
     */
    public $rotate;
    /**
     * @var $format
     * 图片格式，支持jpg、gif、png等，缺省为原图格式。
     */
    public $format;


    public function buildUrlParams()
    {
        $params = '?op=imageMogr2';

        if (!empty($this->autoOrient)) {
            $params .= '&auto-orient=' . $this->autoOrient;
        }

        if (!empty($this->thumbnail)) {
            $params .= '&thumbnail=' . $this->thumbnail;
        }

        if (!empty($this->gravity)) {
            $params .= '&gravity=' . $this->gravity;
        }

        if (!empty($this->crop)) {
            $params .= '&crop=' . $this->crop;
        }

        if (!empty($this->quality)) {
            $params .= '&quality=' . $this->quality;
        }

        if (!empty($this->rotate)) {
            $params .= '&rotate=' . $this->rotate;
        }

        if (!empty($this->format)) {
            $params .= '&format=' . $this->format;
        }

        return $params;
    }

    /**
     * 执行操作函数
     * @param $bucketName
     * @param $fileName
     * @return mixed
     */
    public function exec($bucketName, $fileName, $localFile = null) {

        if(empty($localFile)) {
            $localFile = $fileName;
        }

        $baseUrl  =  Utils::build_public_url($bucketName, $fileName);
        $params = $this->buildUrlParams();
        $url = $baseUrl . $params;

        $resp = Image::GET($url, $localFile);

        return $resp->message;
    }

}