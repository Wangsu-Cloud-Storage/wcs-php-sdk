<?php
namespace  Wcs\ImageProcess;

use Wcs;
use Wcs\Utils;

class ImageWatermark {
    /**
     * @var $mode
     * 水印模式
     * mode = 1 图片水印
     * mode = 2 文字水印
     */
    public $mode;
    /**
     * @var $dissolve
     * 透明度，取值范围1-100，缺省值100（完全不透明）
     */
    public $dissolve;
    /**
     * @var $gravity
     * 水印位置，取值
     * "TOP_LEFT", "TOP_CENTER", "TOP_RIGHT",
     * "CENTER_LEFT", "CENTER", "CENTER_RIGHT",
     * "BOTTOM_LEFT", "BOTTOM_CENTER", "BOTTOM_RIGHT"
     * 默认："BOTTOM_RIGHT"
     */
    public $gravity;
    /**
     * @var  $dx
     * 横轴边距，单位:像素(px)，缺省值为10
     */
    public $dx;
    /**
     * @var  $dy
     * 纵轴边距，单位:像素(px)，缺省值为10
     */
    public $dy;


    /**
     * @var $image
     * 水印图片地址（公网可访问）
     */
    public $image;


    /**
     * @var $text
     * 水印文字内容
     */
    public $text;
    /**
     * @var $font
     * 水印文字字体
     * 缺省为黑体。支持宋体，楷体，微软雅黑，arial等java平台支持的字体。
     */
    public $font;
    /**
     * @var $fontsize
     * 水印文字大小，单位: 缇，等于1/20磅，缺省值30（默认大小）
     */
    public $fontsize;
    /**
     * @var $fill
     * 水印文字颜色，RGB格式，
     * 可以是颜色名称（比如red）或十六进制（比如#FF0000），参考RGB颜色编码表，
     * 缺省为白色
     */
    public $fill;

    function __construct($mode, $text = null)
    {
        $this->mode = $mode;
        if(!empty($text)) {
            $this->text = $text;
        }

    }


    /**
     * 生成params
     * @return string
     */
    public function buildUrlParams() {
        $mode = $this->mode;

        $params = '?op=watermark&mode=' . $mode;

        if (!empty($this->dissolve)) {
            $params .= '&dissolve=' . $this->dissolve;
        }

        if (!empty($this->gravity)) {
            $params .= '&gravity=' . $this->gravity;
        }

        if (!empty($this->dx)) {
            $params .= '&dx=' . $this->dx;
        }

        if (!empty($this->dy)) {
            $params .= '&dy=' . $this->dy;
        }

        if ($mode === 1) {
            if (!empty($this->image)) {
                $params .= '&image=' . Utils::url_safe_base64_encode($this->image);
            }
        } else if ($mode === 2) {
            if (!empty($this->text)) {
                $params .= '&text=' . Utils::url_safe_base64_encode($this->text);
            }

            if (!empty($this->font)) {
                $params .= '&font=' . Utils::url_safe_base64_encode($this->font);
            }

            if (!empty($this->fontsize)) {
                $params .= '&fontsize=' . $this->fontsize;
            }

            if (!empty($this->fill)) {
                $params .= '&fill=' . Utils::url_safe_base64_encode($this->fill);
            }
        }

        return $params;
    }

    /**
     * exec执行
     * @param $bucketName
     * @param $fileName
     * @return mixed
     */
    public function exec($bucketName, $fileName, $localFile = null) {

        if(empty($localFile)) {
            $localFile = $fileName;
        }

        $baseUrl =  Utils::build_public_url($bucketName, $fileName);
        $params = $this->buildUrlParams();
        $url = $baseUrl . $params;
        //echo $url."\n";
        $resp = Image::GET($url, $localFile);

        //返回响应信息
        return $resp->message;
    }
}