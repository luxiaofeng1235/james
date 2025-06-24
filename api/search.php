<?php
/*
 * 处理迅搜的接口处理信息
 *
 * Copyright (c) 2017 - Linktone
 * @author xiaofeng.lu <xiaofeng.200@163.com>
 * @version 0.1
 *
 */
require_once dirname(__DIR__).'/library/init.inc.php';

$action = array('searchList','addIndex','delIndex');

$default_module = 'searchList';
//定义相关的操作内容
// $req = isset($_POST['req']) ? trim($_POST['req']) : $default_module;
$json_data = file_get_contents('php://input');
$postData =json_decode($json_data, true); 
$req = $postData['req'] ?? $default_module;

//处理异常情况判断
if(!$req || !in_array($req,$action)){
	$req = $default_module;
}
       

if($req == 'addIndex'){ //添加索引
	 $book_id = $postData['book_id'] ?? ''; //书籍ID
	 $book_name = $postData['book_name'] ?? ''; //小说名称
	 $author = $postData['author'] ?? '';//作者
	 //添加动作
	 if(!$book_id || !$book_name || !$author){
	 	$data = responseError('bad params');
		echo $data;exit();
	 }
	 $book_id = intval($book_id);
	 $ret = XunSearch::addDocument($book_id , $book_name , $author);
	 if($ret == false){
		$result = responseSuccess('添加失败',[]);
		echo $result;exit();
	}
	 $list = responseSuccess('success',[]);
	 echo $list;exit();
}else if($req =='delIndex'){//删除搜一年
	$book_id = $postData['id'] ?? 0;
	if(!$book_id){
		$data = responseError('bad params');
		echo $data;exit();
	}
	$book_id = intval($book_id); 
	//删除索引
	$ret = XunSearch::delDocument($book_id);
	if($ret == false){
		$result = responseSuccess('删除失败',[]);
		echo $result;exit();
	}
	//返回数据判断
	$result = responseSuccess('success',[]);
	echo $result;exit();
}else{
	//搜索查询接口
	$keywords = trim($postData['book_name']) ?? '';
	if(!$keywords){
		$data = responseError('bad params');
		echo $data;exit();
	}
	$resultSearch =$newList= [];	    
	//按关键字搜索
	$docs = XunSearch::getSearchByKeywords($keywords);
	if(empty($docs)){
		//使用全文检索进行联想搜索
		$docs = XunSearch::getSearchImagineList($keywords);
	}
	try {
		if($docs){
			//处理遍历的结果集合
			foreach($docs as $document){
			   if(!$document  || $document->id === ""){
			    	continue;    
				}	    
				$pid = $document->pid ?? 0; //小说ID
				$book_name = $document->book_name ?? ''; //书名
				$author = $document->author ?? ''; //作者
				$chrono = $document->chrono ?? ''; //更新时间
				$info['book_id'] = (int) $pid;
				$info['book_name'] = $book_name;
				$info['author'] = $author;
				$info['chrono'] = (int) $chrono;
				$resultSearch[] = $info;
			}

			//处理相关的逻辑显示顺序问题，有可能搜索的第一个不在第一个
			$position = 0;//默认为0
			foreach($resultSearch as $key =>$val){
				//如果当前的数据中存在有相同的和搜索结果的name 就把他放在第一个， 提高交互率
				if(isset($val['book_name']) && $keywords == $val['book_name']){
					  $position = $key;//记录对应的数据状态变更的消息
					  break;
				}
			}
			//交换第一个和最后一个的顺序显示，方便快速搜索
			$newList = NovelModel::swapFirstWithAny($resultSearch, $position);
		}
		$list = responseSuccess('获取成功',$newList);
		echo $list;exit();
	}catch (\XSException $e) {
    	 $result = $e->getMessage();
    	 $ret = responseError($result);
    	 echo $ret;exit();
 	}
}
?>