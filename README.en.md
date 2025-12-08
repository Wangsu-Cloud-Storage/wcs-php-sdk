# Cloud Storage PHP SDK

## 语言 / Language
- [简体中文](README.md)
- [English](README.en.md)

## Preparation
* Account requirements: NetEase Cloud Storage account with upload keys and upload domain name
* System requirements: PHP 5.4 or above

## Installation
1. Manage project dependencies via composer
```
"require": {
    "wangsucs/wcs-sdk-php": "^2.0.0"
}
```

2. You can also download the [PHP SDK](https://wcsd.chinanetcenter.com/sdk/cnc-php-sdk-wcs.zip) and import it manually
```
require_once __DIR__ . '/vendor/autoload.php';
```

3. PHP SDK includes concurrency mechanism, which may cause exceptions due to network issues. You can use the [simplified version](http://doc-pics.w.wcsapi.biz.matocloud.com/sdk/cnc-php-sdk-wcs-sample.zip) without concurrency mechanism.

## Initialization
When accessing NetEase Cloud Storage, you need to use a valid pair of AK and SK for signature authentication, and configure information such as "upload domain" and "management domain" for file operations. Configuration only needs to be initialized once in the entire application:
```
/*src/Wcs/Config.php*/
// Related URL settings
$WCS_PUT_URL    = 'your uploadDomain';
$WCS_GET_URL    = 'your downloadDomain';
$WCS_MGR_URL	= 'your mgrDomain';

// Access key and secret key settings
$WCS_ACCESS_KEY	= 'your access key';
$WCS_SECRET_KEY	= 'your secrete key';

// Token deadline, default is 1 hour (3600s)
const  WCS_TOKEN_DEADLINE = 3600;

// File upload settings
const WCS_OVERWRITE = 0; // Default: do not overwrite files

// Timeout settings
const WCS_TIMEOUT = 20;

// Chunk upload parameter settings
const WCS_BLOCK_SIZE = 4 * 1024 * 1024; // Default block size: 4M
const WCS_CHUNK_SIZE = 256 * 1024; // Default chunk size: 256K
const WCS_RECORD_URL = './'; // Default: current file directory
const WCS_COUNT_FOR_RETRY = 3;  // Timeout retry count
```

## Usage Guide
### Normal Upload
Normal upload uses form upload to directly upload files to NetEase Cloud Storage. It is recommended for files smaller than 20M. Normal upload supports custom callback notification content, webpage redirect addresses, and preprocessing.
* If the callbackBody parameter is specified, Cloud Storage will initiate an HTTP callback request to the business server address specified by callbackUrl. The business server completes business processing based on the callback content and customizes the response content in the HTTP Response. NetEase Cloud Storage receives this response and sends the feedback data to the client. If the callbackBody parameter is not specified, Cloud Storage will return an empty string to the client.
* If returnBody and returnUrl are specified, Cloud Storage will jump to the address specified by returnUrl after successful upload and carry the parameters specified by returnBody.
If you want to do preprocessing after successful upload, you can specify it through the persistentOps parameter, and persistentNotifyUrl can specify the notification address after successful processing.

**Example:**
```
//bucketName Bucket name
//fileKey   Custom file name
//localFile Upload file name
//returnBody    Custom return content  (optional）
//userParam Custom variable name    <x:VariableName>    (optional）
//userVars  Custom variable value    <x:VariableValue>   (optional）
//mimeType  Custom upload type  (optional）

require '../vendor/autoload.php';
use Wcs\Upload\Uploader;
use Wcs\Http\PutPolicy;

$pp = new PutPolicy();
if ($fileKey == null || $fileKey === '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}

// Notify upload
$pp->returnBody = '';
$pp->returnUrl = '';

// Callback upload
$pp->callbackBody = '';
$pp->callbackUrl = '';

// Preprocessing
$pp->persistentOps = '<cmd>';

// Token validity period, can customize token validity deadline, value is millisecond timestamp; if not specified, it will be calculated according to the validity period configured by WCS_TOKEN_DEADLINE
$pp->deadline = '<timestamp>'; 
$token = $pp->get_token();

$client = new Uploader($token, $userParam, $userVars, $mimeType);
$resp = $client->upload_return($localFile);
print_r($resp);
```
**Command Line Test**
```
$ php file_upload_return.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> [-r <returnBody>] [-u <userParam>] [-v <userVars>] [-m <mimeType>]

$ php file_upload_callback.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> -c <callbackUrl> [-r <returnBody>] [-u <userParam>] [-v <userVars>] [-m <mimeType>]

$ php file_upload_notify.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> -n <notifyUrl> -c <cmd> [-u <userParam>] [-v <userVars>] [-m <mimeType>]
```
### Chunk Upload
Chunk upload process:
1. mkblk (Each block must be mkblk operated before upload, server returns the first chunk ctx)
2. bput (After mkblk, perform bput operation, upload each chunk with the previous chunk's ctx and return the current ctx)
3. mkfile (When file upload is complete, perform mkfile operation with the last chunk ctx of each block)

**Notes:**

* Chunk upload will automatically retry by default when request timeout occurs. In other cases (status code is not 28), it will report an error and exit, and save the error information in a hidden file .filename.log in the current directory.
* After upload interruption, upload information is saved in the hidden file .filename.rcd. Each record is chunk upload information. Resume upload will analyze the current upload status from the last record of the record and proceed with subsequent uploads. After successful upload, the record file will be deleted.
* To resume upload, you only need to re-execute the chunk upload operation.
* Chunk upload is only concurrent within blocks, and it is asynchronous callback concurrency rather than multi-threaded concurrency. Considering that PHP's support for multi-threaded operations is not very good, an asynchronous callback mechanism is used, implemented with guzzlehttp.
* The default block size is 4M and the chunk size is 256K. This is to ensure more stable upload. If customers feel the upload speed is too slow and want to improve the upload speed, they only need to adjust the block or chunk size, which can increase the upload speed (relatively, upload stability may decrease).
* Due to the timeout retry strategy (default 3 retries) to ensure transmission reliability, if customers want to improve upload speed, they can change the chunk size to the block size to ensure the maximum concurrency and improve upload speed.
* Chunk upload progress information is under .filename.rcd, saved in json format. Each uploaded chunk will write a json record. The progress information is saved in the $json['info']['progress'] field. Customers can process this progress information as needed.
* After successful upload, the .filename.rcd file and .filename.log file will be deleted.

**Variable Description:**
```
// Basic information
private $blockSize;
private $chunkSize;
private $countForRetry;
private $timeoutForRetry;

// User-defined information
private $userParam;
private $encodedUserVars;
private $mimeType;

// uuid random number uses php's uniqid()
private $uuid;

// Resume upload record file
private $recordFile;

// Resume upload information
private $localFile;
private $blockNumOfUploaded;    // Number of uploaded blocks
private $chunkNumOfUploaded;    // Number of uploaded chunks in current block
private $ctxListForMkfile;  // Last chunk ctx of each block needed for mkfile operation
private $sizeOfFile;    // File size
private $sizeOfUploaded;    // Uploaded file size
private $latestChunkCtx;    // Latest ctx
private $time;  // Token generation time, used to check if token is invalid
```

**Example:**
```
//bucketName Bucket name
//fileKey   Custom file name
//localFile Upload file name

require '../vendor/autoload.php';
use Wcs\Upload\ResumeUploader;
use Wcs\Http\PutPolicy;

$pp = new PutPolicy();
if ($fileKey == null || $fileKey === '') {
    $pp->scope = $bucketName;
} else {
    $pp->scope = $bucketName . ':' . $fileKey;
}

// Token validity period, can customize token validity deadline, value is millisecond timestamp; if not specified, it will be calculated according to the validity period configured by WCS_TOKEN_DEADLINE
$pp->deadline = '<timestamp>'; 

$pp->persistentOps = $cmd;
$pp->persistentNotifyUrl = $notifyUrl;
$pp->returnBody = $returnBody;
$token = $pp->get_token();

$client = new ResumeUploader($token, $userParam, $encodeUserVars, $mimeType);
$client->upload($localFile);
```
### Resource Management
Provides basic operations for files

#### Delete File

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->delete($bucketName, $fileKey));
```

**Command Line Test**
```
$ php file_delete.php [-h | --help] -b <bucketName> -f <fileKey>
```

#### Get File Information

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->stat($bucketName, $fileKey));
```

**Command Line Test**
```
$ php file_stat.php [-h | --help] -b <bucketName> -f <fileKey>
```

#### List Resources
**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->bucketList($bucketName, $limit, $prefix, $mode, $marker));
```

**Command Line Test**
```
$ php file_download.php [-h | --help] -b <bucketName> [-l <limit>] [-p <prefix>] [-m <mode>] [--ma <marker>]
```

#### Update Mirror Resources

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

//fileKeys = "<fileKey1>|<fileKey2>|<fileKey3>";
$client = new FileManager($auth);
print_r($client->updateMirrorSrc($bucketName, $fileKeys));
```

**Command Line Test**
```
   $ php file_stat.php [-h | --help] -b <bucket> -f [<fileKey1>|<fileKey2>|<fileKey3>...]
```

#### Move Resources

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->move($bucketSrc, $keySrc, $bucketDst, $keyDst));
```

**Command Line Test**
```
$ php file_move.php [-h | --help] --bs <bucketSrc> --ks <keyStr> --bd <bucketDst> --kd <keyDst>
```

#### Copy Resources

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->copy($bucketSrc, $keySrc, $bucketDst, $keyDst));
```

**Command Line Test**
```
$ php file_copy.php [-h | --help] --bs <bucketSrc> --ks <keyStr> --bd <bucketDst> --kd <keyDst>
```

#### Get Audio/Video Metadata

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->avInfo($key));
```

**Command Line Test**
```
php avinfo.php [-h | --help] -k <key>
```

#### Get Simple Audio/Video Metadata

**Example**
```
require '../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->avInfo2($key));
```

**Command Line Test**
```
$ php avinfo2.php [-h | --help] -k <key>
```

#### Set File Retention Period
**Example:**
```
require '../../vendor/autoload.php';
use Wcs\SrcManage\FileManager;
use Wcs\MgrAuth;
use Wcs\Config;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new FileManager($auth);
print_r($client->setDeadline($bucketName, $fileKey, $deadline));
```

**Command Line Test**
```
$ php file_setDeadLine.php [-h | --help] -b <bucketName> -f <fileKey> -d <deadline>
```

#### Audio/Video Operations
##### fops Operation
```
require '../../vendor/autoload.php';
use Wcs\PersistentFops\Fops;
use Wcs\Config;
use Wcs\MgrAuth;

//$fops format, different audio/video operations correspond to different fops formats, see wcs api documentation for details

$bucket = '<input key>';
$key = '<input key>';

// Parameter settings
$notifyURL = '';
$force = 0;
$separate = 0;

// Transcoding command, such as repackaging to mp4
$fops = 'avthumb/mp4|saveas/YnVja2V0TmFtZTprZXlOYW1l';

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fops($auth, $bucket);
print_r($client->exec($fops, $key, $notifyURL, $force, $separate));
```

##### fops Query
```
require '../../vendor/autoload.php';
use Wcs\PersistentFops\Fops;
print_r(Fops::status($persisetntId));
```

#### Advanced Resource Management
Supports asynchronous resource management operations on files
1. Fetch resources
2. Copy resources
3. Move resources
4. Delete resources
5. Delete resources by prefix
6. fmgr task query

##### Fetch Resources
```
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$fetchURL = Utils::url_safe_base64_encode('https://www.baidu.com/img/bd_logo1.png');
$bucket = Utils::url_safe_base64_encode('<input key>');
$key = Utils::url_safe_base64_encode('<input key>');
$prefix = Utils::url_safe_base64_encode('<input key>');

$fops = 'fops=fetchURL/'.$fetchURL.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->fetch($fops));
```

##### Copy Resources
```
// Please fill in the relevant fields first, $fops field format see wcs api documentation
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$resource = Utils::url_safe_base64_encode('<input key>');
$bucket = Utils::url_safe_base64_encode('<input key>');
$key = Utils::url_safe_base64_encode('<input key>');
$prefix = Utils::url_safe_base64_encode('<input key>');

$fops = 'fops=resource/'.$resource.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->copy($fops));
```

##### Move Resources
```
// Please fill in the relevant fields first, $fops field format see wcs api documentation
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$resource = Utils::url_safe_base64_encode('<input key>');
$bucket = Utils::url_safe_base64_encode('<input key>');
$key = Utils::url_safe_base64_encode('<input key>');
$prefix = Utils::url_safe_base64_encode('<input key>');

$fops = 'fops=resource/'.$resource.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->move($fops));
```

##### Delete Resources
```
// Please fill in the relevant fields first, $fops field format see wcs api documentation
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$bucket = Utils::url_safe_base64_encode('<input key>');
$key = Utils::url_safe_base64_encode('<input key>');

$fops = 'fops=bucket/'.$bucket.'/key/'.$key.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->delete($fops));
```

##### Delete Resources by Prefix
```
// Please fill in the relevant fields first, $fops field format see wcs api documentation
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$bucket = Utils::url_safe_base64_encode('<input key>');
$prefix = Utils::url_safe_base64_encode('<input key>');
$output = Utils::url_safe_base64_encode('<input key>');

$fops = 'fops=bucket/'.$bucket.'/prefix/'.$prefix.'&notifyURL='.Utils::url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->deletePrefix($fops));
```
##### Multi-File Compression
```
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;
use Wcs\Utils;


// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

// fops parameters
$bucket = Utils::url_safe_base64_encode('<input key>');
$keys = Utils::url_safe_base64_encode('<input key1>').'|'.Utils::url_safe_base64_encode('<input key2>');
$keyList = Utils::url_safe_base64_encode('<input keylist>');
$targetSource = Utils::url_safe_base64_encode('<bucket:key>');


// When $keys and $keyList exist at the same time, $keyList is invalid
if ($keys) {
    $fops = 'fops=bucket/'.$bucket.'/keys/'.$keys.'/keylist/'.$keyList.'/saveas/'.$targetSource;
}

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;

$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
$res = $client->compress($fops);
print_r($res->code." ".$res->respBody);
print_r("\n");
```

##### fmgr Task Query
```
// Please fill in the relevant fields first, $fops field format see wcs api documentation
require '../../vendor/autoload.php';
use Wcs\Fmgr\Fmgr;
use Wcs\Config;
use Wcs\MgrAuth;

// Optional parameters
$notifyURL = '';
$force = 0;
$separate  = 0;

$ak = Config::WCS_ACCESS_KEY;
$sk = Config::WCS_SECRET_KEY;
$auth = new MgrAuth($ak, $sk);

$client = new Fmgr($auth, $notifyURL, $force, $separate);
print_r($client->status("<input persistentId>"));
```

### etag Calculation
Calculate the etag of a local file through the method provided by the SDK, which can be compared with the etag returned by the server when uploading successfully to verify the integrity of the uploaded file.
```
wcs_etag.GetEtag($localFile)
```

---

[Switch to Chinese](README.md)