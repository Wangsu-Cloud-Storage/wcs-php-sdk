<?php

namespace  Wcs\PersistentFops;
use Wcs;
use Wcs\Config;
use Wcs\Utils;

class Fops {

    /**
     * @var $bucket
     * 空间名
     */
    private $bucket;
    private $auth;


    public function __construct($auth, $bucket)
    {
        $this->bucket = $bucket;
        $this->auth = $auth;
    }

    private function _genernate_header($url, $body=null)
    {
       $token = $this->auth->get_token($url, $body);
       $headers = array("Authorization:$token");
       return $headers;
    }
    /**
     * 持久化操作函数
     *
     * @return mixed
     */
    public function exec($fops, $key, $notifyURL=null, $force=0, $separate=0) {
        $url = Config::WCS_MGR_URL . '/fops';
        $encodebucket = Utils::url_safe_base64_encode(($this->bucket));
        $body = 'bucket='.$encodebucket;
        $body .= '&key=' . Utils::url_safe_base64_encode($key);
        $body .= '&fops=' .Utils::url_safe_base64_encode($fops);
        if(!empty($notifyURL)) {
            $body .= '&notifyURL=' .Utils::url_safe_base64_encode($notifyURL);
        }
        $body .= '&force=' . $force;
        $body .= '&separate=' . $separate;
        $headers = $this->_genernate_header($url, $body);

        $resp = $this->_post($url, $headers, $body);
        return $resp;

    }

    /**
     * @param $persistentId
     * @return mixed
     */
    public static function status($persistentId) {
        $url = Config::WCS_MGR_URL . '/status/get/prefop?persistentId=' . $persistentId;
        $resp = Utils::http_get($url, null);

        return $resp;
    }


    /**
     * @param $url
     * @param $token
     * @param $content
     * @return mixed
     */
    private function _post($url, $headers, $content) {
        $resp = Utils::http_post($url, $headers, $content);

        return $resp;
    }


}
