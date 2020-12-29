<?php
ini_set("memory_limit", "3000M");
set_time_limit(300);
$dirname = dirname(dirname(__FILE__));
$dirname =str_replace("\\", "/", $dirname) ;
require_once ($dirname."/library/mysql_class.php");
require_once ($dirname."/library/curl_http.php");

$mysql_obj = new Mysql_class();
$http_client = new curl_http();#初始化实例

$link_url = 'https://you.ctrip.com/place/';

#先把国内得匹配出来
// echo "<div class=\"goto-items\" id=\"journals-panel-items\">";

$result ='<div class="goto-items" id="journals-panel-items">
  <dl class="item itempl-60">
            <dt class="panel-tab brt"><i class="des-gn"></i>国内<s></s></dt>
            <dd class="panel-con" style="margin-top: -1px;">
                <ul>
                        <li>
                            <strong>黑龙江</strong>
                                <a href="/place/haerbin151.html">哈尔滨</a>
                                <a href="/place/mudanjiang264.html">牡丹江</a>
                                <a href="/place/mohe983.html">漠河</a>
                                <a href="/place/yichun498.html">伊春</a>
                                <a href="/place/heihe265.html">黑河</a>
                        </li>
                        <li>
                            <strong>吉林</strong>
                                <a href="/place/changbaishan268.html">长白山</a>
                                <a href="/place/changchun216.html">长春</a>
                                <a href="/place/jilinshi267.html">吉林市</a>
                                <a href="/place/yanbian415.html">延边</a>
                                <a href="/place/tonghua874.html">通化</a>
                        </li>
                        <li>
                            <strong>新疆</strong>
                                <a href="/place/kanasi816.html">喀纳斯</a>
                                <a href="/place/urumqi117.html">乌鲁木齐</a>
                                <a href="/place/yili115.html">伊犁</a>
                                <a href="/place/tulufan35.html">吐鲁番</a>
                                <a href="/place/kashi124.html">喀什</a>
                        </li>
                        <li>
                            <strong>辽宁</strong>
                                <a href="/place/dalian4.html">大连</a>
                                <a href="/place/shenyang155.html">沈阳</a>
                                <a href="/place/dandong315.html">丹东</a>
                                <a href="/place/huludao345.html">葫芦岛</a>
                                <a href="/place/benxi463.html">本溪</a>
                        </li>
                        <li>
                            <strong>北京</strong>
                                <a href="/place/beijing1.html">北京</a>
                        </li>
                        <li>
                            <strong>内蒙古</strong>
                                <a href="/place/hulunbeier458.html">呼伦贝尔</a>
                                <a href="/place/huhehaote156.html">呼和浩特</a>
                                <a href="/place/xilingol484.html">锡林郭勒</a>
                                <a href="/place/eerduosi600.html">鄂尔多斯</a>
                                <a href="/place/chifeng483.html">赤峰</a>
                        </li>
                        <li>
                            <strong>甘肃</strong>
                                <a href="/place/dunhuang8.html">敦煌</a>
                                <a href="/place/gannan426.html">甘南</a>
                                <a href="/place/jiayuguan284.html">嘉峪关</a>
                                <a href="/place/lanzhou231.html">兰州</a>
                                <a href="/place/tianshui285.html">天水</a>
                                <a href="/place/jiuquan282.html">酒泉</a>
                        </li>
                        <li>
                            <strong>天津</strong>
                                <a href="/place/tianjin154.html">天津</a>
                        </li>
                        <li>
                            <strong>河北</strong>
                                <a href="/place/chengde135.html">承德</a>
                                <a href="/place/qinhuangdao132.html">秦皇岛</a>
                                <a href="/place/zhangjiakou497.html">张家口</a>
                                <a href="/place/handan495.html">邯郸</a>
                                <a href="/place/baoding459.html">保定</a>
                        </li>
                        <li>
                            <strong>山西</strong>
                                <a href="/place/pingyao365.html">平遥</a>
                                <a href="/place/wutaishan184.html">五台山</a>
                                <a href="/place/datong275.html">大同</a>
                                <a href="/place/yuncheng397.html">运城</a>
                                <a href="/place/linfen318.html">临汾</a>
                        </li>
                        <li>
                            <strong>宁夏</strong>
                                <a href="/place/yinchuan239.html">银川</a>
                                <a href="/place/zhongwei1184.html">中卫</a>
                                <a href="/place/wuzhong890.html">吴中</a>
                                <a href="/place/guyuan888.html">固原</a>
                        </li>
                        <li>
                            <strong>山东</strong>
                                <a href="/place/qingdao5.html">青岛</a>
                                <a href="/place/taishan6.html">泰山</a>
                                <a href="/place/jinan128.html">济南</a>
                                <a href="/place/yantai170.html">烟台</a>
                                <a href="/place/weihai169.html">威海</a>
                        </li>
                        <li>
                            <strong>青海</strong>
                                <a href="/place/xining237.html">西宁</a>
                                <a href="/place/qinghaihu281.html">青海湖</a>
                                <a href="/place/tongren895.html">同仁</a>
                                <a href="/place/geermu332.html">格尔木</a>
                                <a href="/place/yushu896.html">玉树</a>
                        </li>
                        <li>
                            <strong>陕西</strong>
                                <a href="/place/xian7.html">西安</a>
                                <a href="/place/huashan183.html">华山</a>
                                <a href="/place/baoji422.html">宝鸡</a>
                                <a href="/place/yanan423.html">延安</a>
                                <a href="/place/xianyang632.html">咸阳</a>
                        </li>
                        <li>
                            <strong>江苏</strong>
                                <a href="/place/suzhou11.html">苏州</a>
                                <a href="/place/nanjing9.html">南京</a>
                                <a href="/place/wuxi10.html">无锡</a>
                                <a href="/place/yangzhou12.html">扬州</a>
                                <a href="/place/liyang598.html">溧阳</a>
                        </li>
                        <li>
                            <strong>河南</strong>
                                <a href="/place/songshan178.html">嵩山</a>
                                <a href="/place/luoyang198.html">洛阳</a>
                                <a href="/place/kaifeng165.html">开封</a>
                                <a href="/place/zhengzhou157.html">郑州</a>
                                <a href="/place/anyang412.html">安阳</a>
                        </li>
                        <li>
                            <strong>西藏</strong>
                                <a href="/place/lhasa36.html">拉萨</a>
                                <a href="/place/linzhi126.html">林芝</a>
                                <a href="/place/2446.html">日喀则</a>
                                <a href="/place/ali99.html">阿里</a>
                                <a href="/place/shannan339.html">山南</a>
                        </li>
                        <li>
                            <strong>安徽</strong>
                                <a href="/place/huangshan19.html">黄山</a>
                                <a href="/place/yixian528.html">黟县</a>
                                <a href="/place/jiuhuashan182.html">九华山</a>
                                <a href="/place/hefei196.html">合肥</a>
                                <a href="/place/wuhu457.html">芜湖</a>
                        </li>
                        <li>
                            <strong>四川</strong>
                                <a href="/place/chengdu104.html">成都</a>
                                <a href="/place/jiuzhaigou25.html">九寨沟</a>
                                <a href="/place/emeishan24.html">峨眉山</a>
                                <a href="/place/daocheng342.html">稻城-亚丁</a>
                                <a href="/place/kangding344.html">康定</a>
                        </li>
                        <li>
                            <strong>湖北</strong>
                                <a href="/place/wuhan145.html">武汉</a>
</div>';

///<p align=\"center\"><big><strong>(.*?)<\/strong><\/big><\/p>/
//这句话得意思是先去除所有得制表符空行字符
$result =preg_replace("/[\t\n\r]+/","",$result);
preg_match('/<div class="goto-items" id="journals-panel-items">(.*)<\/div>/',$result, $match_res);
if(isset($match_res[1])){
	$p_str = $match_res[1];
	preg_match_all('/<a[^>]*>[^<]*<\/a>/i',$p_str,$aaa);
	$link_contents=[];
	if($aaa && is_array($aaa) && isset($aaa[0])){
		foreach($aaa[0] as $contents){
			$link_contents[] = strip_tags($contents);
		}
	}

	#超链接里得字符已经匹配出来了
	preg_match_all('/<a[^>]+href="(([^"]+)")/i',$p_str,$result2);

	$link_data = $result2[1];


	$city_list = [];
	for ($i=0; $i <count($link_contents) ; $i++) { 
		//处理一下链接拆分开
		if(!$link_data[$i]){
			$link_url= '';
		}else{
			$link_url= substr($link_data[$i], 0,-1);
			$split_str = explode('/', $link_url);
			if(strpos($split_str[2], '.')){
				$keywords = current(explode('.', $split_str[2]));
			}
		}

		$link_url = substr($link_data[$i], 0,-1);
		$city_list[] =[
			'city_name'	=>$link_contents[$i],
			 'link_url'	=>$link_url,
			 'keywords'	=> isset($keywords) ? $keywords : '', //在为了后面翻页做准备
		];
	}

	if($city_list){
		//干DB该干的事 插入库中
		foreach($city_list as $v){
			$rows = $mysql_obj->fetch("select * from trip_city where keywords='".$v['keywords']."' limit 1",$mysql_obj->db_slave);
			if(!$rows){
				$sql ="insert into trip_city(`city_name`,`link_url`,`keywords`) values ('".$v['city_name']."','".$v['link_url']."','".$v['keywords']."')";
				$res = $mysql_obj->query($sql,$mysql_obj->db_master);
				echo "ok";
			}else{
				echo $v['city_name']." 已经同步<br />";
			}
			     
		}
	}
}

?>