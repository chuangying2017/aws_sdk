<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\Result;
use GuzzleHttp\Handler\MockHandler;
use Psr\Http\Message\RequestInterface;
use Swoole\Coroutine as co;
co::set(['hook_flags' => SWOOLE_HOOK_CURL]);
function upload()
{
    $im = new Imagick('./scenery.jpg');//image file upload
    $config = new Aws\Retry\Configuration('adaptive', 2);
    $mock = new MockHandler();
// Return a mocked result

    $s3 = new \Aws\S3\S3Client([
        'retries' => $config,
        'version' => 'latest',
        'region'  => 'us-west-2',
        'validate' => false,
        'endpoint'    => 'http://ss.bscstorage.com',
        'credentials' => array(
            'key'    => 'peugk654v12cljox3wdr',
            'secret' => 'oaN6z/X711jii4kvschiUqb2rjYmCbINdfxpfobM',
        ),
        'debug'   => [
            'logfn'        => function ($msg) { echo $msg . "\n"; },
            'stream_size'  => 0,
            'scrub_auth'   => true,
            'http'         => true,
            'auth_headers' => [
                'X-My-Secret-Header' => '[REDACTED]',
            ],
            'auth_strings' => [
                '/SuperSecret=[A-Za-z0-9]{20}/i' => 'SuperSecret=[REDACTED]',
            ],
        ]
    ]);
    $key = md5(rand(1,9999999).time().rand(1,9999999));
    $result = $s3->putObject([
        'Expires'     => date('D, d M Y H:i:s', (time() + 3600)) . ' GMT',
        'ACL'         => 'public-read',
        'Bucket'      => 'cloudpush',
        'Key'         => $key,

        'SourceFile'        => './scenery.jpg'
    ]);
    var_dump($result);
}
$pm = new Swoole\Process\ProcessManager();
$pm->add(function($pool, $workerId){
    //尝试请求 上传文件
    upload();
    while (true)
    {
        co::sleep(1.0);
    }
},true);

$pm->start();