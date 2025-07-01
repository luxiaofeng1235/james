<?
require_once __DIR__.'/library/init.inc.php';
require_once __DIR__.'/library/R3ClientObject.class.php';
use QL\QueryList;
print_R($_SERVER);
echo 333;exit;

$score = getScoreRandom();

$aarr = webRequest('https://www.bqwxg8.com/wenzhang/44/44203/','GET');
echo "<pre>";
var_dump($aarr);
echo "</pre>";
exit();

// $aa = XunSearch::delDocumentCli(123455);
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


$res = BiqugeRequestModel::getUsingProxy();
echo "<pre>";
var_dump($res);
echo "</pre>";
exit();



$aa =getFirstThreeDigits(171027);
dd($aa);

$dir ='/data/txt';
$aa= getFilesInDirectory($dir);
echo "<pre>";
var_dump($aa);
echo "</pre>";
exit();




$dir = '/data/pic/upload/book12/';
if ( is_dir($dir)) {
   echo "目录存在";
} else {
   echo "目录不存在";
}
exit;


// $data = curlContetnsByProxy('https://res.jhkhmgj.com/bqk/436/ac/cc/25606.jpg',BiqugeRequestModel::getUsingProxy());
// echo "<pre>";
// var_dump($data);
// echo "</pre>";
// exit();


// $url = 'https://res.jhkhmgj.com/bqk/436/ac/cc/25606.jpg';
// dd($url);

// $aa = BiqugeModel::saveBiqugeBookImage('https://res.mysignal.cn/xhs/368/77/13/145103.jpg')
// dd(Env::get('BQG.PIC_NEW_URL'));
// $aa = webRequest('https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=99&area_id=0&isp=0&','GET');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();

/*
代理网络: proxy.stormip.cn
端口: 2000
账户名: storm-red1235_ip-104.234.48.123
密码: 123456
*/

// $aa  =webRequest('https://api.wandouapp.com/?app_key=e890aa7191c00cd2f641060591c4f1d0&num=1&xy=3&type=2&lb=\r\n&nr=99&area_id=&isp=0&','GET');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


// https://chapter.jhkhmgj.com/zl/46/f4/b3/11372/15594.html
// // https://chapter.ycukhv.com/zl/46/f4/b3/11372/15594.html
// //https://chapter.lmmwlkj.com/zl/46/f4/b3/11372/15594.html
// $aa= BiqugeRequestModel::swooleRequest('https://chapter.ycukhv.com/bqk/175/ec/5d/127/387.html');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


// echo "<pre>";
// var_dump(new BiqugeRequestModel());
// echo "</pre>";
// exit();
    
// $url = 'https://res.jhkhmgj.com/slmh/156/20/50/473.jpg';
// $status = check_url($url);
// echo "<pre>";
// var_dump($status);
// echo "</pre>";
// exit();



// $aa =BiqugeModel::getCoverLogoByPaoshu8('肆虐韩娱','姬叉');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


// $info = BiqugeModel::getCoverInfoByDataBase(970837);
// dd($info);






// $res = $mysql_obj->myProConnectionInfo('db_bqg_collect');
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();

    

// $t = range(1,200);
// foreach($t as $val){
// 	$urls[]='https://chapter.jhkhmgj.com/newmerge/485/f4/28/27943/55780.html';
// }

// StoreModel::swooleRquest($urls)

// $image = readFileData('./xl.meta');


// // $aa = StoreModel::swooleRquest($url);
// // $image = array_values($aa)[0] ?? '';
// writeFileCombine('./biquge_test.jpg',$image);
// echo 3;die;
// var_dump($aa);
// die;

// $id = isset($argv[1]) ? $argv[1] : 0;
// if(!$id){
// 	exit(" nodata \r\n");
// }
// $key = getFirstThreeDigits($id);
// $url  = sprintf("%ssource/%s/%s.html" , Env::get('BQG.BASE_URL') , $key , $id);
// echo " url = {$url} \r\n";
// exit;


// $arr = BiqugeService::getRankListByPageId('550b836229cd462830ff4d1d');
// echo "<pre>";
// var_dump($arr);
// echo "</pre>";
// exit();


// $aa =StoreModel::swooleRquest('https://chapter.ycukhv.com/bqk/260/97/78/82/1.html');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


// $aa  = QueryList::get('https://chapter.ycukhv.com/df/205/e2/cc/27700/40084.html');

// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();
    

// $testarr =StoreModel::swooleRquest('https://chapter.ycukhv.com/bqk/160/1c/5f/84278/104028.html');
// echo "<pre>";
// var_dump($testarr);
// echo "</pre>";
// exit();


// $datalist = BiqugeService::getHostNewBookeList();
// echo "<pre>";
// var_dump($datalist);
// echo "</pre>";
// exit();



// $data = BiqugeService::hahatest();
// echo "<pre>";
// var_dump($data);
// echo "</pre>";
// exit();

// $num = 0;
// do{	$num++;
	
// 	$url = 'https://book.prod-book.iolty.xyz/classify/all/1/1/0/-1/'.$num.'.html';
// 	echo "num  = {$num} \r\n";
// 	$res = webRequest($url ,'GET');
// 	$dataRet = json_decode($res,true);
// 	$lists = $dataRet['data']['lists'] ?? [];
// 	if(!$lists || empty($lists)){
// 		echo "当前 num = {$num}为最后一页\r\n";
// 		break;
// 	}else{
// 		echo "有效连接 url = {$url}\r\n";
// 	}
// 	sleep(1);
// }while(true);
// exit;

// $aa = remote_file_exists('https://res.ycukhv.com/z/32/f0/e5/574.jpg');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();


// $url = [
// 	'https://chapter.ycukhv.com/bbqsk/447/20/f0/277/476.html',
// 	'https://chapter.ycukhv.com/bbqsk/447/20/f0/277/477.html',
// 	'https://chapter.ycukhv.com/bbqsk/447/20/f0/277/478.html',
// 	'https://chapter.ycukhv.com/bbqsk/447/20/f0/277/479.html',
// ];
// $list = StoreModel::swooleRquest($url);
// foreach($list as $key =>$val){
// 	$cc = json_decode($val,true);
// 	$content =$cc['data']['content'] ?? '';
// 	echo "<pre>";
// 	var_dump($content);
// 	echo "</pre>";
// 	exit();
	    
	    


// $aa= webRequest('https://book.prod-book.iolty.xyz/classify/all/1/8/-1/-1/1/1.html','GET');
// echo "<pre>";
// var_dump($aa);
// echo "</pre>";
// exit();







//zl/46/f4/b3/11372/15585.html
// $data = BiqugeService::getListByKeywords('我不可能是妖魔');
// dd($data);
/*
https://book.prod-book.iolty.xyz/source/985/985371.html
https://book.prod-book.iolty.xyz/source/102/1026968.html
https://book.prod-book.iolty.xyz/source/641/641603.html 
977211
643536
921716
775645
1050071
824315
865332
1007690
1007690
639569
991446
1024433
1047132
1003328
284024
968050
961764
990778
1046941
1051171
1046993
1047011
1046939
1047169
740921
1046707
*/

// $cate = NovelModel::getCateConf();
// dd($cate);

// $data = BiqugeService::getCategoryIndex();
// echo "<pre>";
// var_dump($data);
// echo "</pre>";
// exit();

// $data = BiqugeService::getBaseInfo(822);
// echo "<pre>";
// var_dump($data);
// echo "</pre>";
// exit();

// 定义基本路径
$base_dir = "uploads/";


$user_id = 3000;
$base_dir = "uploads/";

// 将 UID 转换为字符串，确保格式一致
$uid_str = str_pad($user_id, 4, '0', STR_PAD_LEFT); // 将 UID 填充至 4 位


// 根据 UID 的前两位生成分割的文件夹路径
$first_level = substr($uid_str, 0, 2); // 获取前三位位
$target_dir = $base_dir . $first_level . "/" . $user_id . "/"; // 生成目标路径
// 输出生成的路径
echo "生成的文件夹路径: " . $target_dir;
exit;
    

// $data = BiqugeService::getBookSource(966293);
// $data = BiqugeService::getBaseInfo(966293);
$data = BiqugeService::getDetailInfo(546546);
$url = Env::get('BQG.PIC_URL').$data['image'];
echo "<pre>";
var_dump($url);
echo "</pre>";
exit();

$data = BiqugeService::getBqgChapterList('zl/46/f4/b3/11372.html','https://chapter.ycukhv.com/');
echo "<pre>";
var_dump($data);
echo "</pre>";
exit();

// // $res = webRequest('http://192.168.10.17:8005/api/adver/getAdverMap','POST',[],[]);
// // $dataList= json_decode($res,true);
// // echo "<pre>";
// // var_dump($dataList);
// // echo "</pre>";
// // exit();



// // $product = [
// //     'id' => 1,
// //     'title' => '茅台飞天52°',
// //     'product_class_title' => '酒水/饮料',
// //     'price' => 9.01,
// // ];

// $str = urlencode('听说前夫暗恋我');
// $url=sprintf("s.prod-book.iolty.xyz/v4/2/lists.api?form=1&keyword=%s&package=com.bqkkxb2023.read.tt",$str);
// $res = webRequest($url,'GET');
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();

// $data = json_decode($res,true);
// $content = $data['data']['content'] ?? ''; //获取内容
// echo "<pre>";
// var_dump($res);
// echo "</pre>";

// echo "\r\n";
// $res = BiqugeService::pswDecryptString($content);
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();





//https://book.prod-book.iolty.xyz/source/215/215292.html
 $url = 'https://book.prod-book.iolty.xyz/book/details/v4/78/78121.html';
$data = webRequest($url,'GET');

$list = json_decode($data,true);
echo "<pre>";
var_dump($list);
echo "</pre>";
exit();


$list = BiqugeService::getBqgChapterList('slsy/116/03/ea/215292.html');
echo "<pre>";
var_dump($list);
echo "</pre>";
exit();


$storeCient = new R3ClientObject();
$pic = '/data/pic/202409/wtmdfpzscwgsdhs-mxs.jpg';
$storeCient->handlePutFiles($pic);
exit;

// $html = webRequest('https://www.bqg24.net/article/76008/','GET');
// $html = iconv('gbk', 'utf-8//ignore', $html);
// $data = Bqg24Model::getBqg24ChapterList($html,'狩猎诸天','https://www.bqg24.net/article/76008/');
// echo "<pre>";
// var_dump($data);
// echo "</pre>";
// exit();




$sql = "select * from mc_book_class";
$list = $mysql_obj->fetchAll($sql,'db_novel_pro');
foreach($list as $key =>$val){
	$sql = "select pic from mc_book where book_name regexp '{$val['class_name']}' and book_type = {$val['book_type']}  order by id desc  LIMIT 1";
	$info = $mysql_obj->fetch($sql , 'db_novel_pro');
	$pic = $info['pic'] ?? '';
	if($pic){
		$sql = "update mc_book_class set class_pic = '{$pic}' where id =  {$val['id']}";
		$mysql_obj->query($sql,'db_novel_pro');
	}
	echo "{$key} \t $sql\r\n";
}
exit;

$url = 'https://img.ipaoshuba.net/322719/264400.jpg';

$file_str = webRequest($url,'GET');
$t = writeFileCombine("/data/pic/upload/book/testhhaha.jpg",$file_str);
echo "<pre>";
var_dump($t);
echo "</pre>";
exit();

    
$bookInfo = BiqugeService::getBookChapterList('bbqsk',508);
echo "<pre>";
var_dump($bookInfo);
echo "</pre>";
exit();

    
// 示例
$number1 = 10512;

$result1 = getFirstThreeDigits($number1);
echo "数字 $number1 的前三位是: $result1\n"; // 输出: 012
exit;


// $res = 972265% 1000;
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();


$res  = webRequest('https://book.prod-book.iolty.xyz/source/972/972265.html','GET');
$book_ret = json_decode($res,true);
echo "<pre>";
var_dump($book_ret);
echo "</pre>";
exit();


// $res = QueryList::get('http://www.xiaoshubao.net/read/472427/1.html')
// 			->query()
// 			->getHtml();
// dd($res);
$url = 'http://www.xiaoshubao.net/read/472427/1.html';
$t = range(0,100);
foreach ($t as $key => $value) {
	$urls[] = $url;
}
$res = StoreModel::swooleRquest($urls);
echo "<pre>";
var_dump($res);
echo "</pre>";
exit();


$url = 'https://www.keleshuba.net/book/304439/223032855.html';

$aa = QueryList::get($url)->getHtml();
echo "<pre>";
var_dump($aa);
echo "</pre>";
exit();

$res = StoreModel::swooleRquest($url,'POST');
echo "<pre>";
var_dump($res);
echo "</pre>";
exit();



$rules = [
	'content'	=>	['#nr1','html'],
];

$data = QueryList::get($url)
			->rules($rules)
			->query()
			->getData();
$data = $data->all();
echo "<pre>";
var_dump($data);
echo "</pre>";
exit();
    

$third_novel_id  =CommonService::getCollectWebId($url);
echo "<pre>";
var_dump($third_novel_id);
echo "</pre>";
exit();
     

//测试
$list = StoreModel::swooleRquest($url);
$html = array_values($list)[0] ?? '';

#获取规则
$rules = CommonService::collectContentRule($url);
#获取采集数据
$info_data = QueryList::html($html)
				->rules($rules)
				->query()
				->getData();
echo "<pre>";
var_dump($info_data);
echo "</pre>";
exit();
    



echo 33;exit;
$aa = webRequest('https://pic.mnjkfup.cn/data/pic/general/cyxdmccksty-qm.jpg','GET');
file_put_contents('./hh.jpg', $aa);
echo 3;die;

// $res  = GeneralModel::testarr();
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();


// require_once(__DIR__.'/library/file_factory.php');
// $rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['siluke520_content'];

// $text = '據說這位女老師來了幼兒園後，園區裡男家長去接送的次數都增加了數倍。';
// $res = traditionalCovert($text);

// dd(Env::get('SOURCE_LIST'));
// $res = webRequest('http://www.wenxuedu.org/html/0/350/index.html','GET');
// echo "<pre>";
// var_dump($res);
// echo "</pre>";
// exit();

// $url ='https://czbooks.net/n/s66ai0/s66o7374?chapterNumber=1';

// $urlData = parse_url($url);
// $path = $urlData['path']??'';
// if(isset($urlData['query']) && !empty($urlData['query'])){
// 	$path .='?'.$urlData['query'];
// }
// echo "<pre>";
// var_dump($path);
// echo "</pre>";
// exit();

// echo "<pre>";
// var_dump($urlData);
// echo "</pre>";
// exit();

// $res = webRequest($url,'GET');
// $convert_text = traditionalCovert($res);
// dd($convert_text);
// exit;
$rules = $urlRules[Env::get('APICONFIG.PAOSHU_STR')]['siluke520_info'];
$url = 'https://www.siluke520.net/book/17527/12011879.html';
$list = StoreModel::swooleRquest($url);
dd($list);
$html = array_values($list)[0] ??'';
$store_data = QueryList::html($html)
				->rules($rules)
				->query()
				->getData();
$store_data = $store_data->all();
// $cstore = $content['content'];
$aa = NovelModel::replaceContent($cstore,null ,null ,null);

$rt = NovelModel::getCharaList($html,$store_data['title'] , false , false  ,'siluke520');
echo "<pre>";
var_dump($rt);
echo "</pre>";
exit();
    

$date = date('Y-m-d H:i:s',strtotime('-7 days'));
echo "<pre>";
var_dump($date);
echo "</pre>";
exit();

echo '<pre>';
var_dump($_SERVER);
echo '</pre>';
exit;
$client = new R3ClientObject();
$pic = "/data/pic/202408/xcys-ftxg.jpg";
// $client->checkRemoteFiles($pic);
$client->deleteFile($pic);
echo 33;exit;
