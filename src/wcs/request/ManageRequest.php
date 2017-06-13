<?php
/*
 * ManageRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

use wcs\helper\WcsHelper;

abstract class ManageRequest extends Request
{
    public $fops;

    public function buildToken() {
        $signingString = $this->getPath();
        if ($this->getQuery()) $signingString .= '?'.$this->getQuery();
        $signingString .= "\n".$this->getBody();

        $encodeSign = hash_hmac('sha1', $signingString, $this->secretKey, false);
        $token = $this->accessKey.':'.WcsHelper::urlsafeBase64Encode($encodeSign);
        return $token;
    }

    public function buildUri() {
        $uri = $this->manageUrl.$this->getPath();
        if ($this->getQuery()) {
            $uri .= '?'.$this->getQuery();
        }

        return $uri;
    }

    public function getHeader() {
        return [
            'Authorization:'.$this->buildToken(),
        ];
    }

    public abstract function getPath();
}