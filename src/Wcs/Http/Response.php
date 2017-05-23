<?php

namespace  Wcs\Http;

class Response {

    public $code; //响应状态码

    public $message; //响应信息，当响应内容为二进制数据流时返回响应信息

    public $respHeader; //响应头部

    public $respBody; //响应内容

}