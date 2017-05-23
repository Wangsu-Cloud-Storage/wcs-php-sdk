<?php

namespace Wcs;

use Wcs\Utils;

class MgrAuth
{
    public $AccessKey;
    public $SecretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->AccessKey = $accessKey;
        $this->SecretKey = $secretKey;
    }

    public function get_token($url, $body=null)
    {
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        if($query) {
            if ($body) {
                $arr = array($path,'?',$query,"\n",$body);
            }
            else {
                $arr = array($path,'?',$query,"\n");
            }
        }
        else {
            if ($body) {
                $arr = array($path,"\n",$body);
            }
            else {
                $arr = array($path,"\n");
            }
        }
        $sign = join("",$arr);
        $encodesign = hash_hmac('sha1', $sign, $this->SecretKey, false);
        return $this->AccessKey . ':' . Utils::url_safe_base64_encode($encodesign);
    }
}


