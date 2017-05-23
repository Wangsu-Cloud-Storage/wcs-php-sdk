<?php
namespace Wcs;

final class Mac
{

    public $AccessKey;
    public $SecretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->AccessKey = $accessKey;
        $this->SecretKey = $secretKey;
    }

    public function get_token($data)
    {
        $sign = hash_hmac('sha1', $data, $this->SecretKey, false);
        return $this->AccessKey . ':' . Utils::url_safe_base64_encode($sign);
    }

    public function get_token_with_data($data)
    {
        $data = Utils::url_safe_base64_encode($data);
        return $this->get_token($data) . ':' . $data;
    }



}