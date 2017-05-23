<?php
namespace Wcs\ImageProcess;

use Wcs\Utils;


class ImageView {

    /**
     * @var $mode
     * 缩放模式
     * mode = 1	限定缩略图的宽最少为<width>，高最少为<height>，进行等比缩放，居中裁剪。转后的缩略图通常恰好是<width>x<height>的大小（有一个边缩放的时候会因为超出矩形框而被裁剪掉多余部分）。如果只指定width参数或只指定height参数，代表限定为长宽相等的正方图。
     * mode = 2	限定缩略图的宽度最多为<width>，高度最多为<height>，进行等比缩放，不裁剪。如果只指定width参数则表示限定宽度（高度自适应），只指定height 参数则表示限定高度（宽度自适应）。
     * mode = 3	限定缩略图的宽最少为<width>，高最少为<height>，进行等比缩放，不裁剪。
     */
    public $mode;
    /**
     * @var %width 缩放宽度（单位px）
     */
    public $width;
    /**
     * @var $height （单位px）
     */
    public $height;
    /**
     * @var $quality
     * 新图的图像质量，取值范围：1-100，缺省为85；
     * 如原图质量小于指定值，则按原值输出
     */
    public $quality;
    /**
     * @var $format
     * 新图的输出格式，取值范围：jpg，gif，png等，缺省为原图格式
     */
    public $format;

    function __construct($mode) {
        $this->mode = $mode;
    }


    public function buildUrlParams() {
        $params = '?op=imageView2&mode=' . $this->mode;

        if (!empty($this->width)) {
            $params .= '&width=' . $this->width;
        }

        if (!empty($this->height)) {
            $params .= '&$height=' . $this->height;
        }

        if (!empty($this->quality)) {
            $params .= '&$quality=' . $this->quality;
        }

        if (!empty($this->format)) {
            $params .= '&$format=' . $this->format;
        }

        return $params;
    }

    /**
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

        //返回响应信息
        return $resp->message;
    }
}