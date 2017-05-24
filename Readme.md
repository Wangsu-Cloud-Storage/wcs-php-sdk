#wcs-php-sdk


这是php SDK基于网宿云API规范构建，（php环境满足php >= 5.6）。

*   [安装]()
*   [使用指南]()
*   *   [准备开发环境](#3)
    *   [配置信息](#4)
    *   [使用范例](#5)
    *   [文件上传](#6)
    *   [资源管理](#7)
    *   [图片处理](#8)
    *   [音视频操作](#9)
    *   [Fmgr](#10)


###安装

1.通过composer管理项目依赖

```
"require": {
     "wcs/wcs-sdk-php": "~2.0"
 }
```

2.手动下载
[wcs-php-sdk下载链接](http://localhost/link)

##<span id="3">使用指南</span>
###准备开发环境
*   php版本满足 php >= 5.5

###<span id="4">配置信息</span>
要接入网宿云存储，您需要拥有一对有效的AK和SK进行签名认证，填写上传、管理域名信息进行文件操作，您只需要在整个应用程序中初始化一次信息即可。可以通过如下步骤：

*   开通网宿云存储平台账户
*   登录网宿云存储平台，在“安全管理”下的“密钥管理”查看AK和SK，“域名查询”查看上传、管理域名。

在获取到AK和SK等信息之后，您可以按照如下方式进行信息初始化：

    /*Config.php*/

    //相关url设置
    $WCS_PUT_URL    = 'your uploadDomain';
    $WCS_GET_URL	= 'your downloadDomain';
    $WCS_MGR_URL	= 'your mgrDomain';

    //access key 和 secret key 设置
    $WCS_ACCESS_KEY	= 'your access key';
    $WCS_SECRET_KEY	= 'your secrete key';

    //token的deadline,默认是1小时,也就是3600s
    const  WCS_TOKEN_DEADLINE = 3600;

    //上传文件设置
    const WCS_OVERWRITE = 0; //默认文件不覆盖

    //超时时间
    const WCS_TIMEOUT = 20;

    //分片上传参数设置
    const WCS_BLOCK_SIZE = 4 * 1024 * 1024; //默认块大小4M
    const WCS_CHUNK_SIZE = 256 * 1024; //默认片大小256K
    const WCS_RECORD_URL = './'; //默认当前文件目录
    const WCS_COUNT_FOR_RETRY = 3;  //超时重试次数




### 计算文件etag值

wcs-php-sdk提供了计算文件etag值的工具，用户通过命令行的形式体验这个功能


    cd wcs-php-sdk/src/Wcs
    php wcs_etag.php
    Usage: php wcs_etag.php <filename>

    php wcs_etag.php filepath
    FmJn08Pu-3ZM1hNHlPH052sS-Zyp

etag计算算法

    算法大体如下：
    1. 如果你能够确认文件 <= 4M，那么 hash = UrlsafeBase64([0x16, sha1(FileContent)])。也就是，文件的内容的sha1值（20个字节），前面加一个      byte（值为0x16），构成 21 字节的二进制数据，然后对这 21 字节的数据做 urlsafe 的 base64 编码。
    2. 如果文件 > 4M，则 hash = UrlsafeBase64([0x96, sha1([sha1(Block1), sha1(Block2), ...])])，其中 Block 是把文件内容切分为 4M 为单位的一个个块，也就是 BlockI = FileContent[I*4M:(I+1)*4M]。


###<span id="6">文件上传</span>
1.  普通上传
2.  回调上传
3.  通知上传
4.  分片上传

使用文件上传类前需先加入引导脚本和命名空间

    require '../vendor/autoload.php';
    use Wcs\Upload\Uploader;

然后实例化Uploader或者ResumeUploader

    //userParam 自定义变量名    <x:VariableName>    (可选）
    //userVars  自定义变量值    <x:VariableValue>   (可选）
    //mimeType  自定义上传类型  (可选）

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $token = $pp->get_token();

    $client = new Uploader($token, $userParam, $userVars, $mimeType);

    $client = new ResumeUploader($token, $userParam, $userVars, $mimeType);


#####上传进度信息
普通上传、回调上传、通知上传均是一次性上传

####1.  普通上传(POST方式上传)

上传返回结果由云存储平台统一控制，规范统一化，详见网宿云存储API规范。 注意：

<1> 在表单上传的时候，可以开启returnurl，进行页面跳转；其他情况下建议不要设置returnurl。

<2> 若文件大小超过20M，建议使用分片上传。

*   如果用户指定上传策略数据的returnUrl，网宿云存储将反馈一个指向returnUrl的HTTP 303，驱动客户端执行跳转；
*   如果用户没指定上传策略数据的returnUrl，网宿云存储根据returnbody的设定向客户端发送反馈信息。

范例：

    //bucketName 空间名称
    //fileKey   自定义文件名
    //localFile 上传文件名
    //returnBody    自定义返回内容  (可选）
    //userParam 自定义变量名    <x:VariableName>    (可选）
    //userVars  自定义变量值    <x:VariableValue>   (可选）
    //mimeType  自定义上传类型  (可选）

    require '../vendor/autoload.php';
    use Wcs\Upload\Uploader;
    use Wcs\Http\PutPolicy;
    use Wcs\Config;

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $token = $pp->get_token();
    $client = new Uploader($token, $userParam, $userVars, $mimeType);
    $res = $client->upload_return($localFile);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_upload_return.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> [-r <returnBody>] [-u <userParam>] [-v <userVars>] [-m <mimeType>]

#####比如：
    $ php file_upload_return.php -b test -f test.png -l test.png -r {test}

####2.  回调上传（POST方式）
上传文件后，对返回给客户端的信息进行自定义格式时，详见网宿云存储API规范。需要启用上传策略数据的callbackUrl参数,而callbackBody参数可选（建议使用该参数）。
>注意：returnUrl和callbackUrl不能同时指定。

*   如果指定了callbackBody参数，云存储将接收此参数，并向callbackUrl指定的地址发起一个HTTP请求回调业务服务器，同时向业务服务器发送数据。发送的数据内容由callbackBody指定。业务服务器完成回调的处理后，可以在HTTP Response中放入数据，网宿云存储会响应客户端，并将业务服务器反馈的数据发送给客户端。
*   如果不指定callbackBody参数，云存储将返回空串给客户端。

范例：

    //bucketName 空间名称
    //fileKey   自定义文件名
    //localFile 上传文件名
    //callbackUrl 回调url
    //callbackBody 回调内容 （可选）

    require '../vendor/autoload.php';
    use Wcs\Upload\Uploader;
    use Wcs\Http\PutPolicy;
    use Wcs\Config;

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $pp->callbackUrl = $callbackUrl;
    $pp->callbackBody = $callbackBody;
    $token = $pp->get_token();

    $client = new Uploader($token, $userParam, $userVars, $mimeType);
    $res = $client->upload_return($localFile);
    print_r($res->code." ".$res->respBody);


命令行测试

    $ php file_upload_callback.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> -c <callbackUrl> [-r <returnBody>] [-u <userParam>] [-v <userVars>] [-m <mimeType>]

####3.  通知上传 （POST方式）
用户在上传文件时，提交文件处理指令（包括视频转码，图片水印，图片缩放等操作），要求云存储平台对上传的文件进行处理，由于这些处理操作一般比较耗费时间，为了不影响客户端的体验，云存储平台采用异步处理策略，处理过程异步执行，处理完成结果将采用异步通知方式告知企业的WEB服务系统，由企业的WEB系统再与客户端进行交互，完成处理通知整个流程，详见网宿云存储API规范。需要启用上传策略数据的persistentOps参数和persistentNotifyUrl参数。

范例：

    //bucketName 空间名称
    //fileKey   自定义文件名
    //localFile 上传文件名
    //notifyUrl 获取处理结果的url
    //cmd   处理命令，详细见wcs的文档

    require '../vendor/autoload.php';
    use Wcs\Upload\Uploader;
    use Wcs\Http\PutPolicy;
    use Wcs\Config;

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $pp->persistentOps = $cmd;
    $pp->persistentNotifyUrl = $notifyUrl;
    $pp->returnBody = $returnBody;
    $token = $pp->get_token();

    $client = new Uploader($token, $userParam, $userVars, $mimeType);
    $res = $client->upload_return($localFile);
    print_r($res->code." ".$res->respBody);


命名行测试

    $ php file_upload_notify.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> -n <notifyUrl> -c <cmd> [-u <userParam>] [-v <userVars>] [-m <mimeType>]


####4.  流地址上传 （POST方式）
用户在上传文件时，提交文件的流地址，SDK通过流地址获取文件二进制流，然后通过mulitpart/form形式上传。

在上传之前需要配置虚拟内存磁盘：

    mount -t tmpfs -o size=1g tmpfs /mnt/ramdisk

挂载如下：

    Filesystem      Size  Used Avail Use% Mounted on
    tmpfs           1.0G  2.1M 1022M   1% /mnt/ramdisk

将挂载目录写入配置文件Config.php：

    const WCS_RAM_URL = '/mnt/ramdisk/';

范例：

    //bucketName 空间名称
    //fileKey   自定义文件名
    //Stream 文件流地址


    require '../vendor/autoload.php';
    use Wcs\Upload\StreamUploader;
    use Wcs\Http\PutPolicy;
    use Wcs\Config;

    $bucketName = '';
    $fileKey = '';
    $stream = '';

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $token = $pp->get_token();

    $client = new StreamUploader($token);
    $res = $client->upload_return($stream);
    print_r($res->code." ".$res->respBody);


命名行测试

    $ php file_upload_stream.php


####    4.  分片上传 （POST方式）

分片上传支持并发上传，文件先分块，块分片，按照片的力度进行上传。
块之间进行并发，片之间不支持并发，块上传大致流程如下：

*   mkblk   (每一块上传前必须先mkblk操作，服务器返回第一片ctx)
*   bput    (mkblk之后进行bput操作，上传每一片附带上一片的ctx并返回当前的ctx)
*   mkfile  (当文件上传完毕，进行mkfile操作，附带每一块的最有一片ctx信息)

分片上传需要注意的点有：

>   1. 分片上传默认在上传请求发起超时情况下会自动重传，其他情况下（状态码非28）直接报错退出，并将错误信息保存在当前目录的`.文件名.log`的隐藏文件下面。
>   2. 上传中断后，上传信息会保存在隐藏文件`.文件名.rcd`下面，每一条记录为片上传的信息，断点续传会从记录的最后一条信息分析当前上传的状态，并进行后续上传。上传成功后，会删除该记录文件。
>   3. 用户想要进行断点续传，只需要重新执行一次分片上传操作。
>   4. 分片上传只在块内作并发，而且是异步回调并发而非多线程并发，考虑到php对多线程操作的支持不是很好，因此采用异步回调的机制，用guzzlehttp实现。
>   5. 默认块的大小是4M，片的大小256K,这样的目的是为了更稳定的上传，若客户觉得上传速度过慢，想要提高上传速度，只需调整块或片的大小，这样能提升上传的速度（相对来说，上传稳定性可能会降低）
>   6. 由于有超时重传策略（默认重传3次）来保证传输的可靠性，因此客户如果希望提高上传速度，可将片的大小改为块的大小，保证最大并发数，提高上传速度。
>   7. 分片上传进度信息在`.文件名.rcd`下面，以json的格式保存。每上传一片都会写入一条json记录，进度信息保存在`$json['info']['progress']`这个字段里,客户可根据需要处理该进度信息。
>   8. 上传成功将删除 `.文件名.rcd`文件和`.文件名.log`文件.

文件`.文件名.rcd`字段说明:

    {
        "info": {
            "sizeofUploaded": "已经上传的文件大小",
            "sizeOfFile": "文件大小",
            "progress": "上传的进度",
            "uuid": "上传的uuid",
            "token": "上传的token",
            "time": "token生成的时间,用于检验token超时",
            "ctxList": "mkfile需要的ctxlist"
        },
        "0": {
            "success": "当前块是否已经上传成功",
            "blockSize": "块的大小",
            "curChunkSize": "当前片的大小",
            "chuckNum": "当前块中片的数量",
            "chunk": "已经上传的片数",
            "uploaded": "块已经上传的大小",
            "latestCtx": "最新的ctx的值",
            "retry": "超时需要重试的次数"
        },
        "1": {
            ...
        }
        ...
    }




变量说明

    //基本信息
    private $blockSize;
    private $chunkSize;
    private $countForRetry;
    private $timeoutForRetry;

    //用户自定义信息
    private $userParam;
    private $encodedUserVars;
    private $mimeType;

    //uuid随机数用 php 的uniqid()
    private $uuid;

    //断点续传记录文件
    private $recordFile;

    //断点续传信息
    private $localFile;
    private $blockNumOfUploaded;    //已经上传的块数量
    private $chunkNumOfUploaded;    //当前块已经上传的片数量
    private $ctxListForMkfile;  //mkfile操作需要的每一块最后一片ctx
    private $sizeOfFile;    //文件大小
    private $sizeOfUploaded;    //已经上传的文件大小
    private $time;  //token生成的时间，用来检验token是否失效


范例：

    //bucketName 空间名称
    //fileKey   自定义文件名
    //localFile 上传文件名

    require '../vendor/autoload.php';
    use Wcs\Upload\ResumeUploader;
    use Wcs\Http\PutPolicy;
    use Wcs\Config;

    $pp = new PutPolicy();
    if ($fileKey == null || $fileKey === '') {
        $pp->scope = $bucketName;
    } else {
        $pp->scope = $bucketName . ':' . $fileKey;
    }

    $pp->persistentOps = $cmd;
    $pp->persistentNotifyUrl = $notifyUrl;
    $pp->returnBody = $returnBody;
    $token = $pp->get_token();

    $client = new ResumeUploader($token, $userParam, $encodeUserVars, $mimeType);
    $res = $client->upload($localFile);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_upload_resume.php [-h | --help] -b <bucketName> -f <fileKey> -l <localFile> [-u <userParam>] [-v <encodeUserVars>] [-m <mimeType>]

#####分片上传成功返回结果

    {"key":"test0.mp4","hash":"lrV8ZZRKgjHAE0JX6Y8iXxU3x0eJ"}

#####请求失败

    {
        "code":     "<code string>",
        "message":  "<message string>"
    }


###<span id="7">资源管理</span>
提供对文件的基本操作：
1.   删除文件
2.   获取文件信息
3.   列举资源
4.   更新镜像资源
5.   移动资源
6.   复制资源
7.   获取音视频元数据
8.   获取音视频简单元数据


####1.  删除文件
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    print_r($client->delete($bucketName, $fileKey));

命令行测试

    $ php file_delete.php [-h | --help] -b <bucketName> -f <fileKey>


####2.  获取文件信息
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->stat($bucketName, $fileKey);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_stat.php [-h | --help] -b <bucketName> -f <fileKey>

####3.  列举资源
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->bucketList($bucketName, $limit, $prefix, $mode, $marker));
    print_r($res->code." ".$res->respBody);


命令行测试

    $ php file_list.php [-h | --help] -b <bucketName> [-l <limit>] [-p <prefix>] [-m <mode>] [--ma <marker>]

####4.  更新镜像资源
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    //fileKeys = "<fileKey1>|<fileKey2>|<fileKey3>";
    $client = new FileManager($auth);
    $res = $client->updateMirrorSrc($bucketName, $fileKeys);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php updateMirrorSrc.php [-h | --help] -b <bucket> -f [<fileKey1>|<fileKey2>|<fileKey3>...]

####5.  移动资源
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->move($bucketSrc, $keySrc, $bucketDst, $keyDst);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_move.php [-h | --help] --bs <bucketSrc> --ks <keyStr> --bd <bucketDst> --kd <keyDst>

####6.  复制资源
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->copy($bucketSrc, $keySrc, $bucketDst, $keyDst);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_copy.php [-h | --help] --bs <bucketSrc> --ks <keyStr> --bd <bucketDst> --kd <keyDst>


####7.  获取音视频元数据
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->avInfo($key));
    print_r($res->code." ".$res->respBody);

命令行测试

    php avinfo.php [-h | --help] -k <key>

####8.  获取音视频简单元数据
范例：

    require '../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->avInfo2($key);
    print_r($res->code." ".$res->respBody);


命令行测试

    $ php avinfo2.php [-h | --help] -k <key>

####9.  设置文件保存期限
范例：

    require '../../vendor/autoload.php';
    use \Wcs\SrcManage\FileManager;
    use \Wcs\MgrAuth;
    use \Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new FileManager($auth);
    $res = $client->setDeadline($bucketName, $fileKey, $deadline);
    print_r($res->code." ".$res->respBody);

命令行测试

    $ php file_setDeadLine.php [-h | --help] -b <bucketName> -f <fileKey> -d <deadline>


###<span id="8">直播录制文件处理</span>

####1.  直播录制文件查询
范例：

    require '../../vendor/autoload.php';
    use Wcs\Wslive\WsLive;
    use Wcs\MgrAuth;
    use Wcs\Config;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new WsLive($auth);
    $res = $client->wslive_list($channelname, $startTime, $endTime, $bucket, $start, $limit);
    print_r($res->code." ".$res->respBody);

命令行执行：

    php wslive_list.php [-h | --help] -c <channelname> -s <startTime> -e <endTime> -b <bucket> [-S <start>] [-L <limit>]

###<span id="8">图片处理</span>
图片处理的相关接口，主要有
1.   图片缩放
2.   图片水印
3.  文字水印
4.  高级图片处理
6.  获取图片基本信息
7.  获取图片EXIF信息

####1.  图片缩放

    require '../../vendor/autoload.php';
    use Wcs\ImageProcess\ImageView;

    $mode = 1;
    $client = new ImageView($mode);

     //可选参数
    //$client->quality = '';
    //$client->format = '';
    //$client->width = 200;
    //$client->height = 200;

    print_r($client->exec($bucketName, $fileName));

#####2. 图片水印

    require '../vendor/autoload.php';
    use Wcs\ImageProcess\ImageWatermark;

    //自定义参数
    //$mode = 1;

    $client = new ImageWatermark($mode);

    //可选参数
    //$client->width = 200;
    //$client->height = 200;
    //$client->image = '';
    //$client->dx = '';
    //$client->dy = '';
    //$client->gravity = '';
    //$client->dissolve = '';

    print_r($client->exec($bucketName, $fileName, $localFile));

####3.  文字水印

    require '../vendor/autoload.php';
    use Wcs\ImageProcess\ImageWatermark;

    //自定义参数
    //$mode = 2;
    //$text = 'test';

    $client = new ImageWatermark($mode, $text);

    // 可选参数
    //$client->dissolve = '';
    //$client->font = '';
    //$client->fontsize = 16;
    //$client->image = '';
    //$client->dx = '';
    //$client->dy = '';
    //$client->gravity = '';

    print_r($client->exec($bucketName, $fileName, $localFile));


####4.  高级图片处理

    require '../../vendor/autoload.php';
    use Wcs\ImageProcess\ImageMogr;

    $client = new ImageMogr();

    //可选参数，详见wcs api的文档说明
    $client->thumbnail = '!10p';

    print_r($client->exec($bucketName, $fileName));


####5.  获取图片基本信息

    require '../../vendor/autoload.php';
    use \Wcs\ImageProcess\ImageInfo;

    $client = new ImageInfo();
    $res = $client->imgInfo($bucketName, $fileName);
    print_r($res->code." ".$res->respBody);


####6.  获取图片EXIF信息

    require '../../vendor/autoload.php';
    use \Wcs\ImageProcess\ImageInfo;

    $client = new ImageInfo();
    $res = $client->imageEXIF($bucketName, $fileName);
    print_r($res->code." ".$res->respBody);

###<span id="9">持久化操作</span>
1.  fops操作
2.  fops查询

####1.  fops操作
    require '../../vendor/autoload.php';
    use \Wcs\PersistentFops\Fops;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //$fops的格式，不同的音视频操作对应不同的fops格式，详细见wcs api 文档

    $bucket = '<input key>';
    $key = '<input key>';

    //参数设置
    $notifyURL = '';
    $force = 0;
    $separate = 0;

    $fops = '';

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fops($auth, $bucket);

    $res = $client->exec($fops, $key, $notifyURL, $force, $separate);
    print_r($res->code." ".$res->respBody);

####2.  fops查询

    require '../../vendor/autoload.php';
    use \Wcs\PersistentFops\Fops;

    $res = Fops::status($persisetntId);
    print_r($res->code." ".$res->respBody);

###<span id="10">Fmgr操作</span>

*   抓取资源
*   复制资源
*   移动资源
*   删除资源
*   按前缀删除资源
*   fmgr任务查询

####1.  抓取资源

    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    //fops参数
    $fetchURL = \Wcs\url_safe_base64_encode('https://www.baidu.com/img/bd_logo1.png');
    $bucket = \Wcs\url_safe_base64_encode('<input key>');
    $key = \Wcs\url_safe_base64_encode('<input key>');
    $prefix = \Wcs\url_safe_base64_encode('<input key>');

    $fops = 'fops=fetchURL/'.$fetchURL.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.\Wcs\url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $res = $client->fetch($fops);
    print_r($res->code." ".$res->respBody);

####2.  复制资源

    // 请先填写相关字段,$fops字段格式详见wcs api 文档
    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    //fops参数
    $resource = \Wcs\url_safe_base64_encode('<input key>');
    $bucket = \Wcs\url_safe_base64_encode('<input key>');
    $key = \Wcs\url_safe_base64_encode('<input key>');
    $prefix = \Wcs\url_safe_base64_encode('<input key>');

    $fops = 'fops=resource/'.$resource.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.\Wcs\url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $res = $client->copy($fops);
    print_r($res->code." ".$res->respBody);

####3.   移动资源

    // 请先填写相关字段,$fops字段格式详见wcs api 文档
    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    //fops参数
    $resource = \Wcs\url_safe_base64_encode('<input key>');
    $bucket = \Wcs\url_safe_base64_encode('<input key>');
    $key = \Wcs\url_safe_base64_encode('<input key>');
    $prefix = \Wcs\url_safe_base64_encode('<input key>');

    $fops = 'fops=resource/'.$resource.'/bucket/'.$bucket.'/key/'.$key.'/prefix/'.$prefix.'&notifyURL='.\Wcs\url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $client->move($fops);
    print_r($res->code." ".$res->respBody);

####4.   删除资源

    // 请先填写相关字段,$fops字段格式详见wcs api 文档
    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    //fops参数
    $bucket = \Wcs\url_safe_base64_encode('<input key>');
    $key = \Wcs\url_safe_base64_encode('<input key>');

    $fops = 'fops=bucket/'.$bucket.'/key/'.$key.'&notifyURL='.\Wcs\url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $res = $client->delete($fops);
    print_r($res->code." ".$res->respBody);

####5.   按前缀删除资源


    // 请先填写相关字段,$fops字段格式详见wcs api 文档
    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    //fops参数
    $bucket = \Wcs\url_safe_base64_encode('<input key>');
    $prefix = \Wcs\url_safe_base64_encode('<input key>');
    $output = \Wcs\url_safe_base64_encode('<input key>');

    $fops = 'fops=bucket/'.$bucket.'/prefix/'.$prefix.'&notifyURL='.\Wcs\url_safe_base64_encode($notifyURL).'&force='.$force.'&separate='.$separate;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $res = $client->deletePrefix($fops);
    print_r($res->code." ".$res->respBody);

####6.  fmgr任务查询

    // 请先填写相关字段,$fops字段格式详见wcs api 文档
    require '../../vendor/autoload.php';
    use \Wcs\Fmgr\Fmgr;
    use \Wcs\Config;
    use \Wcs\MgrAuth;

    //可选参数
    $notifyURL = '';
    $force = 0;
    $separate  = 0;

    $ak = Config::WCS_ACCESS_KEY;
    $sk = Config::WCS_SECRET_KEY;
    $auth = new MgrAuth($ak, $sk);

    $client = new Fmgr($auth, $notifyURL, $force, $separate);
    $res = $client->status("<input persistentId>");
    print_r($res->code." ".$res->respBody);


