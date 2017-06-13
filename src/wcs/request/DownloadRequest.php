<?php
/*
 * DownloadRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;


abstract class DownloadRequest extends Request
{
    public $key;
    public $downloadDomain;

    public function __construct($options) {
        $this->queries = [
            'op' => $this->getOp(),
        ];
        parent::__construct($options);
    }

    public function buildUri() {
        return $this->downloadDomain.'/'.$this->key.'?'.$this->getQuery();
    }

    public function getHeader() {
        return [];
    }

    /**
     * 获取操作类型
     */
    public abstract function getOp();
}