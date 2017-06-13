<?php
/*
 * WcsHelper.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\helper;

class WcsHelper
{
    public static function urlsafeBase64Encode($uri) {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($uri));
    }

    public static function urlsafeBase64Decode($uri) {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $uri));
    }

    public static function buildAgent($sdkVersion) {
        $sdkInfo = "WCS PHP SDK /" .$sdkVersion . " (http://wcs.chinanetcenter.com/)";

        $systemInfo = php_uname("s");
        $machineInfo = php_uname("m");

        $envInfo = "($systemInfo/$machineInfo)";

        $phpVer = phpversion();

        return "$sdkInfo $envInfo PHP/$phpVer";
    }
}