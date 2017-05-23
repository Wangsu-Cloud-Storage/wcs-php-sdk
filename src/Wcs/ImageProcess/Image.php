<?php

namespace Wcs\ImageProcess;

use Wcs\Utils;

class Image {

    public static function GET($url, $localFile) {

        //发送请求
        $resp = Utils::http_get($url, null);

        //返回成功状态码，保存图片
        if((int)($resp->code / 100) == 2) {

            //保存文件
            $file = fopen($localFile, "w");
            fwrite($file, $resp->respBody, strlen($resp->respBody));
            fclose($file);
            $resp->message = "图片下载完成！";
        }
        else {
            if($resp->code == 28) {
                $resp->message = '请求超时！';
            }
            else {
                die($resp->respBody."\n");
            }
        }

        return $resp;
    }
}

