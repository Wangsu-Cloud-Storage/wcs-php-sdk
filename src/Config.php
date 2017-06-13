<?php

return [
    // accessKey，参考SI安全管理-密钥管理AK
    'accessKey'         => 'db17ab5d18c137f786b67c490187317a0738f94a',

    // secretKey，参考SI安全管理-密钥管理SK
    'secretKey'         => 'b19958e080eb3b219628f50c20c9024f4ff1b140',

    // 上传域名，参考SI安全管理-域名查询-上传域名
    'uploadUrl'         => 'http://apitestuser.up0.v1.wcsapi.com',

    // 管理域名，参考SI安全管理-域名查询-管理域名
    'manageUrl'         => 'http://apitestuser.mgr0.v1.wcsapi.com',

    /** 空间域名，如未配置空间域名不用配置
    * eg: [
    *   'myBucket' => 'http://mydoamin.com'
    * ]
    **/
    'bucketDomains'      => [],

    // token过期时间，默认3600秒
    'tokenDeadline'     => 3600,

    // 请求超时时间，默认30秒
    'timeout'           => 30,
];