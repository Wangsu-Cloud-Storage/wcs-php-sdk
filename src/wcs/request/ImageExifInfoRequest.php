<?php
/*
 * ImageExifInfoRequest.php
 *
 * created by laihy 2017年4月28日
 */

namespace wcs\request;

class ImageExifInfoRequest extends DownloadRequest
{
    public function getOp() {
        return 'exif';
    }
}