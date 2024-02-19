<?php
// ///////////////////////////////////////////////////
// Copyright(c) 2016,真一网络
// 日 期：2024年2月19日
// 作　者：Lucky
// E-mail :luxiaofeng.200@163.com
// 文件名 :book_detail.php
// 创建时间:下午7:12:00
// 编 码：UTF-8
// 摘 要:获取最新的文章的标签
// ///////////////////////////////////////////////////
ini_set("memory_limit", "5000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once($dirname.'/library/init.inc.php');

$art_id  = isset($_REQUEST['art_id']) ? intval($_REQUEST['art_id']) : 0;
if(!$art_id){
    echo '请选择要抓取的内容id';
    exit();
}
$table_name ='ims_category';
$info = $mysql_obj->get_data_by_condition('id = \''.$art_id.'\'',$table_name);
$url ='https://www.souduw.com/';
if($info){
    //进行相关的匹配信息
    if(!empty($info[0]['article_url'])){
        $link_url = $url . $info[0]['article_url'];//需要抓取的网址
        // echo $link_url;die;
        $detail ='<div class="jieshao"><div class="lf">
<img src="/cover/4e/ed/02/4eed02ddc035b204cf347b0786114c43.jpg" alt="人在合欢宗，你让我守身如玉？" onerror="this.src=\'/cover/4e/ed/02/4eed02ddc035b204cf347b0786114c43.jpg\'">
</div>
<div class="rt">
<h1>人在合欢宗，你让我守身如玉？</h1>
<div class="msg">
<em>作者：<a href="/author/NanFengGuoJing.html" target="_blank" title="南风过境作品集">南风过境</a></em>
<em>状态：连载中</em>
<em>更新时间：2024-02-19 15:54:33</em>
<em>最新章节：<a href="https://www.souduw.com/RenZaiHeHuanZong_NiRangWoShouShenRuYu_/436_1.html" title="人在合欢宗，你让我守身如玉？ 第436章 上清邪神" target="_blank">第436章 上清邪神</a></em>
</div>
<div class="info">
<a href="/RenZaiHeHuanZong_NiRangWoShouShenRuYu_/1_1.html" target="_blank"> 开始阅读</a>
<a href="#footer" rel="nofollow">直达底部</a>
<a href="/user/mark/add.html?novelid=337339" rel="nofollow">加入书架</a>
<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&amp;email=masxin@zohomail.com" target="_blank" rel="nofollow">错误举报</a>
</div>
<div class="intro">
《<a href="/xiaoshuo/RenZaiHeHuanZong_NiRangWoShouShenRuYu_.html" title="人在合欢宗，你让我守身如玉？">人在合欢宗，你让我守身如玉？</a>》是网络小说作家<a href="/author/NanFengGuoJing.html" target="_blank" title="南风过境作品集">南风过境</a>创作的长篇玄幻奇幻小说<br><p>苏尘因意外穿越到了天元大世界，并且还成为了合欢宗的一名弟子！最关键的是苏尘身为九阳圣体，在合欢宗是绝佳的鼎炉，毫无疑问他成为了无数合欢宗女性的梦中情人！四位拥有绝世容颜，性格不一样的师姐都对他青睐有加，每天都渴望于他双修。就连宗门内的师妹也是百般尝试，倾心于他。然而就在苏尘准备提枪上战场的时候，守身如玉系统突然降临。【滴】【宿主可通过守身如玉来获取奖励】【任务一：不与师姐双修，奖励一重境界】【任务二：与师姐双修，惩罚宿主渡雷劫】苏尘欲哭无泪道。“我都穿越到合欢宗了，你让我守身如玉？”</p><p>您要是觉得</p><a href="https://www.souduw.com" title="搜读网">搜读网</a>提供<a href="/xiaoshuo/RenZaiHeHuanZong_NiRangWoShouShenRuYu_.html" title="人在合欢宗，你让我守身如玉？">人在合欢宗，你让我守身如玉？</a>免费阅读(<a href="https://www.souduw.com/xiaoshuo/RenZaiHeHuanZong_NiRangWoShouShenRuYu_.html" title="https://www.souduw.com/xiaoshuo/RenZaiHeHuanZong_NiRangWoShouShenRuYu_.html">https://www.souduw.com/xiaoshuo/RenZaiHeHuanZong_NiRangWoShouShenRuYu_.html</a>)</div>
<div id="listtj">推荐阅读：<a href="https://www.souduw.com/xiaoshuo/YiJianDuZun.html" style="color: #c00;">一剑独尊</a>、<a href="https://www.souduw.com/xiaoshuo/ChangShengWuDao_CongWuQinYangShengQuanKaiShi.html" style="color: #c00;">长生武道：从五禽养生拳开始</a>、<a href="https://www.souduw.com/xiaoshuo/DaoGuiYiXian.html" style="color: #c00;">道诡异仙</a>、<a href="https://www.souduw.com/xiaoshuo/BuKeXueYuShou.html" style="color: #c00;">不科学御兽</a>、<a href="https://www.souduw.com/xiaoshuo/TianQiYuBao.html" style="color: #c00;">天启预报</a>、<a href="https://www.souduw.com/xiaoshuo/ShenKongBiAn.html" style="color: #c00;">深空彼岸</a>、<a href="https://www.souduw.com/xiaoshuo/KaiJuQianDaoHuangGuShengTi.html" style="color: #c00;">开局签到荒古圣体</a>、<a href="https://www.souduw.com/xiaoshuo/GaoTianZhiShang.html" style="color: #c00;">高天之上</a>、<a href="https://www.souduw.com/xiaoshuo/BaoHuWoFangZuChang.html" style="color: #c00;">保护我方族长</a>、<a href="https://www.souduw.com/xiaoshuo/WoDeGuiYiRenShengMoNiQi.html" style="color: #c00;">我的诡异人生模拟器</a>、<a href="https://www.souduw.com/xiaoshuo/HeDongLiJianXian.html" style="color: #c00;">核动力剑仙</a>、<a href="https://www.souduw.com/xiaoshuo/ZuiChuJinHua.html" style="color: #c00;">最初进化</a>、<a href="https://www.souduw.com/xiaoshuo/YeDeMingMingShu.html" style="color: #c00;">夜的命名术</a>、<a href="https://www.souduw.com/xiaoshuo/XiuLianCongJianHuaGongFaKaiShi.html" style="color: #c00;">修炼从简化功法开始</a>、<a href="https://www.souduw.com/xiaoshuo/MinSuCongXiangXiXueShenKaiShi.html" style="color: #c00;">民俗从湘西血神开始</a>、<a href="https://www.souduw.com/xiaoshuo/WuXingManJi_JianGeGuanJianLiuShiNian.html" style="color: #c00;">悟性满级：剑阁观剑六十年</a>、<a href="https://www.souduw.com/xiaoshuo/GuaiTanWanJia.html" style="color: #c00;">怪谈玩家</a>、<a href="https://www.souduw.com/xiaoshuo/ZheGeWuShengGuoYuKangKai.html" style="color: #c00;">这个武圣过于慷慨</a>、<a href="https://www.souduw.com/xiaoshuo/HaiLanSaLingZhu.html" style="color: #c00;">海兰萨领主</a>、<a href="https://www.souduw.com/xiaoshuo/MingKeJie13Hao.html" style="color: #c00;">明克街13号</a>、</div>
</div>
</div>
<div class="mulu">
<h2>《<a href="" title="重生之青云直上">重生之青云直上</a>》章节列表</h2>
<ul>
<li><a href="/ChongShengZhiQingYunZhiShang/1_1.html">第1章 大梦一场，我捏着满手王炸重生了</a></li>
<li><a href="/ChongShengZhiQingYunZhiShang/2_1.html">第2章 我想要这个人</a></li>
<li><a href="/ChongShengZhiQingYunZhiShang/3_1.html">第3章 这人胆子很大啊</a></li>
</ul>
</div>
';

        //$detail = webRequest($link_url , 'GET' , [],[]);
        preg_match("/<div class=\"jieshao\".*?>.*?<\/div>/ism",$detail,$matchesRes);
        preg_match('/<img.*?src="([^"]+)"/',$matchesRes[0],$m);
        $store_data['cover_logo'] = $url .$m[1]??'';

        //获取标题
        preg_match("#<h1>([^<]*)</h1>#",$detail,$title_data);
        $store_data['title'] = $title_data[1] ?? '';

        //获取连载和更新时间、作者、最后的一节的章节
        preg_match("/<div class=\"msg\".*?>.*?<\/div>/ism",$detail,$all_data);
        $pattern = '/[\s]+/';
        $c_data= preg_split($pattern, $all_data[0]);
        if(isset($c_data[5])){
            $author = filterHtml($c_data[5]);
            $author_info = explode('>',$author);
            $store_data['author'] = $author_info[1] ?? '';
        }
    if($c_data){
        $en_preg = "/[\x7f-\xff]+/";//匹配中文
        if(isset($c_data[5])){
            preg_match($en_preg,$c_data[5],$author_data);
            $store_data['author'] = $author_data[0] ?? '';
        }

        if(isset($c_data['6'])){
            $a= explode('：',$c_data[6]);
            preg_match($en_preg,$a[1],$status_data);
            $store_data['status'] = $status_data[0] ??'';
        }
        //处理更新时间
        if(isset($c_data[7])){
            list($c_name,$date) =explode('：',$c_data[7]);
            $up_time =$date.' '.$c_data[8] ??'';
            $up_time = filterHtml($up_time);
            $store_data['third_update_time'] = strtotime($up_time);
        }
        print_R(preg_match('/target="_blank">.*/',$c_data[14],$t));
        //处理最后的章节
        $aa = $c_data[15];
        $nearyby_item = $t[0].' '.$aa;
        $html =str_replace('target="_blank">','',$nearyby_item);
        $html = str_replace('</a>','',$html);
        $html =str_replace('</em>','' ,$html);
        $store_data['nearby_chapter'] = $html;
    }


    $chapter_detal  = [];
    //匹配目录信息
    preg_match("/<div class=\"mulu\".*?>.*?<\/div>/ism",$detail,$matchesRes);
    if(isset($matchesRes[0]) && !empty($matchesRes[0])){
        $pat = '/href=[\"|\'](.*?)[\"|\']/i';
        $newdata = preg_match_all("/<li.*?>.*?<\/li>/ism" , $matchesRes[0],$aaa);

        if(!empty($aaa)){
            foreach($aaa[0] as $link_value){
                preg_match($pat , $link_value ,$link_info);
                $chapter_detal[]=[
                    'link_url'  =>  $link_info[1] ?? '',
                ];
            }
        }
    }
    $store_data['cate_id'] = $art_id;
    $store_data['createtime'] = time();


    //执行插入操作
    $id = 5;
    foreach($chapter_detal as &$v){
        $v['store_id']  =   $id;
        $v['createtime'] = time();
    }
    $chapter_table_name= 'ims_chapter';
    $res = $mysql_obj->add_data($chapter_detal , $chapter_table_name);
    echo '<pre>';
    print_R($res);
    echo '</pre>';
    exit;
}
}else{
    echo "no data";
}
?>