<?php
/*
 * HttpClient.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\http;

use wcs\response\Response;

class HttpClient
{
    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

    /**
    * $method
    * $url
    * $header
    * $options
    *   agent
    *   tiemout
    *   tokenDeadline
    **/
    public function __construct($method, $url, $header, $options) {
        $this->method = $method;
        $this->url = $url;
        $this->header = $header;

        $this->agent = $options['agent'];
        $this->tiemout = $options['timeout'];
        $this->tokenDeadline = $options['tokenDeadline'];
    }

    public function send($params=[]) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_FILETIME, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        if ($this->header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }

        if ($this->method == self::HTTP_POST) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::HTTP_POST);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->tokenDeadline);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        $result = curl_exec($ch);var_dump($result);
        $response = new Response();

        $errCode = curl_errno($ch);
        if ($errCode) {
            $response->setCode($errCode);
            $response->setMessage(curl_error($ch));
            return $response;
        }

        $response->setResponse($result);
        return $response;
    }

    private $method;
    private $url;
    private $header;
    private $agent;
    private $timeout;
    private $tokenDeadline;
}