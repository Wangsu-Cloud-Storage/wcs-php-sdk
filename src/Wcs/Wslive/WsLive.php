<?php
namespace Wcs\Wslive;

use Wcs;
use Wcs\Config;
use Wcs\Utils;

class WsLive
{
    private $auth;
    function __construct($auth)
    {
        $this->auth = $auth;
    }
    private function _generate_headers($url, $body=null)
    {
        $token = $this->auth->get_token($url, $body);
        $headers = array("Authorization:$token");
        return $headers;
    }
    public function wslive_list($channelname, $startTime, $endTime, $bucket, $start=null, $limit=null)
    {
        $url = Config::WCS_MGR_URL.'/wslive/list';
        $query = "channelname=".$channelname."&startTime=".$startTime."&endTime=".$endTime."&bucket=".$bucket;
        if($start)
        {
            $tmp = '&start='.$start;
            $query.= $tmp;
        }
        if($limit)
        {
            $tmp = '&limit='.$limit;
            $query.=$tmp;
        }
        $url = $url.'?'.$query;
        $headers = $this->_generate_headers($url);
        $resp = $this->_get($url, $headers);
        return $resp;
    }
    private function _get($url, $headers=null,$body=null)
    {
        $resp = Utils::http_get($url, $headers, $body);
        return $resp;
    }
}
