<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_cate.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');


// $url = 'https://www.souduw.com/'; //需要爬取的url章节信息


// $data = webRequest($url ,'GET',[],[]);//获取当前的匹配的内容信息
$data ='<div class="proxylistitem">
      <div style="float:left; display:block; width:630px;">
　　　　　 <li>
            <span class="lx"><a href="/youxijingji.html" title="游戏竞技">游戏竞技</a></span>
            <span class="sm"><a href="/xiaoshuo/LianMeng_KaiJuSuoNa_SongZouLiaoZhouJieDaiMei.html" title="联盟：开局唢呐，送走了周姐呆妹">联盟：开局唢呐，送走了周姐呆妹</a></span>
            <span class="zj"><a href="/LianMeng_KaiJuSuoNa_SongZouLiaoZhouJieDaiMei/822_1.html" title="联盟：开局唢呐，送走了周姐呆妹 第785章 用峡谷先锋拖延北虎" target="_blank">第785章 用峡谷先锋拖延北虎</a></span>
            <span class="zz"><a href="/author/YouYingYeShen2.html" target="_blank" title="幽影夜神2">幽影夜神2</a></span>
            <span class="sj">02-19</span>
            </li>
            <li>
            <span class="lx"><a href="/dushishenghuo.html" title="都市生活">都市生活</a></span>
            <span class="sm"><a href="/xiaoshuo/LongYi_KaiJuCanZaoBeiPan_FanShouYingQuXiaoYiZi.html" title="龙医：开局惨遭背叛，反手迎娶小姨子">龙医：开局惨遭背叛，反手迎娶小姨子</a></span>
            <span class="zj"><a href="/LongYi_KaiJuCanZaoBeiPan_FanShouYingQuXiaoYiZi/275_1.html" title="龙医：开局惨遭背叛，反手迎娶小姨子 第二百七十五章 无名剑出！" target="_blank">第二百七十五章 无名剑出！</a></span>
            <span class="zz"><a href="/author/ZuiXingChen4.html" target="_blank" title="罪星辰">罪星辰</a></span>
            <span class="sj">02-19</span>
            </li>
            <li>
<span class="lx"><a href="/dushishenghuo.html" title="都市生活">都市生活</a></span>
<span class="sm"><a href="/xiaoshuo/ShangChengZhiXia.html" title="上城之下">上城之下</a></span>
<span class="zj"><a href="/ShangChengZhiXia/301_1.html" title="上城之下 第0301章 惊人发现" target="_blank">第0301章 惊人发现</a></span>
<span class="zz"><a href="/author/LiMa.html" target="_blank" title="李马">李马</a></span>
<span class="sj">02-19</span>
</li>
　　　　</div>
</div>';
if($data){
    //<div class="lastupdate"> 最新更新
    preg_match("/<div class=\"proxylistitem\".*?>.*?<\/div>/ism",$data,$matchesRes);
    if(isset($matchesRes[0]) && !empty($matchesRes[0])){
        $nearby_item = $matchesRes[0] ?? [];
        //获取分类的基础信息
        preg_match_all("/<span class=\"lx\".*?>.*?<\/span>/ism",$nearby_item,$matchesRes_cate);
        if(isset($matchesRes_cate[0]) && $matchesRes_cate[0]){
                //匹配a连接
                //--- 导出站外链接
                $content_info = $matchesRes_cate[0] ?? [];
                $pat = '/href=[\"|\'](.*?)[\"|\']/i';
                // echo '<pre>';
                // print_R($content_info);
                // echo '</pre>';
                // exit;
                // preg_match_all($pat, $matchesRes_cate[0], $content_hrefs);
                //摘取连接里的数据信息
                $items = [];
                # $en_pa ='/[^\u4e00-\u9fa5]/';
                if(is_array($content_info)){
                    $en_preg = "/[\x7f-\xff]+/";//匹配中文
                    foreach($content_info as $response){
                        preg_match($pat, $response, $link_data);//获取当前的链接
                        preg_match($en_preg , $response,$title_data);//正则匹配汉字
                        $items[] = [
                            'link_url'  =>  $link_data[1] ?? '',
                            'title'     =>  $title_data[0] ?? '',
                        ];
                    }
                    echo '<pre>';
                    print_R($items);
                    echo '</pre>';
                    exit;
                }
        }else{
            echo 'no category data !!!';
        }

    }
    # preg_match_all('/<div\sstyle=\"float[^>]+>.*?<\/div>/',$line,$out);
}else{
    echo 'no data now!@!';
}
?>