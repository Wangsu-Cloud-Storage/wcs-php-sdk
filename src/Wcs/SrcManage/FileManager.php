<?php
namespace  Wcs\SrcManage;

use Wcs;
use Wcs\Config;
use Wcs\Utils;

class FileManager
{
     /**
     * 移动资源
     * @param $bucketSrc //源空间
     * @param $bucketDst //目标空间
     * @param $keySrc
     * @param $keyDst
     * @return Wcs\Http\Response
     */
    private $auth;

    function __construct($auth) {
        $this->auth = $auth;
    }
    private function _genernate_header($url, $body=null)
    {
        $token = $this->auth->get_token($url, $body=$body);
        $headers = array("Authorization:$token");
        return $headers;
    }

    public function move($bucketSrc, $keySrc, $bucketDst, $keyDst) {
        $paramSrc = $bucketSrc . ":" . $keySrc;
        $paramSrc = Wcs\url_safe_base64_encode($paramSrc);
        $paramDst = $bucketDst . ":" . $keyDst;
        $paramDst = Wcs\url_safe_base64_encode($paramDst);

        $url = Config::WCS_MGR_URL . "/move/" . $paramSrc . "/" . $paramDst;

        $headers = $this->_genernate_header($url);

        $resp = $this->_post($url, $headers);

        return $resp;
    }

     /**
     * 复制资源
     * @param $bucketSrc //源空间
     * @param $bucketDst //目标空间
     * @param $keySrc
     * @param $keyDst
     * @return Wcs\Http\Response
     */
    public function copy($bucketSrc, $keySrc, $bucketDst, $keyDst) {
        //encodeEntryUrl bucket:key
        $paramSrc = $bucketSrc . ":" . $keySrc;
        $paramSrc = Wcs\url_safe_base64_encode($paramSrc);
        $paramDst = $bucketDst . ":" . $keyDst;
        $paramDst = Wcs\url_safe_base64_encode($paramDst);

        $url = Config::WCS_MGR_URL . "/copy/" . $paramSrc . "/" . $paramDst;
        $headers = $this->_genernate_header($url);

        $resp = $this->_post($url, $headers);

        return $resp;

    }

    /**
     * 删除文件
     * @param $bucketName
     * @param $fileKey
     * @return mixed
     */
    public function delete($bucketName, $fileKey)
    {
        $entry = $bucketName . ':' . $fileKey;
        $encodedEntry = Utils::url_safe_base64_encode($entry);

        $url = Config::WCS_MGR_URL . '/delete/' . $encodedEntry;
        $headers = $this->_genernate_header($url);

        return $this->_post($url, $headers);

    }
    /**
     * 获取文件信息
     * @param $bucketName
     * @param $fileKey
     * @return mixed
     */
    public function stat($bucketName, $fileKey)
    {
        $entry = $bucketName . ':' . $fileKey;
        $encodedEntry = Utils::url_safe_base64_encode($entry);


        $url = Config::WCS_MGR_URL . '/stat/' . $encodedEntry;
        $headers = $this->_genernate_header($url);

        return $this->_get($url, $headers);
    }

    /**
     * 列举资源
     * @param   $bucket
     * @param   $limit
     * @param   $prefix
     * @param   $mode
     * @param   $marker
     */
    public function setDeadline($bucketName, $fileKey,$deadline)
    {
        $encodebucket = Utils::url_safe_base64_encode($bucketName);
        $encodekey = Utils::url_safe_base64_encode($fileKey);
        $body = 'bucket='.$encodebucket.'&'.'key='.$encodekey.'&'.'deadline='.$deadline;

        $url = Config::WCS_MGR_URL.'/setdeadline';
        $headers = $this->_genernate_header($url, $body);
        return $this->_post($url, $headers, $body);
    }
    public function bucketList($bucket, $limit = 1000, $prefix = null, $mode = null, $marker = null)
    {

        $path = '/list';
        $path .= "?bucket=$bucket";
        $path .= "&limit=$limit";
        if($prefix !== null) {
            $prefix = Utils::url_safe_base64_encode($prefix);
            $path.= "&prefix=$prefix";
        }
        if($mode !== null) {
            $path .= "&mode=$mode";
        }
        if($marker !== null) {
            $path .= "&marker=$marker";
        }

        $url = Config::WCS_MGR_URL . $path;
        $headers = $this->_genernate_header($url);
        $resp = $this->_get($url, $headers);
        return $resp;

    }

     /**
     * 更新镜像资源
     * @param $fileKeys
     */
    public function updateMirrorSrc($bucket, $fileKeys) {
        $url = Config::WCS_MGR_URL . '/prefetch/';
        $separator = "|";
        $files = explode($separator, $fileKeys);
        $param = $bucket.":";
        foreach ($files as $index => $file) {
            $param .= Wcs\url_safe_base64_encode($file);
            if($index !== (sizeof($files) - 1)) {
                $param .= "|";
            }
        }
        $param = Wcs\url_safe_base64_encode($param);
        $url .= $param;
        $headers = $this->_genernate_header($url);
        $resp = $this->_post($url, $headers);

        return $resp;


    }

    /**
     * 获取音视频的元信息
     * @param   $key
     * */
    public  function  avInfo($host, $fileName) {
        $url = $host . '/' . $fileName;
        $params =  '?op=avinfo';
        $url .= $params;
        $resp =Utils::http_get($url, null);

        return $resp;
    }

    /**
     * 获取音视频简单元信息
     * @param   $key
     * */
     public  function  avInfo2($host, $fileName) {
        $url = $host . '/' . $fileName;
        $params =  '?op=avinfo2';
        $url .= $params;
        $resp =Utils::http_get($url, null);

        return $resp;
    }

    private function  _get($url, $headers=null) {
        $resp = Utils::http_get($url, $headers);

        return $resp;
    }

    private function _post($url, $headers=null, $body=null) {
        $resp = Utils::http_post($url, $headers, $body);

        return $resp;
        //return $resp;
    }


}
