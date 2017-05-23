<?php
namespace  Wcs\Http;
use Wcs\Utils;
use Wcs\Config;

//require_once("auth.php");

final class PutPolicy
{
    /**
     * 指定上传的目标资源空间（bucektName）和资源名（fileName）
    有两种格式：
     * 1. <bucket>，表示允许用户上传文件到指定的 bucket。
     * 2. <bucket>:<filename>，表示允许用户上传指定filename
     */
    public $scope;



    /**
     * 上传请求授权的截止时间, 单位为毫秒
     */
    public $deadline;



    /**
     * Web端文件上传成功或失败后，浏览器都会执行303跳转的URL
     * 通常用于HTML Form上传。
     * 文件上传成功后会跳转到<returnUrl>?upload_ret=<queryString>, <queryString>包含returnBody内容。
     * 文件上传失败后会跳转到<returnUrl>?code=<code>&message=<message>, <code>是错误码，<message>是错误具体信息。
     * 如不设置returnUrl，则直接将returnBody的内容返回给客户端。
     */
    public $returnBody;



    /**
     * 指定是否覆盖服务器上已经存在的文件<br />
     * 1-允许覆盖, 0-不允许
     */
    public $overwrite;



    /**
     * 限定上传文件的大小，单位：字节（Byte）；超过限制的上传内容会被判为上传失败，返回413状态码。
     */
    public $fsizeLimit;



    /**
     * Web端文件上传成功后，浏览器执行303跳转的URL
     */
    public $returnUrl;



    /**
     * 上传成功后，网宿云以POST方式请求该callbackUrl
     * （必须公网URL地址，能正常响应HTTP/1.1 200 OK）。
     * 要求 callbackUrl 的Response返回数据格式为JSON文本体
     * 即Content-Type 为 "application/json"。
     */
    public $callbackUrl;



    /**
     * 上传成功后，网宿云POST方式提交请求的数据。
     * 格式例子:<keyName>=(keyValue)&<keyName>=(keyValue)<br />
     * 必须以键值的格式
     */
    public $callbackBody;



    /**
     * 持久化操作指令列表<br />
     * 转换为flv指令：avthumb/flv/vb/1.25m<br />
     * 视频截图指令：vframe/jpg/offset/1<br />
     * 使用分号";"分隔
     */
    public $persistentOps;



    /**
     * 持久化操作通知Url
     */
    public $persistentNotifyUrl;

    /**
    鉴黄
    */
    public $contentDetect;
    public $detectNotifyURL;
    public $detectNotifyRule;


    public function to_string()
    {
        $policy = array('scope' => $this->scope);

            $this->deadline = round(1000 * (microtime(true) + Config::WCS_TOKEN_DEADLINE));

        $policy['deadline'] = $this->deadline;

        if (!empty($this->returnBody)) {
            $policy['returnBody'] = $this->returnBody;
        }

        if (!empty($this->overwrite)) {
            $policy['overwrite'] = $this->overwrite;
        }

        if (!empty($this->fsizeLimit)) {
            $policy['fsizeLimit'] = $this->fsizeLimit;
        }

        if (!empty($this->returnUrl)) {
            $policy['returnUrl'] = $this->returnUrl;
        }

        if (!empty($this->callbackUrl)) {
            $policy['callbackUrl'] = $this->callbackUrl;
        }

        if (!empty($this->callbackBody)) {
            $policy['callbackBody'] = $this->callbackBody;
        }

        if (!empty($this->persistentOps)) {
            $policy['persistentOps'] = $this->persistentOps;
        }

        if (!empty($this->persistentNotifyUrl)) {
            $policy['persistentNotifyUrl'] = $this->persistentNotifyUrl;
        }

        if (!empty($this->contentDetect)) {
            $policy['contentDetect'] = $this->contentDetect;
        }

        if (!empty($this->detectNotifyURL)) {
            $policy['detectNotifyURL'] = $this->detectNotifyURL;
        }

        if (!empty($this->detectNotifyRule)) {
            $policy['detectNotifyRule'] = $this->detectNotifyRule;
        }

        return json_encode($policy);
    }

    public function get_token() {
        $ppString = $this->to_string();
        $ppString = Utils::url_safe_base64_encode($ppString);
        $ak = Config::WCS_ACCESS_KEY;
        $sk = Config::WCS_SECRET_KEY;
        $sign = hash_hmac('sha1', $ppString, $sk, false);
        return $ak.':'.Utils::url_safe_base64_encode($sign).':'.$ppString;
        #return \Wcs\get_token_with_data($config, $ppString);
    }
}
