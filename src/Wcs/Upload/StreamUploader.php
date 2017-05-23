<?php


namespace Wcs\Upload;

use Wcs;
use Wcs\Http\PutPolicy;
use Wcs\Config;
use Wcs\Utils;
use Wcs\Http\Response;


class StreamUploader
{

    function __construct($token)
    {
        $this->token = $token;
    }



    /**
     * 普通上传
     * @param $bucketName
     * @param $fileName
     * @param $localFile
     * @param $returnBody
     * @return string
     */
    function upload_return($Stream) {
        $resp = $this->_upload($Stream);

        return $this->build_result($resp);

    }

    function _upload($Stream) {

        //$streambody = Utils::http_get($Stream, $headers=null);
        //$content = $streambody->respBody;
        $content = stream_get_contents(fopen($Stream, "rb"));
        if(!isset($content)||strlen($content)==0) {
            die("ERROR: {$Stream}流地址无效！"."\n");
        }
        if(!is_dir(Config::WCS_RAM_URL))
        {
            die("ERROR: 虚拟内存目录不存在！");
        }
        $filename = Config::WCS_RAM_URL."steam";

        $fp = fopen($filename, "w+b");
        fwrite($fp, $content);
        rewind($fp);
        $url = Config::WCS_PUT_URL . '/file/upload';

        $mimeType = null;
        $fields = array(
            'token' => $this->token,
            'file' => $this->create_file($filename),
        );
        $opt = array(
            CURLOPT_NOPROGRESS => true
        );
        $resp = Utils::http_post($url, null, $fields, $opt);
        fclose($fp);
        return $resp;
    }


    private function create_file($filename, $mime=null)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $mime);
        }
        // Use the old style if using an older version of PHP
        $value = "@{$filename}";
        if (!empty($mime)) {
            $value .= ';type=' . $mime;
        }

        return $value;
    }

    private function build_result($resp) {
        if ($resp->code == 28) {
            $ret = Array(
                'code' => 28,
                'message' => '请求超时！'
            );
            return json_encode($ret, JSON_UNESCAPED_UNICODE);
        } else {
            return $resp;
        }
    }

}
