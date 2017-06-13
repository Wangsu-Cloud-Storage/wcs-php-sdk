<?php
/*
 * Request.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

abstract class Request
{
    public $accessKey;
    public $secretKey;
    public $uploadUrl;
    public $manageUrl;
    public $bucket;
    public $key;

    /*
    * array query 参数
    */
    public $queries=[];

    /*
    * array post 参数
    */
    public $params=[];

    /**
    * $options 数组
    *   accessKey
    *   secretKey
    *   uploadUrl
    *   manageUrl
    *   tokenDeadline   非必须 token有效期，默认值1小时==3600秒
    *   timeout         非必须 超时时间，默认30秒
    **/
    public function __construct($options) {
        $this->accessKey = $options['accessKey'];
        $this->secretKey = $options['secretKey'];
        $this->uploadUrl = $options['uploadUrl'];
        $this->manageUrl = $options['manageUrl'];
    }

    // 返回URL query
    public function getQuery() {
        if (!$this->queries) return '';

        // 过滤空值
        $queries = [];
        foreach ($this->queries as $key => $value) {
            if ($value) $queries[$key] = $value;
        }
        return http_build_query($queries);
    }

    // 返回json编码的
    public function getBody() {
        if (!$this->params) return '';
        $params= [];
        foreach ($this->params as $key => $value) {
            $params[] = "{$key}={$value}";
        }

        return implode('&', $params);
    }

    public abstract function buildUri();
}