<?

/**
 * curl请求类多线程，支持get和post请求
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
class Http
{


   public static function rolling_curl($urls,$custom_options = null){//多个url访问
        if (sizeof($urls)==0) return;
        // make sure the rolling window isn't greater than the # of urls
        $rolling_window = 8;
        $rolling_window = (sizeof($urls) < $rolling_window) ? sizeof($urls) : $rolling_window;
        $master   = curl_multi_init();
        $curl_arr = array();
        // add additional curl options here
        $std_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HEADER  => 0,
            CURLOPT_ENCODING    =>  'gzip',
        );

        $std_options[CURLOPT_SSL_VERIFYPEER] = FALSE;
        $std_options[CURLOPT_SSL_VERIFYHOST] = FALSE;
        $std_options[CURLOPT_HTTPHEADER] =array(
            'Content-Type: application/json'
        );
        $options = ($custom_options) ? ($std_options + $custom_options) : $std_options;
        // start the first batch批 of requests
        for ($i = 0; $i < $rolling_window; $i++) {
            $ch = curl_init();
            $options[CURLOPT_URL] = $urls[$i];
            $options[CURLOPT_REFERER] = $urls[$i];
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1');
             //设置头部
            // curl_setopt($ch, CURLOPT_REFERER, $url); //设置来源
            curl_setopt_array($ch, $options);
            curl_multi_add_handle($master, $ch); //添加对象
        }
        $data =[];
        do {

            while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM); //执行
            if ($execrun != CURLM_OK) {
                break;
            }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($master)) {
                $info = curl_getinfo($done['handle']);
                if ($info['http_code'] == 200) {
                    $output = curl_multi_getcontent($done['handle']); //获取结果
                    $data[] =$output;

                    // request successful.  process output using the callback function.
                    //这里写成功后的操作

                    // 把请求已经完成了得 curl handle 删除
                    curl_multi_remove_handle($master, $done['handle']);
                    // start a new request (it's important to do this before removing the old one)
                    }
                if($i<sizeof($urls)){
                    $ch                   = curl_init();
                    //$options[CURLOPT_POST] = true;
                    //$options[CURLOPT_POSTFIELDS] = json_encode($postData[$i]);
                    $options[CURLOPT_URL] = $urls[$i++]; // increment i
                    curl_setopt_array($ch, $options);
                    curl_multi_add_handle($master, $ch);
                }
                // remove the curl handle that just completed
                curl_multi_remove_handle($master, $done['handle']);
            } //while
            // 当没有数据的时候进行堵塞，把 CPU 使用权交出来，避免上面 do 死循环空跑数据导致 CPU 100%
            if ($running) {
                $rel = curl_multi_select($master, 1);
                if($rel == -1){
                    usleep(1000);
                }
            }
        } while ($running);
        curl_multi_close($master);
        return $data;
    }


    /**
     * https 发起post请求
     * @param string $url url信息
     * @param mixed $data 参数信息[$data = '{"a":1,"b":2}' or $data = array("a" => 1,"b" => 2)]
     * @param int $timeOut 超时设置
     * @param string $proxyHost 代理host
     * @param int $proxyPort 代理端口
     * @return string
     */
    public static function post($url, $data = null, $timeOut = 20, $proxyHost = null, $proxyPort = null)
    {
        try {
            if (strlen($url) < 1) {
                return null;
            }

            $ch = curl_init();
            // 设置url
            curl_setopt($ch, CURLOPT_URL, $url);
            if (false == empty($data)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                if (is_array($data) && count($data) > 0) {
                    curl_setopt($ch, CURLOPT_POST, count($data));
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            // 如果成功只将结果返回，不自动输出返回的内容
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // user-agent
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
            // 超时
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);

            // 使用代理
            if (strlen($proxyHost) > 0 && strlen($proxyPort) > 0) {
                // 代理认证模式
                curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                // 代理服务器地址
                curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
                // 代理服务器端口
                curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
                // 使用http代理模式
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            }

            // 执行
            $out = curl_exec($ch);
            // 关闭
            curl_close($ch);
            return $out;
        } catch (Exception $e) {
            return null;
        }

    }


    /**
     * https 发起post多发请求
     * @param array $nodes url和参数信息。
     * $nodes = [
     * [0] = > [
     * 'url' => 'http://www.baidu.com',
     * 'data' => '{"a":1,"b":2}'
     * ],
     * [1] = > [
     * 'url' => 'http://www.baidu.com',
     * 'data' => null
     * ]
     * ....
     * ];
     * @param int $timeOut 超时设置
     * @return array
     */
    public static function postMulti($nodes, $timeOut = 5)
    {
        try {
            if (false == is_array($nodes)) {
                return array();
            }

            $mh = curl_multi_init();
            $curlArray = array();
            foreach ($nodes as $key => $info) {
                if (false == is_array($info)) {
                    continue;
                }
                if (false == isset($info['url'])) {
                    continue;
                }

                $ch = curl_init();
                // 设置url
                $url = $info['url'];
                curl_setopt($ch, CURLOPT_URL, $url);

                $data = isset($info['data']) ? $info['data'] : null;
                if (false == empty($data)) {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    // array
                    if (is_array($data) && count($data) > 0) {
                        curl_setopt($ch, CURLOPT_POST, count($data));
                    }
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }

                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                // 如果成功只将结果返回，不自动输出返回的内容
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // user-agent
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:22.0) Gecko/20100101 Firefox/22.0");
                // 超时
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);

                $curlArray[$key] = $ch;
                curl_multi_add_handle($mh, $curlArray[$key]);
            }

            $running = NULL;
            do {
                usleep(10000);
                curl_multi_exec($mh, $running);
            } while ($running > 0);

            $res = array();
            foreach ($nodes as $key => $info) {
                $res[$key] = curl_multi_getcontent($curlArray[$key]);
            }
            foreach ($nodes as $key => $info) {
                curl_multi_remove_handle($mh, $curlArray[$key]);
            }
            curl_multi_close($mh);
            return $res;
        } catch (Exception $e) {
            return array();
        }

    }

}
?>