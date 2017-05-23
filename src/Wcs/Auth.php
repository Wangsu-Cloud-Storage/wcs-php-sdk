<?php

namespace Wcs;

class Auth
{

    public static function wcs_require_mac($mac)
    {
        if (isset($mac)) {
            return $mac;
        }

        return new Mac(Config::WCS_ACCESS_KEY, Config::WCS_SECRET_KEY );
    }

    public static function get_token($mac, $data)
    {
        return self::wcs_require_mac($mac)->get_token($data);
    }

    public static function get_token_with_data($mac, $data)
    {
        return self::wcs_require_mac($mac)->get_token_with_data($data);
    }

    public static function get_file_stat_token($bucketName, $fileName) {
        $encodedEntry = Utils::url_safe_base64_encode($bucketName . ':' . $fileName);
        $encodedPath = '/stat/' . $encodedEntry . "\n";
        return self::wcs_require_mac(null)->get_token($encodedPath);
    }

    public static function get_file_delete_token($bucketName, $fileName) {
        $encodedEntry = Utils::url_safe_base64_encode($bucketName . ':' . $fileName);
        $encodedPath = '/delete/' . $encodedEntry . "\n";
        return self::wcs_require_mac(null)->get_token($encodedPath);
    }

    public static function get_src_manage_token($path, $body = null) {
        $signingStr = $path;
        if($body) {
            $signingStr .= $body;
        }
        return self::wcs_require_mac(null)->get_token($signingStr);
    }
}