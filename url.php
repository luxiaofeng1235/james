<?
$cli = new Swoole\Coroutine\Http\Client('127.0.0.1', 80);
$cli->setHeaders([
    'Host' => "localhost",
    "User-Agent" => 'Chrome/49.0.2587.3',
    'Accept' => 'text/html,application/xhtml+xml,application/xml',
    'Accept-Encoding' => 'gzip',
]);

$cli->get('/index.php');
echo $cli->body;
$cli->close();



?>