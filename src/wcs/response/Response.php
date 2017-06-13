<?php
/*
 * Response.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\response;

class Response
{
    const CODE_200 = 200;

    public function setCode($code) {
        $this->code = $code;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function setResponse($response) {
        $this->response = $response;

        $result = $this->parseResponse();
        $header = $result['header'];
        $data = $result['data'];
        if (isset($data['code']) && $data['code'] != 200 && isset($data['message'])) {
            $this->setCode($data['code']);
            $this->setMessage($data['message']);
        }
        else {
            $this->setCode(self::CODE_200);
        }

        $this->header = $header;
        $this->data = $data;
    }

    public function isSuccess() {
        return ($this->code == self::CODE_200);
    }

    public function getCode() {
        return $this->code;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getHeader() {
        $result = [];

        $headers = explode("\r\n", $this->header);
        foreach ($headers as $header) {
            $header = explode(':', $header);
            if (count($header) > 1) {
                $result[$header[0]] = trim($header[1]);
            }
        }

        return $result;
    }

    public function getData() {
        return $this->data;
    }

    private function parseResponse() {
        $result = [];

        $response = explode("\r\n\r\n", $this->response);
        if (count($response) == 1) {
            $result['header'] = null;
            $result['data'] = $response[0];
        }
        else {
            $count = count($response);
            $header = '';
            for ($i=0; $i<$count-1; $i++) {
                $header .= $response[$i];
            }

            $result['header'] = $header;
            $result['data'] = $response[$count-1];
        }

        $result['data'] = json_decode($result['data'], true);
        return $result;
    }

    private $code;
    private $message;
    private $response;
    private $header;
    private $data;
}