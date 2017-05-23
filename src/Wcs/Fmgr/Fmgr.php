<?php
namespace Wcs\Fmgr;

use Wcs\Config;
use Wcs\Utils;

class Fmgr {

    /**
     * @var $fops
     * 不同的操作，对应的fops参数不一样，详见wcs 文档的说明
     */
    public $fops;

    /**
     * @var null $notifyURL 处理结果通知的URL
     */
    public $notifyURL;

    /**
     * @var $force
     * 当处理结果已经存在，是否强制执行数据处理，1为强制执行，0为不强制，0为默认值
     */
    public $force;

    /**
     * @var int $separate 处理指令是否分开通知
     * 1为每个指令完成都通知 notifyURL，0为所有指令执行完后一次性通知 notifyURL
     * 默认值为0
     */
    public $separate;

    /**
     * Fmgr constructor.
     * 三个参数都是可选的，类实例化时指定
     * @param null $notifyURL
     * @param int $force
     * @param int $separate
     */
     public $auth;

    public function __construct($auth, $notifyURL = null, $force = 0, $separate = 0)
    {
        $this->notifyURL = $notifyURL;
        $this->separate = $separate;
        $this->force = $force;
        $this->auth = $auth;
    }

    /**
     * 抓取资源，并存储到指定空间,fops 格式如下
     * fops=fetchURL/<Urlsafe_Base64_Encoded_URL>
        /bucket/<Urlsafe_Base64_Encoded_bucket>
        /key/<Urlsafe_Base64_Encoded_key>
        /prefix/<Urlsafe_Base64_Encoded_prefix>
        /md5/<md5>
        & notifyURL =<Urlsafe_Base64_Encoded_notifyUrl>
        &force=<Force>& separate=<Separate>
     * @param $fops
     * @return mixed
     */
    private function _genernate_header($url, $body=null)
    {
        $token = $this->auth->get_token($url, $body=$body);
        $headers = array("Authorization:$token");
        return $headers;
    }
    public function fetch($fops) {

        $url = Config::WCS_MGR_URL . "/fmgr/fetch";
        $signingStr = "/fmgr/fetch" . "\n";
        $content = $this->_addContent($fops);

        $headers = $this->_genernate_header($url, $content);

        $resp = $this->_post($url, $headers, $content);

        return $resp;

    }


    /**
     * 复制资源,fops格式如下
     * fops=resource/<EncodeEntryURI>
        /bucket/<Urlsafe_Base64_Encoded_bucket>
        /key/<Urlsafe_Base64_Encoded_key>
        /prefix/<Urlsafe_Base64_Encoded_prefix>
        &notifyURL =<Urlsafe_Base64_Encoded_notifyUrl>
        &separate=<Separate>
     * @param $fops
     * @return mixed
     */
    public function copy($fops) {

        $url = Config::WCS_MGR_URL . "/fmgr/copy";
        $signingStr = "/fmgr/copy" . "\n";
        $content = $this->_addContent($fops);
        $headers = $this->_genernate_header($url, $content);
        $resp = $this->_post($url, $headers, $content);

        return $resp;
    }

    /**
     * 移动资源，fops格式如下
     * fops=resource/<EncodeEntryURI>
        /bucket/<Urlsafe_Base64_Encoded_bucket>
        /key/<Urlsafe_Base64_Encoded_key>
        /prefix/<Urlsafe_Base64_Encoded_prefix>
        &notifyURL =<Urlsafe_Base64_Encoded_notifyUrl>
        &separate=<Separate>
     * @param $fops
     * @return mixed
     */
    public function move($fops) {

        $url = Config::WCS_MGR_URL . "/fmgr/move";
        $signingStr = "/fmgr/move" . "\n";
        $content = $this->_addContent($fops);
        $headers = $this->_genernate_header($url, $content);
        $resp = $this->_post($url, $headers, $content);

        return $resp;
    }

    /**
     * 删除资源,fops格式如下
     *fops=bucket/<Urlsafe_Base64_Encoded_bucket>
        /key/<Urlsafe_Base64_Encoded_key>
        &notifyURL =<Urlsafe_Base64_Encoded_notifyUrl>
        &eparate=<Separate>
     * @param $fops
     * @return mixed
     */
    public function delete($fops) {

        $url = Config::WCS_MGR_URL . "/fmgr/delete";
        $signingStr = "/fmgr/delete" . "\n";
        $content = $this->_addContent($fops);
        $headers = $this->_genernate_header($url, $content);
        $resp = $this->_post($url, $headers, $content);

        return $resp;
    }


    /**
     * 按前缀删除资源, fops格式如下
     * fops=bucket/<Urlsafe_Base64_Encoded_bucket>
        /prefix/<Urlsafe_Base64_Encoded_prefix>
        /output/<Urlsafe_Base64_Encoded_ output>
        &notifyURL =<Urlsafe_Base64_Encoded_notifyUrl>
        &separate=<Separate>
     * @param $fops
     * @return mixed
     */
    public function deletePrefix($fops) {
        $url = Config::WCS_MGR_URL . "/fmgr/deletePrefix";
        $signingStr = "/fmgr/deletePrefix" . "\n";
        $content = $this->_addContent($fops);
        $headers = $this->_genernate_header($url, $content);
        $resp = $this->_post($url, $headers,$content);

        return $resp;
    }

    /**
     * 任务查询
     * @param $persistentId
     * @return mixed
     */
    public function status($persistentId) {
        $url = Config::WCS_MGR_URL . "/fmgr/status?persistentId=" . $persistentId;
        $resp = Utils::http_get($url, null);

        return $resp;
    }

    /**
     * @param $fops
     * @return int|string
     */
    private function _addContent($fops) {

        $this->fops = $fops;
        $content = $this->fops;

        if(!empty($this->notifyURL)) {
            $content .= "&notifyURL=" . Utils::url_safe_base64_encode($this->notifyURL);
        }
        $content .= "&force=" . $this->force;
        $content .= "&separate=" . $this->separate;

        return $content;
    }

    /**
     * @param $url
     * @param $token
     * @param $content
     * @return mixed
     */
    private function _post($url, $headers, $body) {

        $resp = Utils::http_post($url, $headers, $body);

        return $resp;
    }

}
