<?php
/*
 * WcsClient.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs;

use wcs\http\HttpClient;
use wcs\helper\WcsHelper;

use wcs\request\FileListRequest;
use wcs\request\FileInfoRequest;

use wcs\request\ImageInfoRequest;
use wcs\request\ImageExifInfoRequest;
use wcs\request\AvInfoRequest;
use wcs\request\AvBriefInfoRequest;
use wcs\request\PersistentStatusRequest;
use wcs\request\FileDeleteRequest;
use wcs\request\M3u8FileDeleteRequest;
use wcs\request\AdvancedFileDeleteRequest;

class WcsClient
{
    /**
    * 构造函数
    * $options 数组
    *   tokenDeadline   非必须 token有效期，默认值1小时==3600秒
    *   timeout         非必须 超时时间，默认30秒
    **/
    public function __construct($options=[]) {
        $this->config = require(dirname(__FILE__).'/../Config.php');
        if (isset($options['tokenDeadline'])) $this->config['tokenDeadline'] = $options['tokenDeadline'];
        if (isset($options['timeout'])) $this->config['timeout'] = $options['timeout'];
    }

    /**
     * 任务状态查询
     * @param string $persistentId
     * @return unknown|\wcs\response\Response
     */
    public function persistentStatus($persistentId) {
        $request = new PersistentStatusRequest($this->config);
        $request->queries = [
            'persistentId' => $persistentId,
        ];

        $this->request = $request;
        return $this->get();
    }

    /**
     * 删除m3u8文件
     * @param array $items
     * [
     *   [
     *      'bucket':'bucketName',  // 空间名
     *      'key':'keyName',        // 文件名
     *      'isDeleteTs': 0,        // 指定是否进行关联删除ts文件 0-不关联  1-关联
     *   ]
     * ]
     * @param string $notifyUrl
     * @param number $separate 0：表示所有指令执行完后再一次性通知notifyURL  1：表示每个指令执行完后都通知notifyURL
     * @return unknown|\wcs\response\Response
     */
    public function deleteM3u8($items, $notifyUrl=null, $separate=0) {
        $request = new M3u8FileDeleteRequest($this->config);
        $request->items = $items;
        $request->params = [
            'fops' => $request->buildFops(),
        ];
        if ($notifyUrl) {
            $request->params['notifyURL'] = WcsHelper::urlsafeBase64Encode($notifyUrl);
            $request->params['separate'] = $separate;
        }

        $this->request = $request;
        return $this->post();
    }

    /**
     * 高级删除文件-支持异步通知，一次删除多个资源
     * @param array $items
     * [
     *   [
     *      'bucket':'bucketName',  // 空间名
     *      'key':'keyName',        // 文件名
     *   ]
     * ]
     * @param string $notifyUrl
     * @param number $separate 0：表示所有指令执行完后再一次性通知notifyURL  1：表示每个指令执行完后都通知notifyURL
     * @return unknown|\wcs\response\Response
     */
    public function advancedFileDelete($items, $notifyUrl=null, $separate=0) {
        $request = new AdvancedFileDeleteRequest($this->config);
        $request->items = $items;
        $request->params = [
            'fops' => $request->buildFops(),
        ];
        if ($notifyUrl) {
            $request->params['notifyURL'] = WcsHelper::urlsafeBase64Encode($notifyUrl);;
            $request->params['separate'] = $separate;
        }

        $this->request = $request;
        return $this->post();
    }

    /**
    * 删除文件
    *   $bucket 空间名
    *   $key    文件名
    **/
    public function fileDelete($bucket, $key) {
        $request = new FileDeleteRequest($this->config);
        $request->bucket = $bucket;
        $request->key = $key;

        $this->request = $request;
        return $this->post();
    }

    /**
    * 文件信息
    *   $bucket 空间名
    *   $key    文件名
    **/
    public function fileInfo($bucket, $key) {
        $request = new FileInfoRequest($this->config);
        $request->bucket = $bucket;
        $request->key = $key;

        $this->request = $request;
        return $this->get();
    }

    /**
    * 文件列表
    *   $bucket 空间名
    *   $limit  返回条数
    *   $prefix 前缀
    **/
    public function fileList($bucket, $limit=self::LIST_LIMIT, $prefix='') {
        $request = new FileListRequest($this->config);
        $request->queries = [
            'bucket'    => $bucket,
            'limit'     => $limit,
            'prefix'    => WcsHelper::urlsafeBase64Encode($prefix),
        ];

        $this->request = $request;
        return $this->get();
    }

    /**
     * 图片信息
     * @param string $bucket    空间名
     * @param string $key       文件名
     * @return \wcs\response\Response
     */
    public function imageInfo($bucket, $key) {
        $request = new ImageInfoRequest($this->config);
        $request->key = $key;
        $request->downloadDomain = $this->getDownloadDomain($bucket);

        $this->request = $request;
        return $this->get();
    }

    /**
     * 图片EXIF信息
     * @param string $bucket    空间名
     * @param string $key       文件名
     * @return \wcs\response\Response
     */
    public function imageExifInfo($bucket, $key) {
        $request = new ImageExifInfoRequest($this->config);
        $request->key = $key;
        $request->downloadDomain = $this->getDownloadDomain($bucket);

        $this->request = $request;
        return $this->get();
    }

    /**
     * 获取音视频元数据
     * @param string $bucket
     * @param string $key
     * @return \wcs\response\Response
     */
    public function avInfo($bucket, $key) {
        $request = new AvInfoRequest($this->config);
        $request->key = $key;
        $request->downloadDomain = $this->getDownloadDomain($bucket);

        $this->request = $request;
        return $this->get();
    }

    /**
     * 获取音视频简单元数据
     * @param string $bucket
     * @param string $key
     * @return \wcs\response\Response
     */
    public function avBriefInfo($bucket, $key) {
        $request = new AvBriefInfoRequest($this->config);
        $request->key = $key;
        $request->downloadDomain = $this->getDownloadDomain($bucket);

        $this->request = $request;
        return $this->get();
    }

    // 发送GET请求
    private function get() {
        return $this->send(HttpClient::HTTP_GET);
    }

    // 发送POST请求
    private function post() {
        return $this->send(HttpClient::HTTP_POST);
    }

    // 发送PUT请求
    private function put() {
        return $this->send(HttpClient::HTTP_PUT);
    }

    // 发送DELETE请求
    private function delete() {
        return $this->send(HttpClient::HTTP_DELETE);
    }

    private function send($method) {
        $url = $this->request->buildUri();

        $options = [
            'agent' => $this->userAgent(),
            'timeout' => $this->config['timeout'],
            'tokenDeadline' => $this->config['tokenDeadline'],
        ];

        $header = $this->request->getHeader();

        $client = new HttpClient($method, $url, $header, $options);
        return $client->send($this->request->getBody());
    }

    private function userAgent() {
        return WcsHelper::buildAgent(self::WCS_SDK_VERSION);
    }

    private function getDownloadDomain($bucket) {
        $domains = $this->config['bucketDomains'];
        if (isset($domains[$bucket])) {
            return $domains[$bucket];
        }

        return self::HTTP.$bucket.'.'.self::DEFAULT_BASE_DOMAIN;
    }

    const WCS_SDK_VERSION = '3.0.0';
    const HTTP = 'http://';
    const DEFAULT_BASE_DOMAIN = 'w.wcsapi.biz.matocloud.com';
    const LIST_LIMIT = 1000;

    private $config;
    private $request;
    private $response;
}